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

    /* Whether to wrap the results in a Laravel Collection object */
    'collection' => true,

    /* You can choose for the address data to be populated into a Laravel Model. A new model of the class specified
        below will be instantiated and filled with the address data. Set to null to turn off this behaviour */
    // 'model'      => '\Path\To\Laravel\Model\For\Address',
    'model'      => null,

    /* You may not want the fields in your Model to be fillable in general use. In this case set forceFill to true
    to allow the attributes to be filled regardless of your $fillable setting in the model.
    If you get a MassAssignmentException, add the required fields to your $fillable array or set this to true */
    'forceFill'  => false,

    /* An array of strings indicating fields to include from the API. Any fields not listed here will be excluded from
    the results, reducing your bandwidth requirement.
    You can transform a field from the API to a different attribute name in your Model by using an associative
    array instead of a string e.g. ["api-field-name" => "model field name"]
    Leave this blank to get all fields from the API */

    'fields' => [],

    /* Example fields
    'fields'    => [
        'sub_building_name',
        'building_name',
        'building_number',
        ['thoroughfare' => 'main_street'],
        // This maps a particular API field to a custom named field in your model/array
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
