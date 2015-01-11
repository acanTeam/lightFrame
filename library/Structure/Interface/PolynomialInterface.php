<?php

namespace Structure\Interface;

use \Structure\Class\Term;

/**
 * Interface implemented by all polynomials.
 */
interface PolynomialInterface
{
    /**
     * Adds the specified term to this polynomial.
     *
     * @param object Term $term The term to be added to this polynomial.
     */
    public abstract function add(Term $term);

    /**
     * Differentiates this polynomial. The terms of this polynomial are each 
     * differentiated one-by-one.
     */
    public abstract function differentiate();

    /**
     * Returns the sum of this polynomial and the specified polynomial.
     *
     * @param object PolynomialInterface $polynomial The polynomial to add to this polynomial.
     * @return object PolynomialInterface The sum of this polynomial and the specified polynomial.
     */
    public abstract function plus(PolynomialInterface $polynomial);
}
