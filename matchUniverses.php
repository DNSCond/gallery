<?php $Favi_verse = [
    'Favicond-Main' => 'Favicond\'s Friendgroup',
    'Favicond-Unknown' => 'Unknown Universe',
    'staff' => 'Representation elsewhere',
    'RecycleReady' => 'Ready For Recycling',
    'Favicond-Magical' => 'Magical Entities',
    'TealLime' => 'Teal and Lime',
    'MoonSun' => 'Moon and Sun',
    'inverConds' => 'Animalcond',
    'Favicond-2' => 'Dresscond',
    'Favicond-All' => 'All',
    'CountryHumans' => 'Country Humans',
    'AttachedEdu' => 'Attached Education'
];
function matchUniverses(string $universe): string
{
    global $Favi_verse;
    if (array_key_exists($universe, $Favi_verse)) {
        return $Favi_verse[$universe];
    } else return $universe;
}
