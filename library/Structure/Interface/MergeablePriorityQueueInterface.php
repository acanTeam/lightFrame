<?php

namespace Structure\Interface;

/**
 * Interface implemented by all mergeable priority queues.
 */
interface MergeablePriorityQueueInterface extends PriorityQueueInterface
{
    /**
     * Merges the contents of given priority queue into this priority queue.
     *
     * @param object MergeablePriorityQueueInterface $queue A mergeable priority queue.
     */
    public abstract function merge(MergeablePriorityQueueInterface $queue);
}
