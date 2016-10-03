<?php

abstract class TestCase extends Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            Squigg\IdealPostcodes\IdealPostcodesServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'IdealPostcodes' => Squigg\IdealPostcodes\Facades\IdealPostcodesFacade::class,
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->loadServiceProvider();
    }

    protected function loadServiceProvider()
    {
        $this->app->make('ideal-postcodes');
    }

    public function tearDown()
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
    }

}
