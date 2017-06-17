<?php


namespace Cable\Annotation;

use Cable\Annotation\Mapping\CommandMapping;
use Cable\Annotation\Mapping\ExecutedBag;
use Cable\Annotation\Mapping\MappedProperty;
use Cable\Annotation\Parser\Exception\ParserException;
use Psr\Container\ContainerInterface;

class Annotation
{

    /**
     * @var Annotation
     */
    private static $instance;

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var array
     */
    private $parsed;


    /**
     * @return Annotation
     */
    public static function getInstance(): Annotation
    {

        return self::$instance;
    }

    /**
     * @param Annotation $instance
     */
    public static function setInstance(Annotation $instance)
    {
        self::$instance = $instance;
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container) : void
    {
        self::$container = $container;
    }


    /**
     * Annotation constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;

        self::$instance = $this;
    }


    /**
     * @param mixed $command
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


        $mapper = new CommandMapping($this->parser);

        $mapper->map($command);


        CommandBag::add($mapper->name, [
            'object' => $command,
            'map' => $mapper
        ]);

        return $this;
    }


    /**
     * @param \ReflectionMethod $method
     *
     * @throws \ReflectionException
     * @throws ParserException
     * @return array
     */
    public function executeMethod(\ReflectionMethod $method): array
    {
        $executed = $this->parse($method->getDocComment())
            ->execute();

        return $executed;
    }

    /**
     * @param \ReflectionProperty $property
     * @return array
     */
    public function executeProperty(\ReflectionProperty $property): array
    {
        $executed = $this->parse($property->getDocComment())
            ->execute();

        return $executed;
    }

    /**
     * @param $class
     *
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


        $this->executeProperties($classReflection->getProperties(), $bag);
        $this->executeMethods($classReflection->getMethods(), $bag);

        foreach ($classExecuted as $item => $value) {
            $bag->set($item, $value);
        }

        return $bag;
    }

    /**
     * @param \ReflectionMethod[] $methods
     * @param ExecutedBag $bag
     * @throws ParserException
     * @throws \ReflectionException
     */
    private function executeMethods(array $methods, ExecutedBag $bag): void
    {
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $executed = $this->executeMethod($method);

                foreach ($executed as $item => $values) {

                    $bag->set($item, $values);
                }
            }
        }
    }

    /**
     * @param \ReflectionProperty[] $properties
     * @param ExecutedBag $bag
     */
    private function executeProperties(array $properties, ExecutedBag $bag): void
    {

        if (!empty($properties)) {
            foreach ($properties as $property) {
                $executed = $this->executeProperty($property);

                foreach ($executed as $item => $values) {
                    $bag->set($item, $values);
                }
            }
        }

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

                $this->checkRequiredParams($map->required, $selectedCommand);

                foreach ($selectedCommand as $key => $parameter) {
                    $property = $this->findPropertyFromMap($map, $key);

                    $this->setProperty($object, $property->name, $parameter);
                }

                $prepared[$commandName][] = $object;
            }
        }

        return $prepared;
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
     * @param $object
     * @param string $name
     * @param $parameter
     */
    private function setProperty($object, string $name, $parameter): void
    {
        if (method_exists(
            $object,
            $method = 'set' . mb_convert_case($name, MB_CASE_TITLE)
        )) {
            $object->$method($parameter);
        } else {
            $object->{$name} = $parameter;
        }

    }

    /**
     * @param CommandMapping $mappedObject
     * @param int $key
     * @throws RequiredArgumentException
     * @return MappedProperty|bool
     */
    private function findPropertyFromMap(CommandMapping $mappedObject, $key)
    {

        foreach ($mappedObject->properties as $property) {
            if ($property->name === $key) {
                return $property;
            }
        }


        throw new RequiredArgumentException(
            sprintf(
                '%s parameter not found',
                $key
            )
        );


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
