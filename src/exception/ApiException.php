<?php
namespace velosipedist\SculptorClient\exception;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;

class ApiException extends \RuntimeException
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Request
     */
    private $request;

    /**
     * @param string $message
     * @param int $code
     * @param Client $client
     * @param Request $request
     */
    function __construct($message, $code, $client, $request)
    {
        $this->client = $client;
        $this->request = $request;
        parent::__construct($message, $code);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
