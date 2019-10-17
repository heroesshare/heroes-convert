<?php namespace Heroes\Convert;
/*
 * heroes-convert
 *
 * Convert HeroesDataParser to heroes-talents, for Heroes of the Storm
 * https://github.com/tattersoftware/heroes-convert
 * 
 */

/**
 * Class Parse
 *
 * Parses data from herodata and gamestrings files into the heroes-talent format
 */
class Parse
{
	/**
	 * Array of hero data from HDP file.
	 *
	 * @var array
	 */
	protected $herodata;
	
	/**
	 * Array of heroes in heroes-talent format, indexed by shortname
	 *
	 * @var array
	 */
	protected $heroes = [];
	
	/**
	 * Load data from the hero data input file.
	 *
	 * @param string    $herodataPath     Path to a valid heroesdata file
	 */
	public function __construct($herodataPath)
	{
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
		if ($array === null)
		{
			throw new \RuntimeException('Error #' . json_last_error() . ' parsing ' . $path . ': ' . json_last_error_msg());
		}
		
		// Verify format
		if (! isset($array['FaerieDragon']['portraits']['minimap']))
		{
			throw new \RuntimeException('Invalid heroData file: ' . $path);
		}
		
		echo 'Loaded ' . count($array) . ' heroes from ' . basename($path) . PHP_EOL;
		
		return $array;
	}

	/**
	 * Return the array of formatted data for all heroes
	 *
	 * @return $this
	 */
	public function getHeroes(): array
	{
		return $this->heroes;
	}

	/**
	 * Process the raw HDP data into heroes-talent format
	 *
	 * @return $this
	 */
	public function run()
	{
		// Process each raw hero array
		foreach ($this->herodata as $cHeroId => $raw)
		{
			// Inject the cHeroId
			$raw['cHeroId'] = $cHeroId;
			
			// Parse it into heroes-talent format
			$hero = $this->heroFromRaw($raw);
			
			// Add it to the collection
			$this->heroes[$hero['shortName']] = $hero;
		}
		
		unset($this->herodata);
		
		return $this;
	}

	/**
	 * Convert a single raw HDP hero into heroes-talent format
	 *
	 * @param array    $raw  Array of hero data from HDP
	 *
	 * @return array
	 */
	protected function heroFromRaw(array $raw): array
	{
		// Start with the identity info
		$hero = [
			'shortName'    => strtolower($raw['hyperlinkId']),
			'attributeId'  => $raw['attributeId'],
			'cHeroId'      => $raw['cHeroId'],
			'cUnitId'      => $raw['unitId'],
			//'name'         => $raw['name'],
			//'role'         => reset($raw['roles']),
			//'expandedRole' => $raw['expandedRole'],
			//'type'         => $raw['type'],
			'releaseDate'  => $raw['releaseDate'],
		];
		
		// Add the icon
		$hero['icon'] = $hero['shortName'] . '.png';
		
		// Add tags
		$hero['tags'] = $raw['descriptors'];
		
		// Parse and add abilities
		$hero['abilities'] = $this->addAbilitiesExtras($hero['cHeroId'], $this->abilitiesFromRaw($raw));
		
		// Parse and add talents
		$hero['talents'] = $this->talentsFromRaw($raw);
		
		return $hero;
	}

	/**
	 * Set hotkeys and ability codes for a hero's abilities
	 *
	 * @param string  $cHeroId    The hero's cHeroId
	 * @param array   $abilities  Array of heroes-talents abilities
	 *
	 * @return array  Updated abilities
	 */
	protected function addAbilitiesExtras(string $cHeroId, array $abilities): array
	{
		$return = [];
		
		// Count abilities on each hotkey
		$hotkeys = [];

		// Count active abilities
		$activables = 0;
			
		// Process each ability
		foreach ($abilities as $ability)
		{
			// Update the hotkey on Actives
			if ($ability['type'] == 'activable')
			{
				$activables++;
				$ability['hotkey'] = (string)$activables;
			}
			
			// Determine the code (e.g Q1) - hotkey concat (# hotkey abilities + 1)
			switch ($ability['type'])
			{
				case 'trait': $code = 'D'; break;
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

			// Set the code and "ability"
			$ability['code']    = $code . $hotkeys[$code];
			$ability['ability'] = $cHeroId . '|' . $ability['code'];
			
			$return[] = $ability;
		}
		
		return $return;
	}

	/**
	 * Standardize abilities from a single raw hero
	 *
	 * @param array   $raw  Array of hero data from HDP
	 *
	 * @return array  Abilities in heroes-talent format
	 */
	protected function abilitiesFromRaw(array $raw): array
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
		
		return $abilities;
	}

