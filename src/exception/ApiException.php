<?php
namespace velosipedist\SculptorClient\exception;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\ResponseInterface;

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
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param string $message
     * @param int $code
     * @param Client $client
     * @param Request $request
     * @param ResponseInterface $response
     */
    function __construct($message, $code, Client $client, Request $request, ResponseInterface $response = null)
    {
        $this->client = $client;
        $this->request = $request;
        parent::__construct($message, $code);
        $this->response = $response;
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

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
