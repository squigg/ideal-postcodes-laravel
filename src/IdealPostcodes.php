<?php namespace Squigg\IdealPostcodes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Squigg\IdealPostcodes\Exceptions\IdealPostcodesException;
use Squigg\IdealPostcodes\Transformers\Interfaces\AddressCollectionTransformer;
use Squigg\IdealPostcodes\Transformers\Interfaces\AddressTransformer;

class IdealPostcodes
{

    /**
     * The Guzzle client
     * @var Client
     */
    protected $client;

    /**
     * Determine whether to return the results as a collection or an array
     * @var bool
     */
    protected $collection;

    /**
     * The classname of a Laravel Model to populate the address data into
     * @var string
     */
    protected $model;

    /**
     * An array of fields to include in the output (defaults to empty = all)
     * @var array
     */
    protected $fields = [];

    /**
     * Total number of results
     * @var int
     */
    protected $total;

    /**
     * Current page of results
     * @var int
     */
    protected $page;

    /**
     * The code returned from the Ideal Postcodes API
     * @var
     */
    protected $code;

    /**
     * The message returned from the Ideal Postcodes API
     * @var string
     */
    protected $message;

    /**
     * Full response data from the API
     * @var array
     */
    protected $responseData;

    /**
     * The current limit for API responses
     * @var int
     */
    protected $limit;

    /**
     * The API key
     * @var string
     */
    protected $apiKey;

    /**
     * Copy of the configuration entries
     * @var string
     */
    protected $config;

    /**
     * Limit for the next request only
     * @var int
     */
    protected $nextRequestLimit;

    /**
     * A transformer class for a collection of addresses
     * @var AddressCollectionTransformer
     */
    protected $collectionTransformer;
    /**
     * A transformer class for a single address
     * @var AddressTransformer
     */
    protected $addressTransformer;

    /**
     * Initialise a new instance of the class
     * @param Client $client
     * @param array $config
     */
    function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->config = $config;
        $this->limit = array_get($config, 'limit', 50);
        $this->apiKey = array_get($config, 'api_key', 50);
    }
    /**
     * Get an array of addresses by Postcode
     * @param $postcode
     * @return array|Collection
     */
    public function getByPostcode($postcode)
    {

        $postcode = $this->preparePostcode($postcode);
        $postcode = $this->encode($postcode);
        $endPoint = "postcodes/$postcode";

        return $this->getAddressesByRequest([$this, 'handlePostcodeResponse'], $endPoint);
    }

    /**
     * Get an address by a Unique Delivery Point Reference Number
     * @param $udprn
     * @return array|Model
     */
    public function getByUDPRN($udprn)
    {
        $udprn = $this->encode($udprn);
        $endPoint = "udprn/$udprn";

        return $this->getAddressesByRequest([$this, 'handleUdprnResponse'], $endPoint);
    }

    /**
     * Get an address by a Unique Delivery Point Reference Number
     * @param $address
     * @return array|Collection
     */
    public function getByAddress($address)
    {
        $address = $this->encode($address);
            $endPoint = "addresses";

        return $this->getAddressesByRequest([$this, 'handleAddressQueryResponse'], $endPoint, ['q' => $address]);
    }

    /**
     * Get addresses from the service by a URL
     * @param callable $handler
     * @param string $endPoint
     * @param array $query
     * @return array|Collection
     * @throws IdealPostcodesException
     */
    protected function getAddressesByRequest($handler, $endPoint, $query = [])
    {
        $this->clearRequestData();
        $query = $this->mergeAdditionalParams($query);
        $query = $this->mergeTemporaryLimitParam($query);

        try {
            $response = $this->client->request('GET', $endPoint, ['query' => $query]);
        } catch (ClientException $e) {
            $this->throwException($e);
        }

        $json = json_decode($response->getBody(), true);
        $this->responseData = $json;
        $this->setRequestData();

        return $handler($json);
    }

    /**
     * Merge additional query params into Guzzle query
     * @param $query
     * @return array
     */
    protected function mergeAdditionalParams($query)
    {
        return array_merge($this->client->getConfig('query'), $query);
    }

    /**
     * Merge additional query params into Guzzle query
     * @param $query
     * @return array
     */
    protected function mergeTemporaryLimitParam($query)
    {
        if ($this->nextRequestLimit) {
            $query = $this->mergeAdditionalParams(['limit' => $this->nextRequestLimit]);
            $this->nextRequestLimit = null;
        }

        return $query;
    }

    /**
     * Create a collection from an array of addresses
     * @param array $addresses
     * @return Collection
     */
    protected function transformAddressCollection($addresses)
    {
        $addresses = array_map([$this, 'transformAddress'], $addresses);

        if ($this->collectionTransformer) {
            return $this->collectionTransformer->transform($addresses);
        }

        return $addresses;
    }

    /**
     * Format an address as per the configuration
     * @param $address
     * @return mixed
     */
    protected function transformAddress($address)
    {
        if ($this->addressTransformer) {
            return $this->addressTransformer->transform($address);
        }
        return $address;
    }

    /**
     * Format response for a postcode lookup request
     * @param $data
     * @return mixed
     */
    protected function handlePostcodeResponse($data)
    {
        $addresses = $data['result'];
        return $this->transformAddressCollection($addresses);
    }

    /**
     * Format response for an address UDPRN query
     * @param $data
     * @return mixed
     */
    protected function handleUdprnResponse($data)
    {
        $address = $data['result'];
        return $this->transformAddress($address);
    }

    /**
     * Format the response to an Address query request
     * @param $data
     * @return mixed
     */
    protected function handleAddressQueryResponse($data)
    {
        $addresses = $data['result']['hits'];

        return $this->transformAddressCollection($addresses);
    }

    /**
     * Encode a string for use in a URL
     * @param $string
     * @return string
     */
    protected function encode($string)
    {
        return rawurlencode($string);
    }

    /**
     * Handle any exceptions from the Guzzle Client
     * @param ClientException $e
     * @throws IdealPostcodesException
     */
    protected function throwException(ClientException $e)
    {
        throw new IdealPostcodesException($e);
    }

    /**
     * Strips any whitespace and URL encodes a postcode ready for lookup
     * @param $postcode
     * @return mixed
     */
    protected function preparePostcode($postcode)
    {
        return $this->encode(trim(strtoupper(str_replace(' ', '', $postcode))));
    }

    /**
     * Set the maximum number of records to be returned (overrides config for next request only)
     * @param int $limit
     * @return $this
     */
    public function limitNextRequest($limit)
    {
        $this->nextRequestLimit = $limit;

        return $this;
    }

    /**
     * Clear the request outcome fields ready for the next request
     */
    protected function clearRequestData()
    {
        $this->total = null;
        $this->page = null;
        $this->code = null;
        $this->message = null;
    }

    /**
     * Set the fields for the outcome of the request based on a response
     */
    protected function setRequestData()
    {
        $this->total = (int)array_get($this->responseData, 'total', null);
        $this->page = (int)array_get($this->responseData, 'page', null);
        $this->limit = (int)array_get($this->responseData, 'limit', null);
        $this->code = (int)$this->responseData['code'];
        $this->message = $this->responseData['message'];
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }
    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->config['fields'];
    }


    /**
     * @param AddressCollectionTransformer $collectionTransformer
     */
    public function setCollectionTransformer(AddressCollectionTransformer $collectionTransformer)
    {
        $this->collectionTransformer = $collectionTransformer;
    }

    /**
     * @param AddressTransformer $addressTransformer
     */
    public function setAddressTransformer(AddressTransformer $addressTransformer)
    {
        $this->addressTransformer = $addressTransformer;
    }
}
