<?php
/**
 * Created by PhpStorm.
 * User: vahit
 * Date: 14.06.2017
 * Time: 18:53
 */

namespace Cable\Annotation\Mapping;


interface MappingInterface
{

    /**
     * @param object $object
     * @return mixed
     */
    public function map($object);

}