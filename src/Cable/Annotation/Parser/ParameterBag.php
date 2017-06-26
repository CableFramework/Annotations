<?php
namespace Cable\Annotation\Parser;


class ParameterBag
{

    /**
     * @var array
     */
    public $parameters;


    /**
     * ParameterBag constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param Parameter $parameter
     * @return ParameterBag
     */
    public function add(Parameter $parameter){
        $this->parameters[] = $parameter;

        return $this;
    }

    /**
     * @return string
     */
    public function toArray(){
        $parameters = [];

        foreach($this->parameters as $parameter){
            $parameters[] = $parameter->toArray();
        }

        return array_merge(...$parameters);
    }

}