<?php

namespace Structure\Interface;

/**
 * Interface implemented by all graph vertices.
 */
interface VertexInterface extends ComparableInterface
{
    /**
     * Returns the number of this vertex.
     *
     * @return integer The number of this vertex.
     */
    public abstract function getNumber();
    
    /**
     * Returns an object the represents the weight associated with this vertex.
     * 
     * @return mixed The weight associated with this vertex.
     */
    public abstract function getWeight();

    /**
     * Returns the edges incident on this vertex.
     *
     * @return object IteratorAggregate The edges incident on this vertex.
     */
    public abstract function getIncidentEdges();

    /**
     * Returns the edges emanating from this vertex.
     *
     * @return object IteratorAggregate The edges emanating from this vertex.
     */
    public abstract function getEmanatingEdges();

    /**
     * Returns the vertices that are the predecessors of this vertex.
     *
     * @return object IteratorAggregate
     * The vertices that are the predecessors of this vertex.
     */
    public abstract function getPredecessors();

    /**
     * Returns the vertices that are the successors of this vertex.
     *
     * @return object IteratorAggregate The vertices that are the successors of this vertex.
     */
    public abstract function getSuccessors();
}
