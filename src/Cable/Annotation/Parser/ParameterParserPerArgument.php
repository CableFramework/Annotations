<?php

namespace Cable\Annotation\Parser;


use Cable\Annotation\Annotation;
use Cable\Annotation\CommandBag;
use Cable\Annotation\RequiredArgumentException;

class ParameterParserPerArgument implements ParserInterface
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
    public function __construct(int $key, string $parameter, string $explode = '=')
    {
        $this->key = $key;
        $this->parameter = $parameter;
        $this->explode = $explode;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return ParameterParserPerArgument
     */
    public function setCommand(string $command): ParameterParserPerArgument
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
            [$name, $value] = explode($this->explode, $this->parameter, 2);
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
     * @return Parameter
     */
    public function parse(): Parameter
    {
        [$name, $value] = $this->getNameAndValue();


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
     * @return mixed
     */
    private function getAnnotationParameter(string $value)
    {
        $annotation = Annotation::getInstance();


        return $annotation->directParse($value)
            ->execute()[$this->command][0];
    }

    /**
     * @param array $matches
     * @return mixed
     */
    private function getArrayParameter(array $matches)
    {
        if ($matches['function'] !== '') {
            return $this->getContainerValue($matches);
        }

        $parameters = new ParameterParser($matches[0], $matches[2], ':');


        return $parameters->parse();
    }

    /**
     * @param array $matches
     */
    private function getContainerValue(array $matches)
    {
        $alias = $matches['function'];


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
            return substr($value, 1, strlen($value) - 2) ?? '';
        }

        return $value;
    }
}