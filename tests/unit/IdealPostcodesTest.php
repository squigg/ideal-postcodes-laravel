<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mockery\Mock;
use Squigg\IdealPostcodes\IdealPostcodes;
use Squigg\IdealPostcodes\Transformers\CollectionTransformer;
use Squigg\IdealPostcodes\Transformers\ModelTransformer;

class IdealPostcodesTest extends TestCase
{

    protected function getMockClientBase()
    {
        /** @var \Mockery\Mock $client */
        $client = Mockery::mock(\GuzzleHttp\Client::class);
        $client->shouldReceive('getConfig')->once()->with('query')->andReturn(['limit' => 50, 'api_key' => 'iddqd']);

        return $client;
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function getMockClientForPostcode($postcode)
    {
        return $this->getMockClientBase()->shouldReceive('request')->withArgs([
            'GET',
            'postcodes/' . $postcode,
            ['query' => ['limit' => 50, 'api_key' => 'iddqd']]
        ])->andReturn($this->getMockResponse($this->postcodeResponse()))->getMock();
    }

    protected function getMockClientForUdprn($udprn)
    {
        return $this->getMockClientBase()->shouldReceive('request')->withArgs([
            'GET',
            'udprn/' . $udprn,
            ['query' => ['limit' => 50, 'api_key' => 'iddqd']]
        ])->andReturn($this->getMockResponse($this->getUdprnResponse()))->getMock();
    }

    protected function getMockClientForSearch($query)
    {
        return $this->getMockClientBase()->shouldReceive('request')->withArgs([
            'GET',
            'addresses',
            ['query' => ['limit' => 50, 'api_key' => 'iddqd', 'q' => $query]]
        ])->andReturn($this->getMockResponse($this->getSearchResponse()))->getMock();
    }

    protected function getMockResponse($response)
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(\GuzzleHttp\Psr7\Response::class);
        return $mock->shouldReceive('getBody')->once()->andReturn($response)->getMock();
    }

    protected function getService($client, $config = [])
    {
        $defaultConfig = [
            'api_key'  => 'iddqd',
            'base_url' => 'https://baseurl/',
            'timeout'  => 5,
            'limit'    => 50,
            'fields'   => [],
        ];

        $config = array_merge($defaultConfig, $config);
        return new IdealPostcodes($client, $config);
    }

    protected function getSingleAddressFixtureAsArray()
    {
        $array = json_decode($this->getUdprnResponse(), true);
        return $array['result'];
    }

    /** @test */
    public function it_can_get_addresses_by_postcode()
    {
        $client = $this->getMockClientForPostcode('ABCDEFG');
        $service = $this->getService($client);

        $response = $service->getByPostcode('ABC DEFG');

        $this->assertCount(1, $response);
        $this->assertArraySubset(['postcode' => 'SW1A 2AA'], $response[0]);
        $this->assertArraySubset(['post_town' => 'LONDON'], $response[0]);

    }

    /** @test */
    public function it_can_get_address_by_udprn()
    {
        $client = $this->getMockClientForUdprn(12345);
        $service = $this->getService($client);

        $response = $service->getByUDPRN(12345);

        $this->assertArraySubset(['postcode' => 'ID1 1QD'], $response);
        $this->assertArraySubset(['post_town' => 'LONDON'], $response);

    }

    /** @test */
    public function it_can_get_addresses_by_search()
    {
        $client = $this->getMockClientForSearch('search');
        $service = $this->getService($client);

        $response = $service->getByAddress('search');

        $this->assertCount(2, $response);
        $this->assertArraySubset(['postcode' => 'SW1A 2AA'], $response[0]);
        $this->assertArraySubset(['post_town' => 'LONDON'], $response[0]);
        $this->assertArraySubset(['postcode' => 'WC1N 1LX'], $response[1]);
        $this->assertArraySubset(['post_town' => 'LONDON'], $response[1]);

    }

    /** @test */
    public function it_can_use_address_transformer()
    {
        $client = $this->getMockClientForUdprn(12345);
        $service = $this->getService($client);

        $service->setAddressTransformer(new ModelTransformer(App\Address::class));

        $response = $service->getByUDPRN(12345);

        $this->assertInstanceOf(Model::class, $response);
        $this->assertEquals('yes', $response->filled);
        $this->assertEquals($this->getSingleAddressFixtureAsArray(),$response->getAttributes());
    }

    /** @test */
    public function it_can_use_address_collection_transformer()
    {
        $client = $this->getMockClientForSearch('search%20me');
        $service = $this->getService($client);

        $service->setCollectionTransformer(new CollectionTransformer());

        $response = $service->getByAddress('search me');

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(2, $response);
    }

    /** @test */
    public function it_can_combine_collection_and_model_transformer()
    {
        $client = $this->getMockClientForSearch('search%20me');
        $service = $this->getService($client);

        $service->setAddressTransformer(new ModelTransformer(App\Address::class));
        $service->setCollectionTransformer(new CollectionTransformer());

        $response = $service->getByAddress('search me');

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(2, $response);
        $this->assertInstanceOf(Model::class, $response->first());
    }

    protected function postcodeResponse()
    {
        return file_get_contents(__DIR__ . '/../fixtures/postcode_response.json');
    }

    protected function getUdprnResponse()
    {
        return file_get_contents(__DIR__ . '/../fixtures/udprn_response.json');
    }

    protected function getSearchResponse()
    {
        return file_get_contents(__DIR__ . '/../fixtures/search_response.json');
    }

}
