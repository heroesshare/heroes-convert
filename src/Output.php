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
				throw new \RuntimeException('Error #' . json_last_error() . ' parsing ' . $hero['hyperlinkId'] . ': ' . json_last_error_msg());
			}
			unset($array);

			// Adjust the spacing to be compatible with heroes-talents
			$json = str_replace('    ', '  ', $json);

			// Write out to the file
			$path = $this->directory . strtolower($hero['hyperlinkId']) . '.json';
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
	 * Preps a hero for output in heroes-talent format
	 *
	 * @param array $hero
	 *
	 * @return array
	 */
	protected function prepHero(array $hero): array
	{
		// Build the hero in precise order
		$return = [
			'id'           => (int)$hero['id'],
			'shortName'    => strtolower($hero['hyperlinkId']),
			'hyperlinkId'  => $hero['hyperlinkId'],
			'attributeId'  => $hero['attributeId'],
			'cHeroId'      => $hero['cHeroId'],
			'cUnitId'      => $hero['unitId'],
			'name'         => $hero['name'],
			'icon'         => strtolower($hero['hyperlinkId']) . '.png',
			'role'         => $hero['role'],
			'expandedRole' => $hero['expandedRole'],
			'type'         => $hero['type'],
			'releaseDate'  => $hero['releaseDate'],
			'releasePatch' => $hero['releasePatch'],
			'tags'         => $hero['descriptors'] ?? [],
		];

		// Ability are categories of hyperLinkID and conditional subunit
		$abilities = [
			$hero['hyperlinkId'] => $this->prepAbilities($hero['abilities'])
		];

		// Check for subunit
		if (isset($this->subunits[$hero['hyperlinkId']]))
		{
			$subAbilities = $this->prepAbilities($hero['abilities'], true);
			
			if (count($subAbilities))
			{
				$abilities[$this->subunits[$hero['hyperlinkId']]] = $subAbilities;
			}
		}

		// Add skills
		$return['abilities'] = $abilities;
		$return['talents']   = $this->prepTalents($hero['talents']);
		
		return $this->stripNulls($return);
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
		// Build the ability in precise order
		$return = [
			'uid'           => $ability['uid'],
			'name'          => $ability['name'],
			'description'   => $ability['description'],
			'hotkey'        => $ability['hotkey'] ?? null,
			'trait'         => $ability['trait'] ?? null,
			'abilityId'     => $ability['abilityId'],
			'cooldown'      => isset($ability['cooldown']) ? (float)$ability['cooldown'] : null,
			'manaCost'      => isset($ability['manaCost']) ? (float)$ability['manaCost'] : null,
			'manaPerSecond' => isset($ability['manaPerSecond']) ? (bool)$ability['manaPerSecond'] : null,
			'icon'          => strtolower(str_replace("'", '', $ability['icon'])),
			'type'          => strtolower($ability['type']),
		];
		
		return $this->stripNulls($return);
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
		// Build the talent in precise order
		$return = [
			'tooltipId'    => $talent['buttonId'],
			'talentTreeId' => $talent['nameId'],
			'name'         => $talent['name'],
			'description'  => $talent['description'],
			'icon'         => strtolower(str_replace("'", '', $talent['icon'])),
			'type'         => $talent['abilityType'],
			'sort'         => (int)$talent['sort'],
			'cooldown'     => isset($talent['cooldown']) ? (float)$talent['cooldown'] : null,
			'isQuest'      => $talent['isQuest'] ?? null,
			'abilityId'    => $talent['abilityId'],
			'abilityLinks' => $talent['abilityLinks'] ?? null,
		];
		
		return $this->stripNulls($return);
	}
	
	/**
	 * Strips null values from an array
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	protected function stripNulls(array $array): array
	{
		return array_filter($array, function($value)
			{
				return ! is_null($value);
			}
		);
	}
}
