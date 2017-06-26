<?php

namespace Cable\Annotation\Mapping;


interface MappingInterface
{

    /**
     * @param object $object
     * @return mixed
     */
    public function map($object);

}