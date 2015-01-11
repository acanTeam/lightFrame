<?php

namespace Structure\Interface;

/**
 * A solver is an abstract machine that solves a problem
 * by exploring its solution space.
 */
interface SolverInterface extends ObjectInterface
{
    /**
     * Returns an optimal solution to a given problem by searching its
     * solution space. The optimal solution is a complete solution that
     * is feasible and for which the objective function is minimized
     *
     * @param object ISolution $initial The initial node in the solution 
     *    space from which to begin the search
     * @return object ISolution The optimal solution.
     */
    public abstract function solve(SolutionInterface $initial);
}
//}>a
?>