	/**
	 * Format a single ability from HDP raw to heroes-talent
	 *
	 * @param array   $raw  HDP ability
	 *
	 * @return array  Ability in heroes-talent format
	 */
	protected function abilityFromRaw(array $raw): array
	{
		// Start with basic info
		$ability = [
			'uid'  => $this->abilityUid($raw),
			'icon' => strtolower(str_replace("'", '', $raw['icon'])), // strip single quotes like Kel'thuzad
		];

		// Determine the type
		$ability['type'] = isset($raw['herounit']) || isset($raw['subunit']) ? 'subunit' : strtolower($raw['type']);

/*

			//'name'        => str_replace("\u{2019}", "'", $raw['name']),  // standardize single quotes
			//'description' => str_replace("   ", "  ", $raw['fullTooltip']),

		// Conditional mana cost
		if (isset($raw['energyTooltip']))
		{
			$ability['mana_cost'] = preg_filter('/[^0-9\.]/', '', $raw['energyTooltip']);
		
			// check for mana per second
			if (strpos($raw['energyTooltip'], "per second")):
				$ability['mana_per_second'] = 1;
			endif;
		else:
			$ability['mana_cost'] = NULL;
			$ability['mana_per_second'] = 0;
		endif;
		
		// Condition cooldown
		if (isset($raw['cooldownTooltip'])):
			$ability['cooldown'] = preg_filter('/[^0-9\.]/', '', $raw['cooldownTooltip'] );
		endif;
		
		// Check for mount abilities that shouldn't have a hotkey ('Z')
		if ($ability['type'] == 'mount' && empty($ability['cooldown'])):
			unset($ability['hotkey']);
		endif;
*/		

		// Use abilityType to set the hotkey
		if ($raw['abilityType'] == 'Heroic')
		{
			$ability['hotkey'] = 'R';
		}
		elseif ($raw['abilityType'] == 'Active')
		{
			$ability['hotkey'] = '1';
		}
		elseif (strlen($raw['abilityType']) == 1)
		{
			$ability['hotkey'] = $raw['abilityType'];
		}

		return $ability;
	}
	
	/**
	 * Computes a unique hash for an ability from a raw HDP ability
	 *
	 * @param array   $raw  Raw HDP ability
	 *
	 * @return string  Ability UID
	 */
	public function abilityUid(array $raw): string
	{
		$keys = ['nameId', 'buttonId', 'abilityType'];
		$values = [];
		
		foreach ($keys as $key)
		{
			$values[] = $raw[$key];
		}
		$values[] = empty($raw['isPassive']) ? 'False' : 'True';

		$str = implode('|', $values);
		
		return substr(md5($str), 0, 7);
	}   

	/**
	 * Format hero abilities for heroes-talent
	 *
	 * @param array   $raw  Raw HDP abilities
	 *
	 * @return array  Abilities in heroes-talent format
	 */
	protected function parseAbilities(array $abilities, string $subunit = null, string $herounit = null): array
	{
		$return = [];
		
		// Types: basic, heroic, trait, hearth, mount, activable, spray, voice
		foreach ($abilities as $type => $raws)
		{
			foreach ($raws as $raw)
			{
				// Add extra info
				$raw['type']     = $type;
				$raw['subunit']  = $subunit;
				$raw['herounit'] = $herounit;

				$return[] = $this->abilityFromRaw($raw);
			}
		}
		
		return $return;
	}
	
	/**
	 * Format hero subAbilities for heroes-talent
	 *
	 * @param array   $raw  Raw HDP subAbilities
	 *
	 * @return array  Abilities in heroes-talent format
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
	 * Format heroUnit abilities for heroes-talent
	 *
	 * @param array   $raw  Raw HDP heroUnits
	 *
	 * @return array  Abilities in heroes-talent format
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
					$return = array_merge($return, $this->parseSubAbilities($unit['subAbilities'], $name));
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Standardize talents from a single raw hero
	 *
	 * @param array   $raw  Array of hero data from HDP
	 *
	 * @return array  Talents in heroes-talent format
	 */
	protected function talentsFromRaw(array $raw): array
	{
		$return = [];

		// Process talents by level
		foreach ($raw['talents'] as $level => $talents)
		{
			$level = str_replace('level', '', $level);
			
			foreach ($talents as $i => $talent)
			{
				$talent['level'] = $level;
				$return[$level][$i] = $this->talentFromRaw($talent);
			}
		}

		return $return;
	}
	
	/**
	 * Format a single talent from HDP raw to heroes-talent
	 *
	 * @param array   $raw  HDP talent
	 *
	 * @return array  Talent in heroes-talent format
	 */
	protected function talentFromRaw(array $raw): array
	{
		// Start with basic info
		$talent = [
			'tooltipId'    => $raw['buttonId'],
			'talentTreeId' => $raw['nameId'],
			'icon'         => strtolower(str_replace("'", '', $raw['icon'])), // replace single quotes in filename
			'type'         => $raw['abilityType'],
			'sort'         => $raw['sort'],
		];

/*
{
  "nameId": "ArtanisTwinBladesAmateurOpponent",
  "buttonId": "ArtanisTwinBladesAmateurOpponentTalent",
  "icon": "storm_ui_icon_artanis_doubleslash_var1.png",
  "abilityType": "W",
  "sort": 2,
  "abilityTalentLinkIds": [
	"ArtanisTwinBladesPrimed",
	"ArtanisTwinBlades"
  ]
},

{
	"tooltipId": "ArtanisTwinBladesAmateurOpponentTalent",
	"talentTreeId": "ArtanisTwinBladesAmateurOpponent",
	"name": "Amateur Opponent",
	"description": "Twin Blades attacks deal 150% bonus damage to non-Heroes.",
	"icon": "storm_ui_icon_artanis_doubleslash_var1.png",
	"type": "W",
	"sort": 2,
	"abilityId": "Artanis|W1",
	"abilityLinks": [
	  "Artanis|W1"
	]
},

			"name" => trim(str_replace("\u{2019}", "'", $talent['name'])),  // standardize single quotes
			"description" => str_replace("   ", "  ", $talent['fullTooltip']),
			
		// conditional cooldown
		if (isset($talent['cooldownTooltip'])):
			$row["cooldown"] = preg_filter('/[^0-9\.]/', "", $talent['cooldownTooltip'] );
		endif;
*/
		// Conditional quest
		if (! empty($raw['isQuest']))
		{
			$talent['isQuest'] = true;
		}

		return $talent;
	}
}
