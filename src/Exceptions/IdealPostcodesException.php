<?php namespace Squigg\IdealPostcodes\Exceptions;

use GuzzleHttp\Exception\ClientException;

/**
 * Created by PhpStorm.
 * User: Squigg
 * Date: 22/02/2015
 * Time: 23:33
 */
class IdealPostcodesException extends \Exception
{

    private $clientException;

    /**
     * Create a new exception with the given message and code
     * @param \Exception|ClientException $exception
     */
    function __construct(ClientException $exception)
    {
        if ($exception->getCode() >= 500) {
            $this->code = $exception->getCode();
            $this->message = $exception->getMessage();
            $this->clientException = $exception;
        } else {
            $response = json_decode($exception->getResponse()->getBody(), true);
            $this->code = $response['code'];
            $this->message = $response['message'];
        }

    }

    /**
     * @return ClientException
     */
    public function getClientException()
    {
        return $this->clientException;
    }

}
