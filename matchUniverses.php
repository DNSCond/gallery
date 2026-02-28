<?php
function matchUniverses(string $universe): string
{
    return match ($universe) {
        'Favicond-Main' => 'Favicond\'s Friendgroup',
        'Favicond-Unknown' => 'Unknown Universe',
        'staff' => 'Representation elsewhere',
        'RecycleReady' => 'Ready For Recycling',
        "Favicond-Magical" => 'Magical Entities',
        'TealLime' => 'Teal And Lime',
        'inverConds' => 'Animalcond',
        'Favicond-2' => 'Dresscond',
        'Favicond-All' => 'All',
        default => "$universe",
    };
}
