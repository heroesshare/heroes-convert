<?php namespace Heroes\Convert;
/*
 * heroes-convert
 *
 * Convert HeroesDataParser to heroes-talents, for Heroes of the Storm
 * https://github.com/tattersoftware/heroes-convert
 * 
 */

/**
 * Class Base
 *
 * Base class for other actions.
 */
class Base
{
	/**
	 * Master array of heroes, indexed by hyperlinkId
	 *
	 * @var array
	 */
	protected $heroes;
	
	/**
	 * Log status levels and their corresponding values
	 *
	 * @var array
	 */
	protected $logLevels = [	
		'emergency' => 1,
		'alert'     => 2,
		'critical'  => 3,
		'error'     => 4,
		'warning'   => 5,
		'notice'    => 6,
		'info'      => 7,
		'debug'     => 8,
	];
	
	/**
	 * Current log reporting threshold
	 * Can be a maximum value or array of values. 0 disables, 9 for ALL
	 *
	 * @var int|array
	 */
	protected $logLevel = [1, 2, 3, 4, 5, 8];

	/**
	 * Array of subunit names
	 *
	 * By default subunits and their abilities are ignored
	 * but specifying them here allows other classes to decide
	 * on their abilities.
	 *
	 * @var array
	 */
	protected $subunits = [
		'valeera'     => 'ValeeraStealth',
		'uther'       => 'UtherEternalDevotion',
		'tychus'      => 'TychusOdinNoHealth',
		'nazeebo'     => 'WitchDoctorGargantuan',
		'leoric'      => 'LeoricUndyingTrait',
		'greymane'    => 'GreymaneWorgenForm',
		'chen'        => 'ChenStormEarthFire',
		'lostvikings' => 'LostVikingsLongboatRaidNewer',
		'fenix'       => 'FenixPhaseBomb',
		'lucio'       => 'LucioCrossfade',
		'jaina'       => 'JainaTraitFrostbite',
		'kelthuzad'   => 'KelThuzadMasterOfTheColdDark',
		'ragnaros'    => 'RagnarosBigRag',
		'alexstrasza' => 'AlexstraszaDragon',
		'abathur'     => 'AbathurSymbiote',
		'dva'         => 'D.VaPilot',
	];

	/**
	 * Store the heroes.
	 *
	 * @param array   $heroes  Parsed hero array
	 */
	public function __construct(array $heroes)
	{
		$this->heroes = $heroes;
	}

	/**
	 * Log a message to the appropriate destination
	 * 
	 * @param string  $message  Message to log
	 * @param string  $status   Status level
	 *
	 */
	protected function logMessage($message, $status = 'debug')
	{
		if (is_int($this->logLevel) && $this->logLevel === 0)
		{
			return;
		}
		elseif (is_int($this->logLevel) && $this->logLevels[$status] > $this->logLevel)
		{
			return;
		}
		elseif (is_array($this->logLevel) && ! in_array($this->logLevels[$status], $this->logLevel))
		{
			return;
		}
		
		$log = strtoupper($status) . ' - ' . date('Y-m-d H:i:s') . ' --> ' . $message;
		
		// WIP - need to support streams/files/handlers
		echo $log . PHP_EOL;
	}
	
	/**
	 * Return the array of formatted data for all heroes
	 *
	 * @return array
	 */
	public function getHeroes(): array
	{
		return $this->heroes;
	}
	
	/**
	 * Sets the log threshold
	 *
	 * @param int|array  Maximum value or array of values
	 *
	 * @return $this
	 */
	public function setLogLevel($value)
	{
		$this->logLevel = $value;
		return $this;
	}
	
	/**
	 * Computes a unique hash for an ability or talent from an HDP ID string
	 *
	 * @param array   $raw  Raw HDP ID
	 *
	 * @return string  UID
	 */
	public function abiltalentUid(string $id): string
	{
		return substr(md5($id), 0, 6);
	}
	
	/**
	 * Searches $this->heroes for an ability
	 *
	 * @param string   $uid          Ability UID
	 * @param string   $hyperlinkId  Optional hero hyperlinkId (to improve performance)
	 *
	 * @return array   [hyperlinkId, index] to the ability 
	 */
	public function findAbility(string $uid, string $hyperlinkId = null): array
	{
		if ($hyperlinkId)
		{
			return [$hyperlinkId, $this->findHeroAbility($uid, $hyperlinkId)];
		}
		
		// Check every hero's abilities
		foreach ($this->heroes as $hyperlinkId => $hero)
		{
			foreach ($hero['abilities'] as $i => $ability)
			{
				if ($ability['uid'] == $uid)
				{
					return [$hyperlinkId, $i];
				}
			}
		}
	}
	
	/**
	 * Searches a hero for an ability
	 *
	 * @param string   $uid          Ability UID
	 * @param string   $hyperlinkId  Hero hyperlinkId
	 *
	 * @return int     index to the ability 
	 */
	public function findHeroAbility(string $uid, string $hyperlinkId): ?int
	{
		foreach ($this->heroes[$hyperlinkId]['abilities'] as $i => $ability)
		{
			if ($ability['uid'] == $uid)
			{
				return $i;
			}
		}
		
		$this->logMessage("Ability {$uid} not found for {$hyperlinkId}!", 'warning');
		
		return null;
	}
	
	/**
	 * Directly updates an ability in $this->heroes
	 *
	 * @param string   $uid          Ability UID
	 * @param string   $hyperlinkId  Hero hyperlinkId
	 * @param array    $array        Array of changes to make
	 *
	 * @return string  UID
	 */
	public function updateHeroAbility(string $uid, string $hyperlinkId, array $array)
	{
		if (! $i = $this->findHeroAbility($uid, $hyperlinkId))
		{
			throw new \RuntimeException("Ability not found: {$uid} ({$hyperlinkId})");
		}
		
		$this->heroes[$hyperlinkId]['abilities'][$i] = array_replace_recursive($this->heroes[$hyperlinkId]['abilities'][$i], $array);
	}
	
	/**
	 * Searches a hero for a talent
	 *
	 * @param string   $uid          Talent UID
	 * @param string   $hyperlinkId  Hero hyperlinkId
	 * @param int      $level        Optional level (to improve performance)
	 *
	 * @return array   [level, index] to the talent 
	 */
	public function findHeroTalent(string $uid, string $hyperlinkId, int $level = null): array
	{
		$levels = $level ? [$level => $this->heroes[$hyperlinkId]['talents'][$level]] : $this->heroes[$hyperlinkId]['talents'];
		
		foreach ($levels as $level => $talents)
		{
			foreach ($talents as $i => $talent)
			{
				if ($talent['uid'] == $uid)
				{
					return [$level, $i];
				}
			}
		}
		
		$this->logMessage("Talent {$uid} not found for {$hyperlinkId}!", 'warning');
		
		return null;
	}
	
	/**
	 * Directly updates a talent in $this->heroes
	 *
	 * @param string   $uid          Talent UID
	 * @param string   $hyperlinkId  Hero hyperlinkId
	 * @param array    $array        Array of changes to make
	 *
	 * @return string  UID
	 */
	public function updateHeroTalent(string $uid, string $hyperlinkId, array $array)
	{
		if (! $index = $this->findHeroTalent($uid, $hyperlinkId))
		{
			throw new \RuntimeException("Talent not found: {$uid} ($hyperlinkId)");
		}
		
		$this->heroes[$hyperlinkId]['talents'][$index[0]][$index[1]] =
			array_replace_recursive($this->heroes[$hyperlinkId]['talents'][$index[0]][$index[1]], $array);
	}
}
