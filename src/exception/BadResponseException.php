<?php
namespace velosipedist\SculptorClient\exception;

use GuzzleHttp\Message\ResponseInterface;

class BadResponseException extends \RuntimeException
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct($message = "", ResponseInterface $response)
    {
        parent::__construct($message);
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

}
