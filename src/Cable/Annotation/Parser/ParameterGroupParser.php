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
     * @var string
     */
    private $exploder;

    /**
     * ParameterParser constructor.
     * @param string $command
     * @param string $parameters
     * @param string $parser
     * @param string $exploder
     */
    public function __construct($command,$parameters,$parser = '=', $exploder = ',')
    {
        $this->command = $command;
        $this->parameters = $parameters;
        $this->parser = $parser;
        $this->exploder = $exploder;
    }

    /**
     * @return mixed
     */
    public function parse()
    {
        if ($this->exploder === ','){
            $parameters = preg_replace_callback('#{(.*?)}#', array($this, 'callback'), $this->parameters);
            $parameters = explode(',', $parameters);
        }else{
            $parameters = explode($this->exploder, $this->parameters);
        }


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

    public function callback(array  $matched){
        return str_replace(',', '|||', $matched[0]);
    }
}
