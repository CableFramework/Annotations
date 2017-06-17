<?php

namespace Cable\Annotation;

/**
 * Interface SetterInterface
 * @package Cable\Annotation
 */
interface SetterInterface
{

    /**
     * @param array $parameters
     * @return mixed
     */
    public function set(array $parameters);
}
