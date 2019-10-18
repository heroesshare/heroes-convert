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
	 * Ability UIDs to filter
	 *
	 * @var array
	 */
	protected $abilityUids = [
		'48f7bd' => 'Summon Mount',
		'ebf134' => 'Unsummon Mount',
		'88ec35' => 'Hearthstone',
		'535f70' => 'Hearthstone',
		'eb6d75' => 'Quick Spray Expression',
		'8187dd' => 'Quick Voice Line Expression',
	];
	
	/**
	 * Remove unwanted items from the heroes array
	 *
	 * @return $this
	 */
	public function run()
	{
		$this->filterAbilities();
		
		return $this;
	}

	/**
	 * Check abilities against the filter lists
	 */
	protected function filterAbilities()
	{
		// Traverse heroes for each ability
		foreach ($this->heroes as $shortname => $hero)
		{
			foreach ($hero['abilities'] as $i => $ability)
			{
				if (isset($this->abilityUids[$ability['uid']]))
				{
					unset($this->heroes[$shortname]['abilities'][$i]);
				}
				
				// Remove ancillary abilities
				if (preg_match('#(Primed|Cancel)$#', $ability['nameId']))
				{
					unset($this->heroes[$shortname]['abilities'][$i]);
				}
			}
		}
	}
}
