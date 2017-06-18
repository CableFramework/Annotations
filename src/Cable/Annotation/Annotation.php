<?php


namespace Cable\Annotation;

use Cable\Annotation\Mapping\CommandMapping;
use Cable\Annotation\Mapping\MappedProperty;
use Cable\Annotation\Parser\AnnotationParser;
use Cable\Annotation\Parser\Exception\ParserException;
use Cable\Annotation\Parser\ParserInterface;
use Psr\Container\ContainerInterface;

class Annotation
{



    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var array
     */
    private $parsed;


    /**
     * @return ContainerInterface
     */
    public static function getContainer() : ContainerInterface
    {
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }


    /**
     * Annotation constructor.
     * @param DocumentedParserInterface $parser
     */
    public function __construct(DocumentedParserInterface $parser)
    {
        $this->parser = $parser;


        AnnotationParser::setAnnotation($this);
    }


    /**
     * @param string|object $command
     *
     * @throws ParserException
     * @throws \ReflectionException
     *
     * @return $this
     */
    public function addCommand($command)
    {
        if (is_string($command)) {
            $command = new $command;
        }
        $map = (new CommandMapping($this->parser))->map($command);

        CommandBag::add($map->name, [
            'object' => $command,
            'map' => $map
        ]);

        return $this;
    }


    /**
     * @param \ReflectionMethod $method
     *
     * @throws CommandNotFoundException
     * @throws RequiredArgumentException
     * @throws \ReflectionException
     * @throws ParserException
     * @return array
     */
    public function executeMethod(\ReflectionMethod $method): array
    {

        return $this->parse($method->getDocComment())
            ->execute();
    }

    /**
     * @param \ReflectionProperty $property
     * @throws CommandNotFoundException
     * @throws RequiredArgumentException
     * @throws \ReflectionException
     * @throws ParserException
     * @return array
     */
    public function executeProperty(\ReflectionProperty $property): array
    {
        return $this->parse($property->getDocComment())
            ->execute();
    }

    /**
     * @param $class
     *
     * @throws CommandNotFoundException
     * @throws RequiredArgumentException
     * @throws \ReflectionException
     * @throws ParserException
     * @return ExecutedBag
     */
    public function executeClass($class): ExecutedBag
    {
        $classReflection = new \ReflectionClass($class);
        $bag = new ExecutedBag();

        $classExecuted = $this->parse($classReflection->getDocComment())
            ->execute();


        $bag->set(
            'properties',
            $this->executeProperties($classReflection->getProperties())
        );

        $methods = $classReflection->getMethods();


        $bag->set('methods',
            $this->executeMethods($methods)
        );



        foreach ($classExecuted as $item => $value) {
            $bag->set($item, $value);
        }

        return $bag;
    }

    /**
     * @param \ReflectionMethod[] $methods
     * @throws ParserException
     * @throws CommandNotFoundException
     * @throws RequiredArgumentException
     * @throws \ReflectionException
     * @return ExecutedBag
     */
    private function executeMethods(array $methods): ExecutedBag
    {
        $bag = new ExecutedBag();


        if (!empty($methods)) {

            foreach ($methods as $method) {


                $executed = $this->executeMethod($method);

                foreach ($executed as $item => $values) {

                    $bag->set($item, array(
                        'from' => $method->getDeclaringClass()->getName().'::'.$method->getName(),
                        'value' => $values
                    ));
                }
            }
        }


        return $bag;
    }

    /**
     *
     * @param \ReflectionProperty[] $properties
     * @throws ParserException
     * @throws CommandNotFoundException
     * @throws RequiredArgumentException
     * @throws \ReflectionException
     * @return ExecutedBag
     */
    private function executeProperties(array $properties): ExecutedBag
    {
        $bag = new ExecutedBag();

        if (!empty($properties)) {
            foreach ($properties as $property) {
                $executed = $this->executeProperty($property);

                foreach ($executed as $item => $values) {
                    $bag->set($item, array(
                        'from' =>
                            $property->getDeclaringClass()
                                ->getName().'->'.$property->getName(),
                        'value' => $values
                    ));
                }
            }
        }


        return $bag;
    }

    /**
     * @param string $comment
     *
     * @throws ParserException
     * @return Annotation
     */
    public function parse(string $comment): Annotation
    {
        $this->parser->setDocument($comment);

        $this->parsed = $this->parser->parse();


        return $this;
    }

    /**
     * @param string $str
     * @return $this
     */
    public function directParse(string $str)
    {
        $this->parsed = $this->parser->directParse($str);

        return $this;
    }


    /**
     *
     * @throws RequiredArgumentException
     * @throws CommandNotFoundException
     * @return array
     */
    public function execute(): array
    {
        $prepared = [];


        foreach ($this->parsed as $command => $commands) {

            foreach ($commands as $selectedCommand) {

                $commandName = $command;

                $com = $this->findCommand($commandName);

                // clone the given command
                $object = clone $com['object'];
                $map = $com['map'];


                $selectedCommand = $this->setDefaultParameters($selectedCommand, $map);

                $this->checkRequiredParams($map->required, $selectedCommand);


                $prepared[$commandName][] = $this->fill($object, $map, $selectedCommand);
            }
        }

        return $prepared;
    }

    /**
     * @param array $selectedCommand
     * @param CommandMapping $mapping
     * @return array
     */
    private function setDefaultParameters(
        array $selectedCommand,
        CommandMapping $mapping
    ): array
    {
        return array_merge($mapping->default, $selectedCommand);
    }

    /**
     * @param object $object
     * @param CommandMapping $map
     * @param array $selectedCommand
     * @return object
     */
    private function fill($object, CommandMapping $map, array $selectedCommand)
    {
        return (new Filler($map, $object, $selectedCommand))->fill();
    }

    /**
     * @param string $command
     * @throws CommandNotFoundException
     * @return array
     */
    private function findCommand(string $command): array
    {
        if (!CommandBag::has($command)) {
            throw new CommandNotFoundException(
                sprintf(
                    '%s command not found',
                    $command
                )
            );
        }


        return CommandBag::get($command);
    }

    /**
     * @param array $required
     * @param array $commands
     * @throws RequiredArgumentException
     */
    private function checkRequiredParams(array $required, array $commands): void
    {

        foreach ($required as $item) {
            if (!isset($commands[$item])) {
                throw new RequiredArgumentException(
                    sprintf(
                        '%s argument is required',
                        $item
                    )
                );
            }
        }
    }
}
