<?php

namespace Structure\Interface;

use Structure\Exception;

/**
 * Interface implemented by all objects.
 */
interface ObjectInterface
{
    /**
     * Returns a unique identifier for this object.
     *
     * @return integer An identifier.
     */
    public abstract function getId();

    /**
     * Returns a hash code for this object.
     *
     * @return integer A hash code. 
     */
    public abstract function getHashCode();

    /**
     * Returns the class of this object.
     *
     * @return object ReflectionClass A ReflectionClass.
     */
    public abstract function getClass();
}

/**
 * Returns a hash code for the given item.
 *
 * @param mixed item An item.
 * @return integer A hash code.
 */
function hash($item)
{
    $type = gettype($item);
    if ($type == 'object') {
        return $item->getHashCode();
    } elseif ($type == 'NULL') {
        return 0;
    } else {
        throw new ArgumentError();
    }
}

/**
 * Returns a textual representation of the given item.
 *
 * @param mixed item An item.
 * @return string A string.
 */
function str($item)
{
    $type = gettype($item);
    if ($type == 'boolean') {
        return $item ? 'true' : 'false';
    } elseif ($type == 'object') {
        return $item->__toString();
    } elseif ($type == 'NULL') {
        return 'NULL';
    } else {
        return strval($item);
    }
}
