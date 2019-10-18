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
	 * Array of heroes in heroes-talent format, indexed by shortname
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
	 * Log a message to the appropriate destination
	 * 
	 * @param string  $message  Message to log
	 * @param string  $status   Status level
	 *
	 */
	public function logMessage($message, $status = 'debug')
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
}
