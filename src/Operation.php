<?php

namespace Laminas\ApiTools\Documentation;

use ArrayIterator;
use IteratorAggregate;

class Operation implements IteratorAggregate
{
    /** @var string */
    protected $httpMethod;

    /** @var string */
    protected $description;

    /** @var string */
    protected $requestDescription = '';

    /**
     * Whether or not the operation requires authorization
     *
     * @var bool
     */
    protected $requiresAuthorization = false;

    /** @var string */
    protected $responseDescription = '';

    /**
     * Possible response status codes and messages
     *
     * @var array
     */
    protected $responseStatusCodes = [];

    /**
     * @param string $httpMethod
     * @return void
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $requestDescription
     * @return void
     */
    public function setRequestDescription($requestDescription)
    {
        $this->requestDescription = $requestDescription;
    }

    /**
     * @return string
     */
    public function getRequestDescription()
    {
        return $this->requestDescription;
    }

    /**
     * @param bool $flag
     * @return void
     */
    public function setRequiresAuthorization($flag)
    {
        $this->requiresAuthorization = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function requiresAuthorization()
    {
        return $this->requiresAuthorization;
    }

    /**
     * @param string $responseDescription
     * @return void
     */
    public function setResponseDescription($responseDescription)
    {
        $this->responseDescription = $responseDescription;
    }

    /**
     * @return string
     */
    public function getResponseDescription()
    {
        return $this->responseDescription;
    }

    /**
     * @param array $statusCodes
     * @return void
     */
    public function setResponseStatusCodes(array $statusCodes)
    {
        $this->responseStatusCodes = $statusCodes;
    }

    /**
     * @return array
     */
    public function getResponseStatusCodes()
    {
        return $this->responseStatusCodes;
    }

    /**
     * Cast object to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'description'            => $this->description,
            'request'                => $this->requestDescription,
            'requires_authorization' => $this->requiresAuthorization,
            'response'               => $this->responseDescription,
            'response_status_codes'  => $this->responseStatusCodes,
        ];
    }

    /**
     * Implement IteratorAggregate
     *
     * Passes the return value of toArray() to an ArrayIterator instance
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }
}
