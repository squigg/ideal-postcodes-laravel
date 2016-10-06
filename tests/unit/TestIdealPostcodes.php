<?php

use Squigg\IdealPostcodes\IdealPostcodes;

class TestIdealPostcodes extends TestCase
{

    protected function getMockClient()
    {
        $client = Mockery::mock(\Guzzle\Http\Client::class);
        $client->shouldReceive('getConfig')->once()->with('query')->andReturn(['limit' => 50, 'api_key' => 'iddqd']);

        return $client;
    }

    protected function getService($config)
    {
        return new IdealPostcodes($this->getMockClient(), $config);
    }

}
