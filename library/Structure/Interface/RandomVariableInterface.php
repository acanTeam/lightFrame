<?php
namespace Structure\Interface;

/**
 * Interface implemented by all random variables.
 */
interface RandomVariableInterfac3 extends ObjectInterface
{
    /**
     * Returns the next sample.
     *
     * @return float The next sample.
     */
    public abstract function next();
}
