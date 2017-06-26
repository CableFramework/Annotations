<?php
namespace Cable\Annotation\Parser;


class ParsedLineBag
{

    /**
     *  @var string
     */
    public $command;


    /**
     * @var array
     */
    public $parameters;

    /**
     * ParsedLineBag constructor.
     * @param string $command
     * @param ParameterBag $parameters
     */
    public function __construct($command = '',ParameterBag $parameters = null)
    {
        $this->command = $command;
        $this->parameters = $parameters;
    }
}
