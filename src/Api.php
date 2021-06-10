<?php

namespace Laminas\ApiTools\Documentation;

use ArrayIterator;
use IteratorAggregate;

//use Laminas\ApiTools\ContentNegotiation\ViewModel;

class Api implements IteratorAggregate
{
    /** @var string */
    protected $name;

    /** @var int|string */
    protected $version = 1;

    /** @var array */
    protected $authorization;

    /** @var Service[] */
    protected $services = [];

    /**
     * @param string $name
     * @return void
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
     * @return void
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
     * @return void
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

    public function addService(Service $service): void
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
        $array = [
            'name'     => $this->name,
            'version'  => $this->version,
            'services' => [],
        ];
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
