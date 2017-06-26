<?php

namespace Cable\Annotation\Parser;


class Parameter
{

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var mixed
     */
    public $value;

    /**
     * Parameter constructor.
     * @param null $name
     * @param null $value
     */
    public function __construct($name = null, $value = null)
    {
        $this->name = trim($name);
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function toArray(){
        $value = $this->value instanceof ParameterBag ?
            $this->value->toArray() :
            $this->value;

        return [$this->name => $value];
    }
}
