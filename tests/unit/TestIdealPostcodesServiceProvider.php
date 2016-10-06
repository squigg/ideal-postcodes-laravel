<?php

use Squigg\IdealPostcodes\IdealPostcodes;

class TestIdealPostcodesServiceProvider extends TestCase
{

    public function testCanBeInstantiatedThroughAppMake() {

        $service = $this->app->make(IdealPostcodes::class);
        $this->assertInstanceOf(IdealPostcodes::class, $service);

    }

}
