<?php namespace Heroes\Convert;
/*
 * heroes-convert
 *
 * Convert HeroesDataParser to heroes-talents, for Heroes of the Storm
 * https://github.com/tattersoftware/heroes-convert
 * 
 */

/**
 * Class Replace
 *
 * Applies manual data overrides
 */
class Replace
{
	/**
	 * Array of heroes in heroes-talent format, indexed by shortname
	 *
	 * @var array
	 */
	protected $heroes;
	
	protected $extras = [
		'abathur'     => [1,  '0.1.1.00000'],
		'alarak'      => [2,  '1.20.0.46158'],
		'alexstrasza' => [74, '2.29.0.59657'],
		'ana'         => [72, '2.28.0.57797'],
		'anduin'      => [86, '2.45.0.73662'],
		'anubarak'    => [3,  '0.6.5.32455 '],
		'artanis'     => [4,  '1.14.2.38593'],
		'arthas'      => [5,  '0.1.1.00000'],
		'auriel'      => [6,  '1.19.4.45228'],
		'azmodan'     => [7,  '0.6.5.32455'],
		'blaze'       => [76, '2.29.7.61129'],
		'brightwing'  => [8,  '0.2.5.29775'],
		'cassia'      => [9,  '1.24.4.52124'],
		'chen'        => [10, '0.5.0.32120'],
		'chogall'     => [11, '1.15.0.39153'],
		'chromie'     => [12, '1.18.0.42958'],
		'deckard'     => [79, '2.32.0.64455'],
		'dehaka'      => [14, '1.17.0.41810'],
		'diablo'      => [15, '0.1.1.00000'],
		'dva'         => [13, '2.25.4.53548'],
		'etc'         => [16, '0.1.1.00000'],
		'falstad'     => [17, '0.1.1.00000'],
		'fenix'       => [78, '2.31.0.63507'],
		'gall'        => [18, '1.15.0.39153'],
		'garrosh'     => [19, '2.27.0.56175'],
		'gazlowe'     => [20, '0.1.1.00000'],
		'genji'       => [21, '2.25.0.52860'],
		'greymane'    => [22, '1.15.6.40087'],
		'guldan'      => [23, '1.19.0.44468'],
		'hanzo'       => [75, '2.29.3.60339'],
		'illidan'     => [24, '0.1.1.00000'],
		'imperius'    => [85, '2.42.0.71449'],
		'jaina'       => [25, '0.7.1.33182'],
		'johanna'     => [26, '1.11.1.35702'],
		'junkrat'     => [73, '2.28.3.58623'],
		'kaelthas'    => [27, '0.11.0.35360'],
		'kelthuzad'   => [71, '2.27.3.57062'],
		'kerrigan'    => [28, '0.1.1.00000'],
		'kharazim'    => [29, '1.13.0.37117'],
		'leoric'      => [30, '1.12.3.36536'],
		'lili'        => [31, '0.2.5.29775'],
		'liming'      => [32, '1.16.0.40431'],
		'lostvikings' => [56, '0.9.0.34053'],
		'ltmorales'   => [33, '1.14.0.38236'],
		'lucio'       => [34, '1.23.3.50441'],
		'lunara'      => [35, '1.15.3.39595'],
		'maiev'       => [77, '2.30.0.61952'],
		'malfurion'   => [36, '0.1.1.00000'],
		'malganis'    => [83, '2.39.0.69350'],
		'malthael'    => [37, '2.26.0.54339'],
		'medivh'      => [38, '1.18.4.43571'],
		'mephisto'    => [82, '2.37.0.67985'],
		'muradin'     => [39, '0.1.1.00000'],
		'murky'       => [40, '0.1.1.00000'],
		'nazeebo'     => [41, '0.1.1.00000'],
		'nova'        => [42, '0.1.1.00000'],
		'orphea'      => [84, '2.40.0.70200'],
		'probius'     => [43, '1.24.0.51375'],
		'qhira'       => [87, '2.47.0.75589'],
		'ragnaros'    => [44, '1.22.3.48760'],
		'raynor'      => [45, '0.1.1.00000'],
		'rehgar'      => [46, '0.2.5.31360'],
		'rexxar'      => [47, '1.13.3.37569'],
		'samuro'      => [48, '1.21.0.47219'],
		'sgthammer'   => [49, '0.1.1.00000'],
		'sonya'       => [50, '0.1.1.00000'],
		'stitches'    => [51, '0.1.1.00000'],
		'stukov'      => [52, '2.26.3.55288'],
		'sylvanas'    => [53, '0.10.0.34659'],
		'tassadar'    => [54, '0.1.1.00000'],
		'thebutcher'  => [55, '1.12.0.36144'],
		'thrall'      => [57, '0.8.0.33684'],
		'tracer'      => [58, '1.17.2.42273'],
		'tychus'      => [59, '0.1.1.00000'],
		'tyrael'      => [60, '0.1.1.00000'],
		'tyrande'     => [61, '0.1.1.00000'],
		'uther'       => [62, '0.1.1.00000'],
		'valeera'     => [63, '1.23.0.49747'],
		'valla'       => [64, '0.1.1.00000'],
		'varian'      => [65, '1.22.0.48027'],
		'whitemane'   => [81, '2.36.0.67143'],
		'xul'         => [66, '1.16.3.41150'],
		'yrel'        => [80, '2.34.0.65751'],
		'zagara'      => [67, '0.2.5.30829'],
		'zarya'       => [68, '1.20.2.46690'],
		'zeratul'     => [69, '0.1.1.00000'],
		'zuljin'      => [70, '1.22.5.49076'],
	];

	/**
	 * Load the heroes.
	 *
	 * @param array  $heroes  Heroes in heroes-talents format
	 */
	public function __construct($heroes)
	{
		$this->heroes = $heroes;
	}

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
			
			// Update the collection
			$this->heroes[$shortname] = $hero;
		}
/*
		
		// OVERRIDES
		
		// Nasty Vikings...
		if (strpos($ability['shortname'], 'LostViking') !== false && $ability['type'] == 'subunit')
			$ability['type'] = strtolower($raw['type']);
		
		// A few manual overrides (shortname => type)
		$overrides = [
			'AlexstraszaLifebinder'          => 'heroic',
			'DVaPilotBigShot'                => 'heroic',
			'DVaPilotPilotMode'              => 'trait',
			'RagnarosBigRagReturnMoltenCore' => 'trait',
			'RagnarosLavaWaveTargetPoint'    => 'heroic',
			'KelThuzadGlacialSpike'          => 'activable',
			'ImprovedIceBlock'               => 'activable',
			'LostVikingSelectAll'            => 'subunit',
		];
		if (isset($overrides[$ability['shortname']]))
			$ability['type'] = $overrides[$ability['shortname']];
*/			
		
		return $this;
	}

	/**
	 * Add heroes-talent IDs to each hero
	 *
	 * @return $this
	 */
	public function addIDs()
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
	 * Return the array of formatted data for all heroes
	 *
	 * @return array
	 */
	public function getHeroes(): array
	{
		return $this->heroes;
	}	
}
