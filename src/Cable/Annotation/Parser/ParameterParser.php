<?php

namespace Cable\Annotation\Parser;

use Cable\Annotation\CommandBag;
use Cable\Annotation\CommandNotFoundException;
use Cable\Annotation\Parser\Exception\ParserException;
use Cable\Annotation\RequiredArgumentException;

class ParameterParser implements ParserInterface
{

    /**
     * @var string
     */
    private $parameter;

    /**
     * @var string
     */
    private $explode;


    /**
     * @var int
     */
    private $key;

    /**
     * @var string
     */
    private $command;

    /**
     * ParameterParserPerArgument constructor.
     * @param int $key
     * @param string $parameter
     * @param string $explode
     */
    public function __construct($key,$parameter, $explode = '=')
    {
        $this->key = $key;
        $this->parameter = $parameter;
        $this->explode = $explode;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return ParameterParser
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }


    /**
     * @return array
     */
    private function getNameAndValue()
    {
        $name = 0;


        if (false !== strpos($this->parameter, $this->explode)) {
            list($name, $value) = explode($this->explode, $this->parameter, 2);
        } else {


            if (CommandBag::has($this->command)) {
                $name = CommandBag::getMap($this->command)
                    ->properties[$this->key]
                    ->name;
            }

            $value = trim($this->parameter);
        }


        return
            [
                is_string($name) ? trim($name) : $name,
                $this->getCleanedValue($value)
            ];
    }

    /**
     * @throws CommandNotFoundException
     * @throws RequiredArgumentException
     * @throws ParserException
     * @return Parameter
     */
    public function parse()
    {
        list($name, $value) = $this->getNameAndValue();


        if (preg_match("/(?'function'[\w]*){((?:[^{}]+|(?R))*)}/", $value, $matches)) {
            return new Parameter($name, $this->getArrayParameter($matches));
        }

        if (preg_match("/@(?'function'[\w]*)[\s\n\r]*/", $value)) {
            return new Parameter($name, $this->getAnnotationParameter($value));
        }


        return new Parameter($name, $value);
    }

    /**
     * @param string $value
     *
     * @throws RequiredArgumentException
     * @throws CommandNotFoundException
     * @return mixed
     */
    private function getAnnotationParameter($value)
    {
        $annotation = AnnotationParser::getAnnotation();


        return $annotation->directParse($value)
            ->execute()[$this->command][0];
    }

    /**
     * @param array $matches
     * @throws ParserException
     * @return mixed
     */
    private function getArrayParameter(array $matches)
    {
        return (new ArrayParameterParser($matches, ':'))->parse();
    }


    /**
     * @param mixed $value
     * @return bool|string
     */
    private function getCleanedValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if (0 === strpos($value, '"') || 0 === strpos($value, "'")) {
            $str =  substr($value, 1, -1);

            return $str !== null ? $str : '';
        }

        return $value;
    }
}