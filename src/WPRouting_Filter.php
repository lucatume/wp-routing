<?php

/**
 * Class WPRouting_Filters
 *
 * Manages the access filters associated to a route.
 *
 */
class WPRouting_Filters
{
    protected $filters;

    /**
     * @param array $filters An array of filters, callable functions, to validate the user access rights.
     */
    public function __construct($filters)
    {
        if (!is_array($filters)) {
            throw new BadMethodCallException('Route filters must be an array!', 1);
        }
        $this->filters = $filters;
    }

    /**
     * Calls the filters in sequence and returns a logic AND of their return values.
     *
     * The function is meant to be called in the WP_Route::check_access method. By default a user is granted access (the function will return true).
     *
     * @return bool
     */
    public function callFilters()
    {
        foreach ($this->filters as $filterFunction) {
            // if even one filters returns false then return false
            if ((boolean)call_user_func($filterFunction) == false) {
                return false;
            }
        }
        return true;
    }
} 