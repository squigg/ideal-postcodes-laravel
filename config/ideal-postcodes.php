<?php

return [

    /* Your API key from Ideal Postcodes. This defaults to checking your ENV file for an IDEALPOSTCODES_API_KEY entry */
    'api_key'    => env('IDEALPOSTCODES_API_KEY', 'iddqd'),

    /* Base URL for the API endpoints. Currently only v1 is supported */
    'base_url'   => 'https://api.ideal-postcodes.co.uk/v1/',

    /* Request timeout in seconds */
    'timeout'    => 5,

    /* Maximum records to be returned */
    'limit'      => 50,

    /* The AddressCollectionTransformer class that determines how an array of addresses is returned    */
    'collectionTransformer' => \Squigg\IdealPostcodes\Transformers\CollectionTransformer::class,

    /* The AddressTransformer class that determines how a single address is returned   */
    /* The default will populate a Laravel model given in the modelTransformerOptions below. */
    'modelTransformer' => \Squigg\IdealPostcodes\Transformers\ModelTransformer::class,

    /* These are options for the default ModelTransformer that comes with the package */
    'modelTransformerOptions' =>
        [
            /* Choose a Laravel model for the address data to be populated into. A new model of the class specified
            below will be instantiated and filled with the address data. */
            'model' => \App\Address::class,

            /* You may not want the fields in your Model to be fillable in general use. In this case set forceFill to true
            to allow the attributes to be filled regardless of your $fillable setting in the model.
            If you get a MassAssignmentException, add the required fields to your $fillable array or set this to true */
            'forceFill'  => false,

        ],

    /* An array of strings indicating fields to include from the API. Any fields not listed here will be excluded from
    the results, reducing your bandwidth requirement and avoiding excess attributes on your Model. Leave this empty
    or set to null to get all available fields. */

    'fields' => [],

    /* Example fields
    'fields'    => [
        'sub_building_name',
        'building_name',
        'building_number',
        'thoroughfare',
        'post_town',
        'postcode',
        'premise',
        'udprn',
        'line_1',
        'line_2',
        'line_3',
        'country',
    ],
    */
];
