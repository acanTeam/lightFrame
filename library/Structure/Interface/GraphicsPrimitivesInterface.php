<?php

namespace Structure\Interface;

use \Structure\Class\Point;

/**
 * Encapsulates a simple set of primitive operations on graphical objects.
 */
interface GraphicsPrimitivesInterface
{
    /**
     * Draws this graphical object on the screen.
     */
    public abstract function draw();

    /**
     * Erases this graphical object from the screen.
     */
    public abstract function erase();

    /**
     * Moves this graphical object to a given point on the screen.
     *
     * @param object Point $p The point to which this object is to be moved.
     */
    public abstract function moveTo(Point $p);
}
