<?php

namespace Structure\Interface;

/**
 * Interface implemented by all digraphs.
 */
interface DigraphInterface extends GraphInterface
{
    /**
     * Tests whether this graph is strongly connected.
     *
     * @return boolean True if this graph is strongly connected; false otherwise.
     */
    public abstract function isStronglyConnected();

    /**
     * Causes a visitor to visit the vertices of this directed graph in topological order.
     * This method takes a visitor and, as long as the IsDone method of that visitor 
     * returns false, this method invokes the Visit method of the visitor for each 
     * vertex in the graph. The order in which the vertices are visited is given by a 
     * topological sort of the vertices.
     *
     * @param object VisitorInterface $visitor The visitor to accept.
     */
    public abstract function topologicalOrderTraversal(VisitorInterface $visitor);
}
