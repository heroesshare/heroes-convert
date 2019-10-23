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
		'4a0bc2' => 'Summon Mount', // Greymane
		'ebf134' => 'Unsummon Mount',
		'88ec35' => 'Hearthstone',
		'535f70' => 'Hearthstone',
		'4cc15c' => 'Hearthstone', // Brew
		'eb6d75' => 'Quick Spray Expression',
		'8187dd' => 'Quick Voice Line Expression',
		'b4dc9c' => 'Command Water Elemental',
		'473ccb' => 'Chains of Kel\'Thuzad',
		'797e59' => 'Cancel Wraith Walk',
		'66ee35' => 'Laser Drill Issue Order',
		'3e561e' => 'Overkill Retarget',
		'bc64f9' => 'Evolve Monstrosity Active',
		'97c248' => 'Cancel Bunny Hop',
		'0479e0' => 'Shifting Meteor',
		'285893' => 'Return', // Molten Core
		'a7507f' => 'Cancel Return', // Molten Core
//		'355e2d' => 'Lava Wave', // Molten Core
		'3ce02a' => 'Empower Sulfuras', // "Active"
		'f2827a' => 'Gargantuan Stomp',
		'33434d' => 'Run and Gun', // Overkill
		'f57681' => 'Minigun', // "Active"
		'8e2bc8' => 'Holy Light', // https://github.com/HeroesToolChest/HeroesDataParser/issues/59
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
				// Check for explicit filter
				if (isset($this->abilityUids[$ability['uid']]))
				{
					unset($this->heroes[$shortname]['abilities'][$i]);
				}
				
				// Remove ancillary abilities
				if (preg_match('#(Primed|Cancel|DVa.+Off)$#', $ability['nameId']))
				{
					unset($this->heroes[$shortname]['abilities'][$i]);
				}
				
				// Remove subunit abilities for unsupported heroes
				if ($ability['sub'] && ! isset($this->subunits[$shortname]))
				{
					unset($this->heroes[$shortname]['abilities'][$i]);
				}
			}
		}
	}
}
