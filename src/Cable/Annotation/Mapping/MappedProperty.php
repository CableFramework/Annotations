<?php

namespace Cable\Annotation\Mapping;


class MappedProperty extends Mapped
{
    /**
     * @var string
     */
    public $name;


    /**
     * MappedProperty constructor.
     * @param string $name
     * @param array $mapped
     */
    public function __construct($name,array $mapped = [])
    {
        $this->name = $name;
        $this->mapped = $mapped;
    }
}
