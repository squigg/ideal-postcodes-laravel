<?php namespace Squigg\IdealPostcodes\Facades;

use Illuminate\Support\Facades\Facade;

class IdealPostcodesFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ideal-postcodes-client';
    }

}
