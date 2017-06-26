<?php

namespace Cable\Annotation;

/**
 * Class ExecutedBag
 * @package Cable\Annotation
 */
class ExecutedBag
{
    /**
     * @var array
     */
    public $objects;

    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param  string $key
     * @param mixed $default
     * @return mixed
     *
     */
    public function get($key, $default = null)
    {
        $array = &$this->objects;

        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('\\', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * @param $key
     * @param $value
     * @return array|mixed
     */
    public function set($key, $value)
    {
        $array = &$this->objects;

        $keys = explode('\\', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }


    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        return $this->get($name, isset($arguments[0]) ? $arguments[0] :  null);
    }
}