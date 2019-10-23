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
 * Class Output
 *
 * Writes finalized data out to individual files.
 */
class Output extends Base
{
	/**
	 * Target directory for files
	 *
	 * @var string
	 */
	public $directory;
	
	/**
	 * Store the heroes.
	 *
	 * @param array  $heroes  Parsed hero array
	 */
	public function __construct(array $heroes)
	{
		parent::__construct($heroes);
		
		$this->directory = __DIR__ . DIRECTORY_SEPARATOR . 'hero' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Write each hero to its own file
	 *
	 * @return $this
	 */
	public function run()
	{
		$this->ensureDirectory();
		$count = 0;
		
		foreach ($this->heroes as $hero)
		{
			$array = $this->prepHero($hero);
			
			// JSON encode with spacing
			$json = json_encode($array, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			if ($json === false)
			{
				var_dump($array);
				throw new \RuntimeException('Error #' . json_last_error() . ' parsing ' . $hero['shortName'] . ': ' . json_last_error_msg());
			}

			// Adjust the spacing to be compatible with heroes-talents
			$json = str_replace('    ', '  ', $json);

			// Write out to the file
			$path = $this->directory . $hero['shortName'] . '.json';
			$result = file_put_contents($path, $json);
			
			if (empty($result))
			{
				throw new \RuntimeException('Unable to write data to file: ' . $path);
			}
			
			$count++;
		}
		
		$this->logMessage("{$count} heroes converted to {$this->directory}");
	}

	/**
	 * Change the output directory
	 *
	 * @return $this
	 */
	public function setDirectory(string $directory)
	{
		$this->directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		return $this;
	}	

	/**
	 * Checks for and creates (if necessary) the target directory
	 */
	protected function ensureDirectory()
	{
		if (is_dir($this->directory))
		{
			return;
		}
		
		if (! mkdir($this->directory, 0775, true))
		{
			throw new \RuntimeException('Unable to create target directory: ' . $this->directory);
		}
	}
	
	/**
	 * Preps a hero for output
	 *
	 * @param array $hero
	 *
	 * @return array
	 */
	protected function prepHero(array $hero): array
	{
		$return = [];
		
		// Build the array in precise order
		foreach (['id', 'shortName', 'attributeId', 'cHeroId', 'cUnitId', 'name', 'icon', 
			'role', 'expandedRole', 'type', 'releaseDate', 'releasePatch', 'tags'] as $key)
		{
			if (isset($hero[$key]))
			{
				// Make sure numericals are numbers
				$return[$key] = is_numeric($hero[$key]) ? (float)$hero[$key] : $hero[$key];
			}
		}
		
		// Abilities are categorized by hero's name and conditional subunit
		$abilities = [
			$hero['hyperlinkId'] => $this->prepAbilities($hero['abilities'])
		];

		// Check for subunit
		if (isset($this->subunits[$hero['shortName']]))
		{
			$abilities[$this->subunits[$hero['shortName']]] = $this->prepAbilities($hero['abilities'], true);
		}

		$return['abilities'] = $abilities;
		
		$return['talents']   = $this->prepTalents($hero['talents']);
		
		return $return;
	}
	
	/**
	 * Preps one hero's abilities for output
	 *
	 * @param array $abilities
	 * @param bool  $sub  Whether to return just sub abilities
	 *
	 * @return array
	 */
	protected function prepAbilities(array $abilities, $sub = false): array
	{
		// Assign abilities in likely cast order
		$sorted = array_flip(['Q1', 'W1', 'E1', 'R1', 'R2', 'R3', 'D1', 'Z1', '11', '21', '31', '41', 'Q2', 'W2', 'E2', 'D2', 'D3']);

		foreach ($abilities as $ability)
		{
			// Skip primary abilities when subunit is requested, and vice versa
			if (! ($sub == $ability['sub']))
			{
				continue;
			}

			$sorted[$ability['code']] = $this->prepAbility($ability);
		}
		
		$return = [];
		foreach ($sorted as $value)
		{
			if (is_array($value))
			{
				$return[] = $value;
			}
		}
		
		return $return;
	}
	
	/**
	 * Preps an ability for output
	 *
	 * @param array $ability
	 *
	 * @return array
	 */
	protected function prepAbility(array $ability): array
	{
		$return = ['uid' => $ability['uid']];
		
		// Build the array in precise order
		foreach (['name', 'description', 'hotkey', 'trait', 'abilityId',
			'cooldown', 'manaCost', 'manaPerSecond', 'icon', 'type'] as $key)
		{
			if (isset($ability[$key]))
			{
				// Hotkeys are always strings
				if ($key == 'hotkey')
				{
					$return[$key] = (string)$ability[$key];
				}
				// Make sure numericals are numbers
				elseif (is_numeric($ability[$key]))
				{
					$return[$key] = (float)$ability[$key];
				}
				else 
				{
					$return[$key] = (string)$ability[$key];
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Preps one hero's talents for output
	 *
	 * @param array $talents
	 *
	 * @return array
	 */
	protected function prepTalents(array $talents): array
	{
		$return = [];
		
		// Talents are already in level order
		foreach ($talents as $level => $lTalents)
		{
			$sorted = [];
			
			foreach ($lTalents as $talent)
			{
				// Order by 'sort'
				$sorted[$talent['sort']] = $this->prepTalent($talent);
			}
			
			ksort($sorted);
			
			$return[$level] = array_values($sorted);
		}
		
		return $return;
	}
	
	/**
	 * Preps a talent for output
	 *
	 * @param array $talent
	 *
	 * @return array
	 */
	protected function prepTalent(array $talent): array
	{
		$return = [];
		
		// Build the array in precise order
		foreach (['tooltipId', 'talentTreeId', 'name', 'description', 'icon',
			'type', 'sort', 'cooldown', 'isQuest', 'abilityId', 'abilityLinks'] as $key)
		{
			if (isset($talent[$key]))
			{
				// Make sure numericals are numbers
				$return[$key] = is_numeric($talent[$key]) ? (float)$talent[$key] : $talent[$key];
			}
		}
		
		return $return;
	}
}
