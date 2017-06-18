<?php

namespace Cable\Annotation;


/**
 * Interface FillerInterface
 * @package Cable\Annotation
 */
interface FillerInterface
{
    /**
     * @return object
     */
    public function fill();
}