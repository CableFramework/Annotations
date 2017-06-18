<?php

namespace Cable\Annotation;


use Cable\Annotation\Mapping\CommandMapping;

/**
 * Class Filler
 * @package Cable\Annotation
 */
class Filler implements FillerInterface
{

    /**
     * @var CommandMapping
     */
    private $mapping;

    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $command;

    /**
     * Filler constructor.
     * @param CommandMapping $mapping
     * @param object $object
     * @param array $command
     */
    public function __construct(CommandMapping $mapping,$object, array $command)
    {
        $this->mapping = $mapping;
        $this->object = $object;
        $this->command = $command;
    }

    /**
     * @return object
     */
    public function fill()
    {
        if (false !== $this->mapping->classSetter) {
            $setter = $this->mapping->classSetter;

            $this->object->{$setter}($this->command);

            return $this->object;
        }


        foreach ($this->mapping->properties as $property) {

            $name = $property->name;
            $setter = $this->mapping->propertySetter[$name];


            if (method_exists($this->object, $setter)) {
                $this->object->{$setter}($this->command[$name]);
            } else {
                $this->object->{$name} = $this->command[$name];
            }
        }


        return $this->object;
    }
}