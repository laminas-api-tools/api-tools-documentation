<?php

namespace Laminas\ApiTools\Documentation;

use ArrayIterator;
use IteratorAggregate;
use ReturnTypeWillChange;

class Field implements IteratorAggregate
{
    /** @var string */
    protected $name = '';

    /** @var string */
    protected $description = '';

    /** @var bool */
    protected $required = false;

    /** @var null|string */
    protected $type;

    /** @var string */
    protected $fieldType = '';

    /** @var string */
    protected $example = '';

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
     * @param bool $required
     * @return void
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @param string $fieldType
     * @return void
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /** @return string */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * @param string $example
     * @return $this
     */
    public function setExample($example)
    {
        $this->example = $example;

        return $this;
    }

    /**
     * Cast object to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'description' => $this->description,
            'required'    => $this->required,
            'type'        => $this->fieldType,
            'example'     => $this->example,
        ];
    }

    /**
     * Implement IteratorAggregate
     *
     * Passes the return value of toArray() to an ArrayIterator instance
     *
     * @return ArrayIterator
     */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }
}
