<?php namespace Heroes\Convert;
/*
 * heroes-convert
 *
 * Convert HeroesDataParser to heroes-talents, for Heroes of the Storm
 * https://github.com/tattersoftware/heroes-convert
 * 
 */

/**
 * Class Locale
 *
 * Add locale-specific names and descriptions to parsed hero data
 */
class Locale
{
	/**
	 * Array of game strings from HDP file.
	 *
	 * @var array
	 */
	protected $gamestrings;
	
	/**
	 * Load data from the the game strings file.
	 *
	 * @param array     $heroes           Parsed hero array
	 * @param string    $gamestringsPath  Path to a valid gamestrings file
	 */
	public function __construct(array $heroes, string $gamestringsPath)
	{
		parent::__construct($heroes);
		
		$this->gamestrings = $this->loadStrings($gamestringsPath);
	}

	/**
	 * Verify a file path and returns the parsed JSON contents as an array
	 *
	 * @param string    $path Path to a JSON file
	 *
	 * @return array
	 */
	protected function loadStrings(string $path): array
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
		if (! isset($array['meta']['version']))
		{
			throw new \RuntimeException('Invalid gameStrings file: ' . $path);
		}
		
		echo 'Gamestrings loaded: version ' . $array['meta']['version'] . ', locale ' . $array['meta']['locale'] . PHP_EOL;

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
	 * Update the heroes array with HDP gamestrings
	 *
	 * @return $this
	 */
	public function run()
	{
		$strings = [];
		
		$this->addAbilityStrings();
		
		return $this;
	}

	/**
	 * Add relevant ability strings to each ability
	 *
	 * @return array
	 */
	protected function addAbilityStrings()
	{
		// Get each set of strings
		$strings['names']        = $this->abilityNames($this->gamestrings['abiltalent']['name']);
		$strings['cooldowns']    = $this->abilityCooldowns($this->gamestrings['abiltalent']['cooldown']);
		$strings['manaCosts']    = $this->abilityManaCosts($this->gamestrings['abiltalent']['energy']);
		$strings['descriptions'] = $this->abilityDescriptions($this->gamestrings['abiltalent']['full']);
	
	}

	/**
	 * Fetch ability names by their UID
	 *
	 * @param array   $cooldowns  HDP name gamestrings
	 *
	 * @return array
	 */
	protected function abilityNames(array $names): array
	{
		$return = [];
		
		foreach ($names as $id => $name)
		{
			// Hash the UID
			$uid = $this->abilityUid($id);
			
			$return[$uid] = $name;
		}
		
		return $return;		
	}

	/**
	 * Fetch ability cooldowns by their UID
	 *
	 * @param array   $cooldowns  HDP cooldown gamestrings
	 *
	 * @return array
	 */
	protected function abilityCooldowns(array $cooldowns): array
	{
		$return = [];
		
		foreach ($cooldowns as $id => $cooldown)
		{
			// Hash the UID
			$uid = $this->abilityUid($id);
			
			// Strip everything but the number of seconds
			$cooldown = preg_filter('#\d+(\.\d)?#', $cooldown);
			
			$return[$uid] = $cooldown;
		}
		
		return $return;		
	}

	/**
	 * Fetch ability mana costs by their UID
	 *
	 * @param array   $costs  HDP energy gamestrings
	 *
	 * @return array
	 */
	protected function abilityManaCosts(array $costs): array
	{
		$return = [];
		
		foreach ($costs as $id => $cost)
		{
			// Hash the UID
			$uid = $this->abilityUid($id);
			
			// Find the space before the actual cost
			$pos = strrpos($cost, ' ');
			
			// Lop everything before and trim the final "</s>"
			$cost = substr($cost, $pos, -4);
						
			$return[$uid] = $cost;
		}
		
		return $return;		
	}

	/**
	 * Fetch ability descriptions by their UID
	 *
	 * @param array   $cooldowns  HDP description gamestrings
	 *
	 * @return array
	 */
	protected function abilityDescriptions(array $descriptions): array
	{
		$return = [];
		
		foreach ($descriptions as $id => $description)
		{
			// Hash the UID
			$uid = $this->abilityUid($id);
			
/*
"Alarak targets an area and channels for <c val=\"bfd4fd\">1</c> second,
becoming Protected and Unstoppable. After, if he took damage from an enemy
Hero, he sends a shockwave that deals <c val=\"bfd4fd\">275~~0.04~~</c> damage.",
*/

			$return[$uid] = $description;
		}
		
		return $return;		
	}
	
	/**
	 * Computes a unique hash for an ability from an HDP ID string
	 *
	 * @param array   $raw  Raw HDP ability
	 *
	 * @return string  Ability UID
	 */
	public function abilityUid(string $id): string
	{
		return substr(md5($str), 0, 7);
	}   
	
}
