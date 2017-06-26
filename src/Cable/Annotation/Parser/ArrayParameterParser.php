<?php

namespace Cable\Annotation\Parser;

use Cable\Annotation\Parser\Exception\ParserException;
use Cable\Container\Container;

use Cable\Annotation\Annotation;
use Cable\Annotation\ContainerNotFoundException;

class ArrayParameterParser implements ParserInterface
{

    /**
     * @var array
     */
    private $matches;

    /**
     * @var string
     */
    private $parser;


    /**
     * ArrayParameterParser constructor.
     * @param array $matches
     * @param string $parser
     */
    public function __construct(array $matches,$parser = ':')
    {
        $this->matches = $matches;
        $this->parser = $parser;
    }

    /**
     * @return mixed
     */
    public function parse()
    {
        $matches = $this->matches;

        $parameters = new ParameterGroupParser($matches[0], $matches[2], ':', '|||');
        $resolved = $parameters->parse();

        if ($matches['function'] !== '') {
            return $this->getContainerValue($matches['function'], $resolved->toArray());
        }

        return $resolved;
    }

    /**
     * @param string $alias
     * @param array $attributes
     * @throws ParserException
     * @return mixed
     */
    private function getContainerValue($alias, array $attributes = [])
    {

        if (null === Annotation::getContainer()) {
            throw new ContainerNotFoundException(
                'You did not provide any container'
            );
        }


        if (!Annotation::getContainer()->has($alias)) {
            throw new ContainerNotFoundException(
                sprintf(
                    '%s alias could not found in container',
                    $alias
                )
            );
        }


        if (Annotation::getContainer() instanceof Container) {
            return Annotation::getContainer()->fill($alias, $attributes);
        }

        return $this->fill(Annotation::getContainer()->get($alias), $attributes);
    }


    /**
     * @param mixed $resolved
     * @param array $attributes
     * @return mixed
     * @throws PropertyNotFoundException
     * @throws \ReflectionException
     */
    private function fill($resolved, array $attributes = []){

        if (empty($attributes)) {
            return $resolved;
        }

        $class = new \ReflectionClass($resolved);

        foreach ($attributes as $name => $attribute) {
            if (!$class->hasProperty($name)) {
                throw new PropertyNotFoundException(
                    sprintf(
                        '%s property could not found',
                        $name
                    )
                );
            }


            $property = $class->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($resolved, $attribute);
        }


        return $resolved;
    }

}
