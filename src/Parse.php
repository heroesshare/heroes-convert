<?php namespace Heroes\Convert;
/*
 * heroes-convert
 *
 * Convert HeroesDataParser to heroes-talents, for Heroes of the Storm
 * https://github.com/tattersoftware/heroes-convert
 * 
 */

require_once 'Base.php';

/**
 * Class Parse
 *
 * Parses data from herodata and gamestrings files into the heroes-talent format
 */
class Parse extends Base
{
	/**
	 * Array of hero data from HDP file.
	 *
	 * @var array
	 */
	protected $herodata;
	
	/**
	 * Load data from the hero data input file.
	 *
	 * @param string    $herodataPath     Path to a valid heroesdata file
	 */
	public function __construct($herodataPath)
	{
		parent::__construct([]);
		
		$this->herodata = $this->loadData($herodataPath);
	}

	/**
	 * Verify a file path and returns the parsed JSON contents as an array
	 *
	 * @param string    $path Path to a JSON file
	 *
	 * @return array
	 */
	protected function loadData(string $path): array
	{
		// Verify the file
		if (! is_file($path))
		{
			throw new \RuntimeException('Unable	to locate file: ' . $path);
		}
		
		// Load raw contents
		$data = file_get_contents($path);
		if (empty($data))
		{
			throw new \RuntimeException('Unable to read data from file: ' . $path);
		}
		
		// Decode JSON data
		$array = json_decode($data, true);
		unset($data);
		if ($array === false)
		{
			throw new \RuntimeException('Error #' . json_last_error() . ' parsing ' . $path . ': ' . json_last_error_msg());
		}
		
		// Verify format
		if (! isset($array['FaerieDragon']['portraits']['minimap']))
		{
			throw new \RuntimeException('Invalid heroData file: ' . $path);
		}
		
		$this->logMessage('Loaded ' . count($array) . ' heroes from ' . basename($path));
		
		return $array;
	}

	/**
	 * Process the raw HDP data into heroes-talent format
	 *
	 * @return $this
	 */
	public function run()
	{
		// Process each hero array
		foreach ($this->herodata as $cHeroId => $hero)
		{
			// Inject the cHeroId
			$hero['cHeroId'] = $cHeroId;
			
			// Reformat abilities
			$hero['abilities'] = $this->reformatAbilities($hero);

			// Reformat talents
			$hero['talents'] = $this->reformatTalents($hero);

			// Remove extraneous fields
			unset($hero['subAbilities'], $hero['heroUnits']);
			
			// Add it to the collection
			$this->heroes[$hero['hyperlinkId']] = $hero;
		}
		
		unset($this->herodata);
		
		return $this;
	}

	/**
	 * Standardize abilities for a single hero
	 *
	 * @param array   $raw  Array of hero data from HDP
	 *
	 * @return array  Reformatted abilities
	 */
	protected function reformatAbilities(array $raw): array
	{
		// Consolidate abilities and subAbilities from the hero and its heroUnits
		$abilities = $this->parseAbilities($raw['abilities']);
		
		// Check for subAbilities (e.g. Odin)
		if (isset($raw['subAbilities']))
		{
			$abilities = array_merge($abilities, $this->parseSubAbilities(reset($raw['subAbilities'])));
		}

		// Check for heroUnits (e.g. Worgen)
		if (isset($raw['heroUnits']))
		{
			$abilities = array_merge($abilities, $this->parseHeroUnitAbilities($raw['heroUnits']));
		}
		
		// Add the extras and return
		return $this->addAbilitiesExtras($raw['hyperlinkId'], $abilities);
	}

