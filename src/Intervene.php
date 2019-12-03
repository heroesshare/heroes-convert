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
	 * hyperlinkId => [heroesTalentId, releasePatch]
	 *
	 * @var array
	 */
	protected $extras = [
		'Abathur'     => [1,  '0.1.1.00000'],
		'Alarak'      => [56, '1.20.0.46158'],
		'Alexstrasza' => [74, '2.29.0.59657'],
		'Ana'         => [72, '2.28.0.57797'],
		'Anduin'      => [86, '2.45.0.73662'],
		'Anubarak'    => [31, '0.6.5.32455'],
		'Artanis'     => [43, '1.14.2.38593'],
		'Arthas'      => [2,  '0.1.1.00000'],
		'Auriel'      => [55, '1.19.4.45228'],
		'Azmodan'     => [30, '0.6.5.32455'],
		'Blaze'       => [76, '2.29.7.61129'],
		'Brightwing'  => [25, '0.2.5.29775'],
		'Cassia'      => [65, '1.24.4.52124'],
		'Chen'        => [29, '0.5.0.32120'],
		'Chogall'     => [45, '1.15.0.39153'],
		'Chromie'     => [52, '1.18.0.42958'],
		'Deathwing'   => [88, '2.49.0.77525'],
		'Deckard'     => [79, '2.32.0.64455'],
		'Dehaka'      => [50, '1.17.0.41810'],
		'Diablo'      => [3,  '0.1.1.00000'],
		'DVa'         => [67, '2.25.4.53548'],
		'ETC'         => [4,  '0.1.1.00000'],
		'Falstad'     => [5,  '0.1.1.00000'],
		'Fenix'       => [78, '2.31.0.63507'],
		'Gall'        => [44, '1.15.0.39153'],
		'Garrosh'     => [70, '2.27.0.56175'],
		'Gazlowe'     => [6,  '0.1.1.00000'],
		'Genji'       => [66, '2.25.0.52860'],
		'Greymane'    => [47, '1.15.6.40087'],
		'Guldan'      => [54, '1.19.0.44468'],
		'Hanzo'       => [75, '2.29.3.60339'],
		'Illidan'     => [7,  '0.1.1.00000'],
		'Imperius'    => [85, '2.42.0.71449'],
		'Jaina'       => [32, '0.7.1.33182'],
		'Johanna'     => [37, '1.11.1.35702'],
		'Junkrat'     => [73, '2.28.3.58623'],
		'Kaelthas'    => [36, '0.11.0.35360'],
		'KelThuzad'   => [71, '2.27.3.57062'],
		'Kerrigan'    => [8,  '0.1.1.00000'],
		'Kharazim'    => [40, '1.13.0.37117'],
		'Leoric'      => [39, '1.12.3.36536'],
		'LiLi'        => [24, '0.2.5.29775'],
		'LiMing'      => [48, '1.16.0.40431'],
		'LostVikings' => [34, '0.9.0.34053'],
		'LtMorales'   => [42, '1.14.0.38236'],
		'Lucio'       => [63, '1.23.3.50441'],
		'Lunara'      => [46, '1.15.3.39595'],
		'Maiev'       => [77, '2.30.0.61952'],
		'Malfurion'   => [9,  '0.1.1.00000'],
		'MalGanis'    => [83, '2.39.0.69350'],
		'Malthael'    => [68, '2.26.0.54339'],
		'Medivh'      => [53, '1.18.4.43571'],
		'Mephisto'    => [82, '2.37.0.67985'],
		'Muradin'     => [10, '0.1.1.00000'],
		'Murky'       => [26, '0.1.1.00000'],
		'Nazeebo'     => [11, '0.1.1.00000'],
		'Nova'        => [12, '0.1.1.00000'],
		'Orphea'      => [84, '2.40.0.70200'],
		'Probius'     => [64, '1.24.0.51375'],
		'Qhira'       => [87, '2.47.0.75589'],
		'Ragnaros'    => [60, '1.22.3.48760'],
		'Raynor'      => [13, '0.1.1.00000'],
		'Rehgar'      => [28, '0.2.5.31360'],
		'Rexxar'      => [41, '1.13.3.37569'],
		'Samuro'      => [58, '1.21.0.47219'],
		'SgtHammer'   => [14, '0.1.1.00000'],
		'Sonya'       => [15, '0.1.1.00000'],
		'Stitches'    => [16, '0.1.1.00000'],
		'Stukov'      => [69, '2.26.3.55288'],
		'Sylvanas'    => [35, '0.10.0.34659'],
		'Tassadar'    => [17, '0.1.1.00000'],
		'TheButcher'  => [38, '1.12.0.36144'],
		'Thrall'      => [33, '0.8.0.33684'],
		'Tracer'      => [51, '1.17.2.42273'],
		'Tychus'      => [23, '0.1.1.00000'],
		'Tyrael'      => [18, '0.1.1.00000'],
		'Tyrande'     => [19, '0.1.1.00000'],
		'Uther'       => [20, '0.1.1.00000'],
		'Valeera'     => [62, '1.23.0.49747'],
		'Valla'       => [21, '0.1.1.00000'],
		'Varian'      => [59, '1.22.0.48027'],
		'Whitemane'   => [81, '2.36.0.67143'],
		'Xul'         => [49, '1.16.3.41150'],
		'Yrel'        => [80, '2.34.0.65751'],
		'Zagara'      => [27, '0.2.5.30829'],
		'Zarya'       => [57, '1.20.2.46690'],
		'Zeratul'     => [22, '0.1.1.00000'],
		'Zuljin'      => [61, '1.22.5.49076'],
	];

	/**
	 * Apply the overrides defined in this class.
	 *
	 * @return $this
	 */
	public function run()
	{
		// Process each hero
		foreach ($this->heroes as $hyperlinkId => $hero)
		{
			// Check for a match
			if (empty($this->extras[$hyperlinkId]))
			{
				throw new \RuntimeException('Supplemental data missing for hero: ' . $hero['name']);
			}
			
			// Apply release patch and heroes-talent ID
			$hero['id']           = $this->extras[$hyperlinkId][0];
			$hero['releasePatch'] = $this->extras[$hyperlinkId][1];
			
			// Collapse roles to a single value (e.g. Tassadar, Varian)
			$role = explode(',', $hero['role']);
			$hero['role'] = reset($role);
			
			// Process each ability
			foreach ($hero['abilities'] as $i => $ability)
			{
				// Set hotkeys on traits that have cooldowns
				if ($ability['type'] == 'trait' && ! empty($ability['cooldown']))
				{
					$hero['abilities'][$i]['hotkey'] = 'D';
				}
				// Remove hotkeys on traits without cooldowns
				elseif ($ability['type'] == 'trait' && empty($ability['cooldown']))
				{
					unset($hero['abilities'][$i]['hotkey']);
				}
				
				// Check for mana costs per second
				if (isset($ability['manaCost']) && strlen($ability['manaCost']) > 3)
				{
					$hero['abilities'][$i]['manaCost']      = filter_var($ability['manaCost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
					$hero['abilities'][$i]['manaPerSecond'] = true;
				}
			}
			
			// Update the collection
			$this->heroes[$hyperlinkId] = $hero;
		}
		
		// Move quest-unlocked activables to primary abilities
		$this->updateHeroAbility('fbd5a2', 'Jaina', ['sub' => false]); // ImprovedIceBlock
		$this->updateHeroAbility('870ba5', 'KelThuzad', ['sub' => false]); // KelThuzadGlacialSpike
		
		// Change heroic summons' abilities to subunit type
		$this->updateHeroAbility('fd5603', 'Nazeebo', ['type' => 'subunit']); // WitchDoctorGargantuanStompCommand
		
		// Set ability links on talents that reference other talents
		$this->talentAbilityLinkTalents();

		// Tweak some inconsistent trait hotkeys
		// https://github.com/HeroesToolChest/HeroesDataParser/issues/64
		$this->updateHeroAbility('c6b01f', 'Abathur', ['hotkey' => null]); // LocustStrain
		$this->updateHeroAbility('0c414a', 'Artanis', ['hotkey' => null]); // ShieldOverload
		$this->updateHeroAbility('c3bc73', 'Falstad', ['hotkey' => null]); // Tailwind
		$this->updateHeroAbility('9109fa', 'Varian',  ['hotkey' => null]); // HeroicStrike
		$this->updateHeroAbility('0bdf5b', 'Gazlowe', ['hotkey' => 'D']);  // Salvager
		$this->updateHeroAbility('3e1bbf', 'Rexxar',  ['hotkey' => 'D']);  // MishaFocus		

		/*** Specific heroes ***/
		
		// Change Chen's Breath of Fire to be a primary ability
		$this->updateHeroAbility('727df0', 'Chen', ['sub' => false]); // ChenBreathOfFire
		
		// Morale's Healing Beam is actually a toggleCooldown but we set it anyways
		$this->updateHeroAbility('cd68c0', 'LtMorales', ['cooldown' => 0.5]); // MedicHealingBeam
		
		// Malthael has the only talent that modifies generic mount
		$this->updateHeroTalent('3e52a9', 'Malthael', ['abilityLinks' => ['Malthael|Z']]); // MalthaelOnAPaleHorse
				
		// Set TLV tags (originally on each individual Viking)
		$this->heroes['LostVikings']['descriptors'] = [
			'Escaper',
			'Helper',
			'Overconfident',
			'RoleAutoAttacker',
			'RoleTank',
			'WaveClearer'
		];

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
		foreach ($this->heroes as $hyperlinkId => $hero)
		{
			// Add the ID field
			$this->heroes = $hero;
			$raw['cHeroId'] = $cHeroId;
			
			// Parse it into heroes-talent format
			$hero = $this->heroFromRaw($raw);
			
			// Add it to the collection
			$this->heroes[$hyperlinkId] = $hero;
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
		$this->updateHeroTalent('92ed8c', 'Garrosh', ['abilityLinks' => ['Garrosh|11']]);

		// OrpheaInvasiveMiasma -> OrpheaOverflowingChaosInvasiveMiasma
		$this->updateHeroTalent('a389cd', 'Orphea',  ['abilityLinks' => ['Orphea|Trait'], 'abilityId' => 'Orphea|Trait']);

		// VarianBannersGloryToTheAlliance -> VarianBannerOf*
		$this->updateHeroTalent('71509a', 'Varian',  ['abilityLinks' => ['Varian|11', 'Varian|12', 'Varian|13']]);

		// YrelDivineSteed -> YrelDivineSteedSummonMount
		$this->updateHeroTalent('194af7', 'Yrel',    ['abilityLinks' => ['Yrel|Z']]);
	}
}
