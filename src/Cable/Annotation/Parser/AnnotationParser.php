<?php

namespace Cable\Annotation\Parser;


use Cable\Annotation\Annotation;

/**
 * Class AnnotationParser
 * @package Cable\Annotation\Parser
 */
class AnnotationParser implements ParserInterface
{

    /**
     * @var string
     */
    private $commandString;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $string;

    /**
     * @var Annotation
     */
    private static $annotation;
    /**
     * AnnotationParser constructor.
     * @param string $commandString
     * @param string $command
     * @param string $string
     */
    public function __construct(string $commandString,string $command,string $string)
    {
        $this->setCommandString($commandString)
            ->setCommand($command)
            ->setString($string);
    }

    /**
     * @return Annotation
     */
    public static function getAnnotation(): Annotation
    {
        return self::$annotation;
    }

    /**
     * @param Annotation $annotation
     */
    public static function setAnnotation(Annotation $annotation)
    {
        self::$annotation = $annotation;
    }


    /**
     * @return string
     */
    public function getCommandString(): string
    {
        return $this->commandString;
    }

    /**
     * @param string $commandString
     * @return AnnotationParser
     */
    public function setCommandString(string $commandString): AnnotationParser
    {
        $this->commandString = $commandString;
        return $this;
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
     * @return AnnotationParser
     */
    public function setCommand(string $command): AnnotationParser
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @param string $string
     * @return AnnotationParser
     */
    public function setString(string $string): AnnotationParser
    {
        $this->string = $string;
        return $this;
    }

    /**
     * @return mixed
     */
    public function parse()
    {
        $parameterParser = new ParameterGroupParser($this->getCommand(), $this->getString());


        return $parameterParser->parse();
    }
}
