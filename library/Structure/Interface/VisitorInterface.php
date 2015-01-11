<?php

namespace Structure\Interface;

/**
 * Interface implemented by all visitors.
 */
interface IVisitor
{
    /**
     * Visits the given object.
     *
     * @param object ObjectInterface $obj The object to visit.
     */
    public abstract function visit(ObjectInterface $obj);

    /**
     * IsDone predicate.
     *
     * @return boolean True if this visitor is done.
     */
    public abstract function isDone();
}
