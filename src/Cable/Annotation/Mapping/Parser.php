<?php

namespace Cable\Annotation\Mapping;

use Cable\Annotation\Parser as Base;

/**
 * Class Parser
 * @package Cable\Annotation\Mapping
 */
abstract class Parser
{

    /**
     * @var Base
     */
    protected $parser;

    /**
     * @return mixed
     */
    public function getParser() : Base
    {
        return $this->parser;
    }

    /**
     * @param mixed $parser
     * @return Parser
     */
    public function setParser(Base $parser) : Parser
    {
        $this->parser = $parser;
        return $this;
    }
}