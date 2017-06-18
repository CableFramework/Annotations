<?php

namespace Cable\Annotation\Phpdoc;

use Cable\Annotation\SetterInterface;

/**
 * Class Param
 * @package Cable\Annotation\Phpdoc
 * @Name("Param")
 *
 * @Setter("setAttributes")
 */
class Param implements SetterInterface
{


    /**
     * @param array $parameters
     * @return mixed
     */
    public function set(array $parameters)
    {
    }
}