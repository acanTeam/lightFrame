<?php

namespace Structure\Interface;

/**
 * Interface implemented by all stacks.
 */
interface StackInterface extends ContainerInterface
{
    /**
     * Pushes the given object onto this stack.
     *
     * @param object ObjectInterface $obj The object to push.
     */
    public abstract function push(ObjectInterface $obj);

    /**
     * Pops and returns the top object from this stack.
     *
     * @return object ObjectInterface The top object.
     */
    public abstract function pop();

    /**
     * Top getter.
     *
     * @return object ObjectInterface The top object on this stack.
     */
    public abstract function getTop();
}
