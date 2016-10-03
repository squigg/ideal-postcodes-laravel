<?php namespace Squigg\IdealPostcodes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Squigg\IdealPostcodes\Exceptions\IdealPostcodesException;

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
     * @var mixed
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
     * @var
     */
    protected $message;

    /**
     * Full response data from the API
     * @var
     */
    protected $responseData;

    /**
     * The current limit for API responses
     * @var
     */
    protected $limit;

    /**
     * Initialise a new instance of the class
     * @param Client $client
     * @param array $config
     */
    function __construct(Client $client, $config)
    {
        $this->client = $client;

        $this->collection = array_get($config, 'collection', false);
        $this->model = array_get($config, 'model', null);
        $this->fields = array_get($config, 'fields', []);
        $this->limit = array_get($config, 'limit', 50);
    }

    /**
     * Format the addresses ready to be returned
     * @param $addresses
     * @return mixed
     */
    protected function formatAddresses($addresses)
    {
        // Filter only the fields we want
        $addresses = $this->filterAddressFields($addresses);

        // Check if this should be returned as a collection
        if ($this->collection) {
            return $this->createAddressCollection($addresses);
        }

        return array_map([$this, 'formatAddress'], $addresses);
    }

    /**
     * Create a collection from an array of addresses
     * @param array $addresses
     * @return Collection
     */
    protected function createAddressCollection($addresses)
    {
        $collection = new Collection();

        foreach ($addresses as $address) {
            $address = $this->formatAddress($address);
            $collection->push($address);
        }

        return $collection->keyBy('udprn');
    }

    /**
     * Format an address as per the configuration
     * @param $address
     * @return mixed
     */
    protected function formatAddress($address)
    {
        if ($this->model) {
            return $this->convertToModel($address);
        }

        return $address;
    }

    /**
     * Convert an array of address data to a Model
     * @param $address
     * @return mixed
     */
    protected function convertToModel($address)
    {
        return new $this->model($address);
    }

    /**
     * Get an array of addresses by Postcode
     * @param $postcode
     * @return Collection
     */
    public function getByPostcode($postcode)
    {

        $postcode = $this->preparePostcode($postcode);
        $postcode = $this->encode($postcode);
        $url = "postcodes/$postcode";

        return $this->getAddressesByRequest([$this, 'formatPostcodeResponse'], $url);
    }

    /**
     * Get an address by a Unique Delivery Point Reference Number
     * @param $udprn
     * @return array|Collection
     */
    public function getByUDPRN($udprn)
    {
        $udprn = $this->encode($udprn);
        $url = "addresses/$udprn";

        return $this->getAddressesByRequest([$this, 'formatUdprnResponse'], $url);
    }

    /**
     * Get an address by a Unique Delivery Point Reference Number
     * @param $address
     * @return array|Collection
     */
    public function getByAddress($address)
    {
        $address = $this->encode($address);
        $url = "addresses";

        return $this->getAddressesByRequest([$this, 'formatAddressQueryResponse'], $url, ['q' => $address]);
    }

    /**
     * Get addresses from the service by a URL
     * @param callable $formatter
     * @param string $url
     * @param array $query
     * @return array|Collection
     * @throws IdealPostcodesException
     */
    protected function getAddressesByRequest($formatter, $url, $query = [])
    {
        $this->clearRequestData();
        $query = array_merge($this->client->getConfig('query'), $query);
        try {
            $response = $this->client->request('GET', $url, ['query' => $query]);
        } catch (ClientException $e) {
            $this->throwException($e);
        }

        $json = json_decode($response->getBody(), true);
        $this->responseData = $json;
        $this->setRequestData();

        return $formatter($json);
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
     * Strips any whitespace and UEL encodes a postcode ready for lookup
     * @param $postcode
     * @return mixed
     */
    protected function preparePostcode($postcode)
    {
        return $this->encode(trim(strtoupper(str_replace(' ', '', $postcode))));
    }

    /**
     * Filter the fields within an array of addresses
     * @param $addresses
     * @return array
     */
    protected function filterAddressFields($addresses)
    {

        if (!empty($this->fields) && sizeof($addresses) > 0) {

            $addresses = array_map([$this, 'transformAddress'], $addresses);
        }

        return $addresses;
    }

    /**
     * Filter fields in an address based on the fields config setting
     * @param $address
     * @return array
     */
    protected function transformAddress($address)
    {
        $newAddress = [];

        foreach ($this->fields as $field) {

            if (is_array($field)) {
                if (isset($address[key($field)])) {
                    $newAddress[current($field)] = $address[key($field)];
                }
            } else {
                if (isset($address[$field])) {
                    $newAddress[$field] = $address[$field];
                }
            }
        }

        return $newAddress;
    }

    /**
     * Set the maximum number of records to be returned
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
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
     * Format response for a postcode lookup request
     * @param $data
     * @return mixed
     */
    protected function formatPostcodeResponse($data)
    {
        $addresses = $data['result'];

        return $this->formatAddresses($addresses);
    }

    /**
     * Format response for an address UDPRN query
     * @param $data
     * @return mixed
     */
    protected function formatUdprnResponse($data)
    {
        return $this->formatPostcodeResponse($data)->first();
    }

    /**
     * Format the response to an Address query request
     * @param $data
     * @return mixed
     */
    protected function formatAddressQueryResponse($data)
    {
        $addresses = $data['result']['hits'];

        return $this->formatAddresses($addresses);
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
}
