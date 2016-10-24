<?php

use Squigg\IdealPostcodes\IdealPostcodes;

class IdealPostcodesServiceProviderTest extends TestCase
{

    /** @test */
    public function it_can_be_instantiated_using_app_make() {

        $service = $this->app->make(IdealPostcodes::class);
        $this->assertInstanceOf(IdealPostcodes::class, $service);

    }

}
