<?php

namespace Structure\Interface;

/**
 * Interface implemented by all comparable objects.
 */
interface ComparableInterface
{
    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return integer A number less than zero if this object is less than the given object,
     *     zero if this object equals the given object, and a number greater than zero
     *     if this object is greater than the given object.
     */
    public abstract function compare(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return boolean True if this object is equal to the given object.
     */
    public abstract function eq(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return boolean True if this object is not equal to the given object.
     */
    public abstract function ne(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return boolean True if this object is less than the given object.
     */
    public abstract function lt(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return boolean True if this object is less than or equal to the given object.
     */
    public abstract function le(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return boolean True if this object is greater than the given object.
     */
    public abstract function gt(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object The given object.
     * @return boolean True if this object is greater than or equal to the given object.
     */
    public abstract function ge(ComparableInterface $object);
}

/**
 * Returns true if the given items compare equal.
 *
 * @param mixed $left An item.
 * @param mixed $right An item.
 * @return boolean True if the given items are equal.
 */
function eq($left, $right)
{
    if (gettype($left) == 'object' && gettype($right) == 'object') {
        return $left->eq($right);
    } else {
        return $left == $right;
    }
}

/**
 * Returns true if the given items compare not equal.
 *
 * @param mixed $left An item.
 * @param mixed $right An item.
 * @return boolean True if the given items are not equal.
 */
function ne($left, $right)
{
    if (gettype($left) == 'object' && gettype($right) == 'object') {
        return $left->ne($right);
    } else {
        return $left != $right;
    }
}

/**
 * Returns true if the left item is greater than the right item.
 *
 * @param mixed $left An item.
 * @param mixed $right An item.
 * @return boolean True if the given items are equal.
 */
function gt($left, $right)
{
    if (gettype($left) == 'object' && gettype($right) == 'object') {
        return $left->gt($right);
    } else {
        return $left > $right;
    }
}

/**
 * Returns true if the left item is greater than or equal to the right item.
 *
 * @param mixed $left An item.
 * @param mixed $right An item.
 * @return boolean True if the given items are equal.
 */
function ge($left, $right)
{
    if (gettype($left) == 'object' && gettype($right) == 'object') {
        return $left->ge($right);
    } else {
        return $left >= $right;
    }
}

/**
 * Returns true if the left item is less than the right item.
 *
 * @param mixed $left An item.
 * @param mixed $right An item.
 * @return boolean True if the given items are equal.
 */
function lt($left, $right)
{
    if (gettype($left) == 'object' && gettype($right) == 'object') {
        return $left->lt($right);
    } else {
        return $left < $right;
    }
}

/**
 * Returns true if the left item is less than or equal to the right item.
 *
 * @param mixed $left An item.
 * @param mixed $right An item.
 * @return boolean True if the given items are equal.
 */
function le($left, $right)
{
    if (gettype($left) == 'object' && gettype($right) == 'object') {
        return $left->le($right);
    } else {
        return $left <= $right;
    }
}
