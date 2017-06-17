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
    public static function add(string $name,array $command) : void
    {
        static::$commands[$name] = $command;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name) : bool
    {
        return isset(static::$commands[$name]);
    }

    /**
     * @param string $name
     * @return array
     */
    public static function get(string $name) : array
    {
        return static::$commands[$name];
    }


    /**
     * @param string $name
     * @return mixed
     */
    public static function getMap(string $name){
        return static::$commands[$name]['map'];
    }

    /**
     * @return array
     */
    public static function getCommands(): array
    {
        return self::$commands;
    }

    /**
     * @param array $commands
     */
    public static function setCommands(array $commands) : void
    {
        self::$commands = $commands;
    }
}