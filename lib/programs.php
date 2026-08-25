<?php

function cfts_programs(): array
{
    return [
        'Criminology Review' => [
            'fee'  => 13500.00,
            'desc' => 'Comprehensive CLE board preparation.',
            'icon' => '👮',
        ],
    ];
}

function cfts_batch_options(): array
{
    $year = (int) date('Y');

    return [
        "Batch January {$year}",
        "Batch August {$year}",
    ];
}

function cfts_locations(): array
{
    return [
        'Tubod'    => 'Tubod, Lanao Del Norte',
        'Oroqueta' => 'Oroqueta City',
        'Ozamis'   => 'Ozamis City',
        'Iligan'   => 'Iligan City',
    ];
}
