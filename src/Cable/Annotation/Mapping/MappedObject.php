<?php

namespace Cable\Annotation\Mapping;


class MappedObject extends Mapped
{


    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $properties = [];

    /**
     * @var array
     */
    public $methods = [];


    /**
     * MappedObject constructor.
     * @param string $name
     * @param array $properties
     * @param array $methods
     */
    public function __construct(
        string  $name,
        array $properties = [],
        array $methods = []
    )
    {
        $this->name = $name;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    /**
     * @param string|int $name
     * @return MappedProperty
     */
    public function getProperty($name) : MappedProperty {
        return $this->properties[$name];
    }

    /**
     * @param $name
     * @return MappedMethod
     */
    public function getMethod($name) : MappedMethod{
        return $this->methods[$name];
    }
}