	/**
	 * Set hotkeys and ability codes for a hero's abilities
	 *
	 * @param string  $hyperlinkId  The hero's hyperlinkId
	 * @param array   $abilities    Array of heroes-talents abilities
	 *
	 * @return array  Updated abilities
	 */
	protected function addAbilitiesExtras(string $hyperlinkId, array $abilities): array
	{
		$return = [];
		
		// Count abilities on each hotkey
		$hotkeys = [];

		// Count active abilities
		$activables = 0;
			
		// Process each ability
		foreach ($abilities as $ability)
		{
			// Use the abilityType to set hotkeys
			if ($ability['abilityType'] == 'Heroic')
			{
				$ability['hotkey'] = 'R';
			}
			elseif ($ability['abilityType'] == 'Active' || $ability['type'] == 'Activable')
			{
				$activables++;
				$ability['hotkey'] = (string)$activables;
			}
			elseif (strlen($ability['abilityType']) == 1)
			{
				$ability['hotkey'] = $ability['abilityType'];
			}
			
			// Set the code (e.g Q1) as "hotkey + (# hotkey abilities + 1)"
			switch ($ability['type'])
			{
				case 'trait': $code = 'D'; $ability['trait'] = true; break;
				case 'spray': $code = 'T'; break;
				case 'voice': $code = 'I'; break;
				case 'mount': $code = 'Z'; break;

				default:
					if (! empty($ability['hotkey']))
					{
						$code = $ability['hotkey'];
					}
			}

			// Something went wrong
			if (empty($code))
			{
				throw new \RuntimeException('Unable to set code and ability fields for ' . $ability['name']);
			}
	
			// Get the number of abilities with this code
			if (! isset($hotkeys[$code]))
			{
				$hotkeys[$code] = 0;
			}
			$hotkeys[$code]++;

			// Set the code and abilityId
			$ability['code']      = $code . $hotkeys[$code];
			$ability['abilityId'] = $hyperlinkId . '|' . $ability['code'];
			
			$return[] = $ability;
		}
		
		return $return;
	}
	
	/**
	 * Computes a unique hash for an ability or talent
	 *
	 * @param array   $raw  Raw HDP array
	 *
	 * @return string  UID
	 */
	public function uidFromRaw(array $raw): string
	{
		$keys = ['nameId', 'buttonId', 'abilityType'];
		$values = [];
		
		foreach ($keys as $key)
		{
			if (! isset($raw[$key]))
			{
				throw new \RuntimeException('Array missing required field "{$key}" in UID calculation: ' . print_r($raw, true));
			}
			$values[] = $raw[$key];
		}
		$values[] = empty($raw['isPassive']) ? 'False' : 'True';

		$str = implode('|', $values);
		
		return $this->skillUid($str);
	}   

	/**
	 * Reformat hero abilities
	 *
	 * @param array   $raw  Raw HDP abilities
	 *
	 * @return array
	 */
	protected function parseAbilities(array $types, string $subunit = null, string $herounit = null): array
	{
		$return = [];
		
		// Types: basic, heroic, trait, hearth, mount, activable, spray, voice
		foreach ($types as $type => $abilities)
		{
			foreach ($abilities as $ability)
			{
				// Add the UID
				$ability['uid'] = $this->uidFromRaw($ability);

				// Add metadata
				$ability['type']     = $type;
				$ability['subunit']  = $subunit;
				$ability['herounit'] = $herounit;
				$ability['sub']      = $subunit || $herounit;
		
				$return[] = $ability;
			}
		}

		return $return;
	}
	
	/**
	 * Flatten and reformat hero subAbilities
	 *
	 * @param array   $subAbilities  HDP subAbilities array
	 *
	 * @return array
	 */
	protected function parseSubAbilities(array $subAbilities, string $herounit = null): array
	{
		$return = [];
		
		foreach ($subAbilities as $subunit => $abilities)
		{
			$return = array_merge($return, $this->parseAbilities($abilities, $subunit, $herounit));
		}
		
		return $return;
	}
	
	/**
	 * Flatten and reformat heroUnit abilities
	 *
	 * @param array   $heroUnits  HDP heroUnits array
	 *
	 * @return array
	 */
	protected function parseHeroUnitAbilities(array $heroUnits): array
	{
		$return = [];
		
		foreach ($heroUnits as $units)
		{
			foreach ($units as $name => $unit)
			{
				if (isset($unit['abilities']))
				{
					$return = array_merge($return, $this->parseAbilities($unit['abilities'], null, $name));
				}
				
				if (isset($unit['subAbilities']))
				{
					$return = array_merge($return, $this->parseSubAbilities(reset($unit['subAbilities']), $name));
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Reformat talents for a single hero
	 *
	 * @param array   $hero  Array of hero data from HDP
	 *
	 * @return array  Reformatted talents
	 */
	protected function reformatTalents(array $hero): array
	{
		$return = [];

		// Process talents by level
		foreach ($hero['talents'] as $level => $talents)
		{
			$level = str_replace('level', '', $level);
			
			foreach ($talents as $i => $talent)
			{
				$talent['uid']   = $this->uidFromRaw($talent);
				$talent['level'] = $level;
				
				$return[$level][$i] = $talent;
			}
		}

		return $return;
	}
}
