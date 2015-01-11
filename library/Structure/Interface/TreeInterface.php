<?php

namespace Structure\Interface;

/**
 * Interface implemented by all trees.
 */
interface TreeInterface extends ContainerInterface
{
    /**
     * Returns the object contained in this tree node.
     *
     * @return object ObjectInterface The object contained in this tree node.
     */
    public abstract function getKey();

    /**
     * Returns the specified subtree of this tree node.
     *
     * @param integer $i The number of the subtree to select.
     * @return object TreeInterface The specified subtree of this tree node.
     */
    public abstract function getSubtree($i);

    /**
     * Tests if this tree node is a leaf node. A leaf node is an internal node 
     * all the subtrees of which (if any) are external nodes.
     *
     * @return boolean True if this is a leaf node; false otherwise.
     */
    public abstract function isLeaf();

    /**
     * Returns the degree of this tree node. The degree of a node is the number 
     * of subtrees it has. The degree of an external node is zero.
     *
     * @return integer The degree of this tree node.
     */
    public abstract function getDegree();

    /**
     * Returns the height in the tree of this tree node. The height of a node is 
     * the length of the longest path from the node to a leaf. The height of an external node is -1.
     *
     * @return integer The height of this tree node.
     */
    public abstract function getHeight();

    /**
     * Causes a visitor to visit the nodes of this tree in depth-first traversal 
     * order starting from this node. This method invokes the PreVisit and PostVisit 
     * methods of the visitor for each node in this tree. The traversal continues 
     * as long as the IsDone method of the visitor returns false.
     *
     * @param object PrePostVisitorInterface $visitor The visitor to accept.
     */
    public abstract function depthFirstTraversal(PrePostVisitorInterface $visitor);

    /**
     * Causes a visitor to visit the nodes of this tree in breadth-first traversal order starting 
     * from this node. This method invokes the Visit method of the visitor for each node in this tree. 
     * The traversal continues as long as the IsDone method of the visitor returns false.
     *
     * @param object VisitorInterface $visitor The visitor to accept.
     */
    public abstract function breadthFirstTraversal(VisitorInterface $visitor);
}
