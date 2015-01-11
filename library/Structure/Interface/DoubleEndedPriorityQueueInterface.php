<?php

namespace Structure\Interface;

/**
 * Interface implemented by all double-ended priority queues.
 */
interface DoubledEndedPriorityQueueInterface extends PriorityQueueInterface
{
    /**
     * Dequeues and returns the largest object in this queue.
     *
     * @return object ComparableInterface The largest object in this queue.
     */
    public abstract function dequeueMax();

    /**
     * Finds and returns the largest object in this queue.
     *
     * @return object ComparableInterface The largest object in this queue.
     */
    public abstract function findMax();
}
