<?php

namespace Cable\Annotation;


class CommandBag
{

    /**
     * @var array
     */
    private static $commands;


    /**
     * @param string $name
     * @param array $command
     */
    public static function add($name,array $command)
    {
        static::$commands[$name] = $command;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return isset(static::$commands[$name]);
    }

    /**
     * @param string $name
     * @return array
     */
    public static function get($name)
    {
        return static::$commands[$name];
    }


    /**
     * @param string $name
     * @return mixed
     */
    public static function getMap($name){
        return static::$commands[$name]['map'];
    }

    /**
     * @return array
     */
    public static function getCommands()
    {
        return self::$commands;
    }

    /**
     * @param array $commands
     */
    public static function setCommands(array $commands)
    {
        self::$commands = $commands;
    }
}