<?php

namespace Cable\Annotation\Parser;

/**
 * Class ParameterParser
 * @package Cable\Annotation\Parser
 */
class ParameterGroupParser implements ParserInterface
{

    /**
     * @var string
     */
    private $parameters;

    /**
     * @var string
     */
    private $parser;

    /**
     * @var string
     */
    private $command;

    /**
     * ParameterParser constructor.
     * @param string $command
     * @param string $parameters
     * @param string $parser
     */
    public function __construct(string $command, string  $parameters,string  $parser = '=')
    {
        $this->command = $command;
        $this->parameters = $parameters;
        $this->parser = $parser;
    }

    /**
     * @return mixed
     */
    public function parse()
    {
        $parameters = explode(',', $this->parameters);

        $bag = new ParameterBag();


        foreach ($parameters as $key =>  $parameter){
            $bag->add(
                (new ParameterParser($key, $parameter, $this->parser))
                    ->setCommand($this->command)
                    ->parse()
            );
        }

        return $bag;
    }
}
