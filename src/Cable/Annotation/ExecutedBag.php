<?php

namespace Cable\Annotation;

/**
 * Class ExecutedBag
 * @package Cable\Annotation
 */
class ExecutedBag implements \Iterator
{
    /**
     * @var array
     */
    private $objects;

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
        $array = $this->objects;

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

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        current($this->objects);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->objects);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        key($this->objects);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        $key = key($this->objects);
        return ($key !== NULL && $key !== FALSE);

    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
       reset($this->objects);
    }
}
