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
class Filter
{	
	/**
	 * Array of heroes in heroes-talent format, indexed by shortname
	 *
	 * @var array
	 */
	protected $heroes;
	
	/**
	 * Store the heroes.
	 *
	 * @param array     $heroes  Parsed hero array
	 */
	public function __construct(array $heroes)
	{
		$this->heroes = $heroes;
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
}
