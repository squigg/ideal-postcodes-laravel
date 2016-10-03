<?php

return [

    'base_url'   => 'https://api.ideal-postcodes.co.uk/v1/',
    'api_key'    => env('IDEALPOSTCODES_API_KEY', 'iddqd'),
    'timeout'    => 5,
    'limit'      => 50,
    'collection' => true,
    'model'      => '\Path\To\Laravel\Address\Model',
    'forceFill'  => false,
    'fields'     => [
        'sub_building_name',
        'building_name',
        'building_number',
        ['thoroughfare' => 'main_street'],      // This maps a particular API field to a custom named field in your model/array
        'post_town',
        'postcode',
        'premise',
        'udprn',
        'line_1',
        'line_2',
        'line_3',
        'country',
    ],

];
