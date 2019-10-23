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
 * Class Replace
 *
 * Applies manual data overrides
 */
class Intervene extends Base
{
	/**
	 * Array of additional info not found in HDP
	 * [heroesTalentId, releasePatch]
	 *
	 * @var array
	 */
	protected $extras = [
		'abathur'     => [1,  '0.1.1.00000'],
		'alarak'      => [56, '1.20.0.46158'],
		'alexstrasza' => [74, '2.29.0.59657'],
		'ana'         => [72, '2.28.0.57797'],
		'anduin'      => [86, '2.45.0.73662'],
		'anubarak'    => [31, '0.6.5.32455'],
		'artanis'     => [43, '1.14.2.38593'],
		'arthas'      => [2,  '0.1.1.00000'],
		'auriel'      => [55, '1.19.4.45228'],
		'azmodan'     => [30, '0.6.5.32455'],
		'blaze'       => [76, '2.29.7.61129'],
		'brightwing'  => [25, '0.2.5.29775'],
		'cassia'      => [65, '1.24.4.52124'],
		'chen'        => [29, '0.5.0.32120'],
		'chogall'     => [45, '1.15.0.39153'],
		'chromie'     => [52, '1.18.0.42958'],
		'deckard'     => [79, '2.32.0.64455'],
		'dehaka'      => [50, '1.17.0.41810'],
		'diablo'      => [3,  '0.1.1.00000'],
		'dva'         => [67, '2.25.4.53548'],
		'etc'         => [4,  '0.1.1.00000'],
		'falstad'     => [5,  '0.1.1.00000'],
		'fenix'       => [78, '2.31.0.63507'],
		'gall'        => [44, '1.15.0.39153'],
		'garrosh'     => [70, '2.27.0.56175'],
		'gazlowe'     => [6,  '0.1.1.00000'],
		'genji'       => [66, '2.25.0.52860'],
		'greymane'    => [47, '1.15.6.40087'],
		'guldan'      => [54, '1.19.0.44468'],
		'hanzo'       => [75, '2.29.3.60339'],
		'illidan'     => [7,  '0.1.1.00000'],
		'imperius'    => [85, '2.42.0.71449'],
		'jaina'       => [32, '0.7.1.33182'],
		'johanna'     => [37, '1.11.1.35702'],
		'junkrat'     => [73, '2.28.3.58623'],
		'kaelthas'    => [36, '0.11.0.35360'],
		'kelthuzad'   => [71, '2.27.3.57062'],
		'kerrigan'    => [8,  '0.1.1.00000'],
		'kharazim'    => [40, '1.13.0.37117'],
		'leoric'      => [39, '1.12.3.36536'],
		'lili'        => [24, '0.2.5.29775'],
		'liming'      => [48, '1.16.0.40431'],
		'lostvikings' => [34, '0.9.0.34053'],
		'ltmorales'   => [42, '1.14.0.38236'],
		'lucio'       => [63, '1.23.3.50441'],
		'lunara'      => [46, '1.15.3.39595'],
		'maiev'       => [77, '2.30.0.61952'],
		'malfurion'   => [9,  '0.1.1.00000'],
		'malganis'    => [83, '2.39.0.69350'],
		'malthael'    => [68, '2.26.0.54339'],
		'medivh'      => [53, '1.18.4.43571'],
		'mephisto'    => [82, '2.37.0.67985'],
		'muradin'     => [10, '0.1.1.00000'],
		'murky'       => [26, '0.1.1.00000'],
		'nazeebo'     => [11, '0.1.1.00000'],
		'nova'        => [12, '0.1.1.00000'],
		'orphea'      => [84, '2.40.0.70200'],
		'probius'     => [64, '1.24.0.51375'],
		'qhira'       => [87, '2.47.0.75589'],
		'ragnaros'    => [60, '1.22.3.48760'],
		'raynor'      => [13, '0.1.1.00000'],
		'rehgar'      => [28, '0.2.5.31360'],
		'rexxar'      => [41, '1.13.3.37569'],
		'samuro'      => [58, '1.21.0.47219'],
		'sgthammer'   => [14, '0.1.1.00000'],
		'sonya'       => [15, '0.1.1.00000'],
		'stitches'    => [16, '0.1.1.00000'],
		'stukov'      => [69, '2.26.3.55288'],
		'sylvanas'    => [35, '0.10.0.34659'],
		'tassadar'    => [17, '0.1.1.00000'],
		'thebutcher'  => [38, '1.12.0.36144'],
		'thrall'      => [33, '0.8.0.33684'],
		'tracer'      => [51, '1.17.2.42273'],
		'tychus'      => [23, '0.1.1.00000'],
		'tyrael'      => [18, '0.1.1.00000'],
		'tyrande'     => [19, '0.1.1.00000'],
		'uther'       => [20, '0.1.1.00000'],
		'valeera'     => [62, '1.23.0.49747'],
		'valla'       => [21, '0.1.1.00000'],
		'varian'      => [59, '1.22.0.48027'],
		'whitemane'   => [81, '2.36.0.67143'],
		'xul'         => [49, '1.16.3.41150'],
		'yrel'        => [80, '2.34.0.65751'],
		'zagara'      => [27, '0.2.5.30829'],
		'zarya'       => [57, '1.20.2.46690'],
		'zeratul'     => [22, '0.1.1.00000'],
		'zuljin'      => [61, '1.22.5.49076'],
	];

