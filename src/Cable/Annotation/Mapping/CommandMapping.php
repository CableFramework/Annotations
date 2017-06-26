<?php

namespace Cable\Annotation\Mapping;


use Cable\Annotation\DocumentedParserInterface;
use Cable\Annotation\Parser\Exception\ParserException;
use Cable\Annotation\Parser\ParserInterface;
use Cable\Annotation\SetterInterface;

/**
 * Class ClassMapping
 * @package Cable\Annotation\Mapping
 */
class CommandMapping implements MappingInterface
{


    /**
     * @var DocumentedParserInterface
     */
    private $parser;


    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $required = [];

    /**
     * @var array
     */
    public $default = [];


    /**
     * @var array
     */
    public $propertySetter;

    /**
     * @var string
     */
    public $classSetter;

    /**
     * @var array
     */
    public $properties = [];




    /**
     * Mapping constructor.
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param array $parsed
     * @param \ReflectionClass $class
     * @return string
     */
    private function getName(array $parsed, \ReflectionClass $class)
    {
        if (isset($parsed['Namespace'][0][0])) {
            $namespace = $parsed['Namespace'][0][0];

            if ('\\' !== substr($namespace, -1)) {
                $namespace .= '\\';
            }
        } else {
            $namespace = '';
        }


        $name = isset($parsed['Name'][0][0]) ? $parsed['Name'][0][0] :  $class->getName();

        return $namespace . $name;
    }

    /**
     * @param object $object
     * @throws ParserException
     * @throws \ReflectionException
     * @return mixed
     */
    public function map($object)
    {
        $class = new \ReflectionClass($object);
        $this->parser->setDocument($class->getDocComment());
        $parsed = $this->parser->parse();

        $this->name  = $this->getName($parsed, $class);
        $this->classSetter = $this->getSetter($parsed, $object);


        $this->prepareProperties($class->getProperties());


        // we don't need that anymore, let's save memory
        $this->parser = null;

        return $this;
    }

    /**
     * @param array $properties
     * @throws ParserException
     */
    private function prepareProperties(array $properties)
    {
        foreach ($properties as $property) {
            /**
             * @var \ReflectionProperty $property
             */

            $parsed = $this->parser
                ->setDocument($property->getDocComment())
                ->parse();


            if (!isset($parsed['Annotation'])) {
                continue;
            }

            $name = $property->getName();
            $this->prepareRequiredDefaultSetter($name, $parsed);

            $this->properties[] = new MappedProperty($property->getName(), $parsed);
        }
    }


    /**
     * @param string $name
     * @param array $parsed
     */
    private function prepareRequiredDefaultSetter($name, array $parsed)
    {
        // if properity is required
        if (isset($parsed['Required'])) {
            $this->required[] = $name;
        }


        if (isset($parsed['Default'])) {
            $this->default[$name] = $parsed['Default'][0][0];
        }

        // if there is no class setter we will use property setters
        if(false === $this->classSetter){
            if (!isset($parsed['Setter'])) {
                $parsed['Setter'][0][0] = 'set' . mb_convert_case($name, MB_CASE_TITLE);
            }

            $this->propertySetter[$name] = $parsed['Setter'][0][0];
        }
    }

    /**
     * @param array $parsed
     * @param $object
     * @return string|bool
     */
    private function getSetter(array $parsed, $object)
    {
        if (isset($parsed['Setter'])) {
            return $parsed['Setter'][0][0];
        }

        if ($object instanceof SetterInterface) {
            return 'set';
        }


        return false;
    }
}
