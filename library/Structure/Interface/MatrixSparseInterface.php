<?php
namespace Structure\Interface;

/**
 * Interface implemented by all sparse matrix classes.
 *
 * @package Opus11
 */
interface ISparseMatrix extends MatrixInterface
{
    /**
     * Stores a zero value in this matrix at the given indices.
     *
     * @param array $indices A set of indices.
     */
    public abstract function putZero($indices);
}
