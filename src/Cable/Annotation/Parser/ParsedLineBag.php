<?php
/**
 * Created by PhpStorm.
 * User: vahit
 * Date: 14.06.2017
 * Time: 14:25
 */

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
    public function __construct(string $command = '',ParameterBag $parameters = null)
    {
        $this->command = $command;
        $this->parameters = $parameters;
    }
}
