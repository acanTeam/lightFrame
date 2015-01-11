<?php

namespace Structure/Abstract;

use Structure\Interface\Comparable;

/**
 * Abstract base class from which all comparable object classes are derived.
 */
abstract class AbstractComparable extends AbstractObject implements ComparableInterface
{
    /**
     * Constructs an AbstractComparable.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Returns true if this object is equal to the given object.
     * 
     * @param object ComparableInterface $object A comparable object.
     * @return boolean True if this object is equal to the given object.
     */
    public function eq(ComparableInterface $object)
    {
        return $this->compare($object) == 0;
    }

    /**
     * Returns true if this object is not equal to the given object.
     * 
     * @param object ComparableInterface $object A comparable object.
     * @return boolean True if this object is not equal to the given object.
     */
    public function ne(ComparableInterface $object)
    {
        return $this->compare($object) != 0;
    }
    
    /**
     * Returns true if this object is less than the given object.
     * 
     * @param object ComparableInterface $object A comparable object.
     * @return boolean True if this object is less than the given object.
     */
    public function lt(ComparableInterface $object)
    {
        return $this->compare($object) < 0;
    }
    
    /**
     * Returns true if this object is less than or equal to the given object.
     * 
     * @param object ComparableInterface $object A comparable object.
     * @return boolean True if this object is
     * less than or equal to the given object.
     */
    public function le(ComparableInterface $object)
    {
        return $this->compare($object) <= 0;
    }

    /**
     * Returns true if this object is greater than the given object.
     * 
     * @param object ComparableInterface $object A comparable object.
     * @return boolean True if this object is greater than the given object.
     */
    public function gt(ComparableInterface $object)
    {
        return $this->compare($object) > 0;
    }
    
    /**
     * Returns true if this object is greater than or equal to the given object.
     * 
     * @param object ComparableInterface $object A comparable object.
     * @return boolean True if this object is
     * greater than or equal to the given object.
     */
    public function ge(ComparableInterface $object)
    {
        return $this->compare($object) >= 0;
    }

    /**
     * Compares this object with the given object. This object and the 
     * given object are instances of the same class.
     *
     * @param object ComparableInterface $object The given object.
     * @return integer A number less than zero if this object is less than the given object,
     *     zero if this object equals the given object, and a number greater than zero
     *     if this object is greater than the given object.
     */
    protected abstract function compareTo(ComparableInterface $object);

    /**
     * Compares this object with the given object.
     *
     * @param object ComparableInterface $object A comparable object.
     * @return integer A number less than zero if this object is less than the given object,
     *     zero if this object equals the given object, and a number greater than zero
     *     if this object is greater than the given object.
     */
    public function compare(ComparableInterface $object)
    {
        $result = 0;
        if ($this->getClass() == $object->getClass()) {
            $result = $this->compareTo($object);
        } else {
            $result = strcmp($this->getClass()->getName(), $object->getClass()->getName());
        }
        return $result;
    }

    /**
     * Main program.
     *
     * @param array $args Command-line arguments.
     * @return integer Zero on succes; non-zero on failure.
     */
    public static function main($args)
    {
        printf("AbstractComparable main program.\n");
        return 0;
    }
}

if (realpath($argv[0]) == realpath(__FILE__)) {
    exit(AbstractComparable::main(array_slice($argv, 1)));
}
