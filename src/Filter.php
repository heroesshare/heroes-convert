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
 * Class Filter
 *
 * Removes entries not relevant to heroes-talents
 */
class Filter extends Base
{	
	/**
	 * Store the heroes.
	 *
	 * @param array  $heroes  Parsed hero array
	 */
	public function __construct(array $heroes)
	{
		parent::__construct($heroes);
	}
}
