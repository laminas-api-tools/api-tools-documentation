<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation;

use ArrayIterator;
use IteratorAggregate;

//use Laminas\ApiTools\ContentNegotiation\ViewModel;

class Api implements IteratorAggregate
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int|string
     */
    protected $version = 1;

    /**
     * @var array
     */
    protected $authorization;

    /**
     * @var Service[]
     */
    protected $services = array();

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int|string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int|string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param array $authorization
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @return array
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Cast object to array
     *
     * @return array
     */
    public function toArray()
    {
        $array = array(
            'name'     => $this->name,
            'version'  => $this->version,
            'services' => array()
        );
        foreach ($this->services as $i => $service) {
            $array['services'][$i] = $service->toArray();
        }
        return $array;
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