	/**
	 * Apply the overrides defined in this class.
	 *
	 * @return $this
	 */
	public function run()
	{
		// Process each hero
		foreach ($this->heroes as $shortname => $hero)
		{
			// Check for a match
			if (empty($this->extras[$shortname]))
			{
				throw new \RuntimeException('Supplemental data missing for hero: ' . $hero['name']);
			}
			
			// Apply release patch and heroes-talent ID
			$hero['id']           = $this->extras[$shortname][0];
			$hero['releasePatch'] = $this->extras[$shortname][1];
			
			// Process each ability
			foreach ($hero['abilities'] as $i => $ability)
			{
				// Set hotkeys on traits that have cooldowns
				if ($ability['type'] == 'trait' && ! empty($ability['cooldown']))
				{
					$hero['abilities'][$i]['hotkey'] = 'D';
				}
				
				// Check for mana costs per second
				if (isset($ability['manaCost']) && strlen($ability['manaCost']) > 3)
				{
					$hero['abilities'][$i]['manaCost']      = filter_var($ability['manaCost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
					$hero['abilities'][$i]['manaPerSecond'] = true;
				}
			}
			
			// Update the collection
			$this->heroes[$shortname] = $hero;
		}
		
		// Move quest-unlocked activables to primary abilities
		$this->updateHeroAbility('fbd5a2', 'jaina', ['sub' => false]); // ImprovedIceBlock
		$this->updateHeroAbility('870ba5', 'kelthuzad', ['sub' => false]); // KelThuzadGlacialSpike
		
		// Change heroic summons' abilities to subunit type
		$this->updateHeroAbility('fd5603', 'nazeebo', ['type' => 'subunit']); // WitchDoctorGargantuanStompCommand
		
		// Set ability links on talents that reference other talents
		$this->talentAbilityLinkTalents();

		/*** Specific heroes ***/
		
		// Malthael has the only talent that modifies generic mount
		$this->updateHeroTalent('3e52a9', 'malthael', ['abilityLinks' => ['Malthael|Z']]); // MalthaelOnAPaleHorse

		// VarianTwinBladesOfFury -> VarianTwinBladesofFury (notice case on "of/Of")
		// https://github.com/HeroesToolChest/HeroesDataParser/issues/60
		$this->updateHeroTalent('21df2d', 'varian', ['abilityLinks' => ['Varian|R3']]);
		
		// Change Chen's Breath of Fire to be a primary ability
		$this->updateHeroAbility('727df0', 'chen', ['sub' => false]); // ChenBreathOfFire
				
		// Set TLV tags (they are usually on each viking)
		$this->heroes['lostvikings']['tags'] = [
			'Escaper',
			'Helper',
			'Overconfident',
			'RoleAutoAttacker',
			'RoleTank',
			'WaveClearer'
		];
/*
		// A few overrides from the old version to look through at some point...
		
		// Nasty Vikings...
		if (strpos($ability['shortname'], 'LostViking') !== false && $ability['type'] == 'subunit')
			$ability['type'] = strtolower($raw['type']);
		
		// A few manual overrides (shortname => type)
		$overrides = [
			'DVaPilotBigShot'                => 'heroic',
			'DVaPilotPilotMode'              => 'trait',
			'LostVikingSelectAll'            => 'subunit',
		];
		if (isset($overrides[$ability['shortname']]))
			$ability['type'] = $overrides[$ability['shortname']];
*/			
		
		return $this;
	}

	/**
	 * Set subunits on heroes that use them
	 *
	 * @return $this
	 */
	protected function addSubunits()
	{
		// Process each hero
		foreach ($this->heroes as $shortname => $hero)
		{
			// Add the ID field
			$this->heroes = $hero;
			$raw['cHeroId'] = $cHeroId;
			
			// Parse it into heroes-talent format
			$hero = $this->heroFromRaw($raw);
			
			// Add it to the collection
			$this->heroes[$hero['shortname']] = $hero;
		}

		return $this;
	}

	/**
	 * Set ability links on talents that reference other talents
	 *
	 * Since abilities gained from talents don't show up in the normal ability
	 * list they aren't linked properly when another talent modifies them. This
	 * is pretty rare so we'll handle it manually for now.
	 *
	 * @return $this
	 */
	protected function talentAbilityLinkTalents()
	{
		// GarroshArmorUpInnerRage -> GarroshArmorUpBodyCheck
		$this->updateHeroTalent('92ed8c', 'garrosh', ['abilityLinks' => ['Garrosh|11']]);

		// OrpheaInvasiveMiasma -> OrpheaOverflowingChaosInvasiveMiasma
		$this->updateHeroTalent('a389cd', 'orphea',  ['abilityLinks' => ['Orphea|Trait'], 'abilityId' => 'Orphea|Trait']);

		// VarianBannersGloryToTheAlliance -> VarianBannerOf*
		$this->updateHeroTalent('71509a', 'varian',  ['abilityLinks' => ['Varian|11', 'Varian|12', 'Varian|13']]);

		// YrelDivineSteed -> YrelDivineSteedSummonMount
		$this->updateHeroTalent('194af7', 'yrel',    ['abilityLinks' => ['Yrel|Z']]);
	}
}
