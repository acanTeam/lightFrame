<?php
namespace Light\Mvc\Middleware;

class MethodOverride extends AbstractMiddleware
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     * @param  array  $settings
     */
    public function __construct($settings = array())
    {
        $this->settings = array_merge(array('key' => '_METHOD'), $settings);
    }

    /**
     * Call
     *
     * Implements Light middleware interface. This method is invoked and passed
     * an array of environment variables. This middleware inspects the environment
     * variables for the HTTP method override parameter; if found, this middleware
     * modifies the environment settings so downstream middleware and/or the Light
     * application will treat the request with the desired HTTP method.
     *
     * @return array[status, header, body]
     */
    public function call()
    {
        $env = $this->application->environment();
        if (isset($env['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            // Header commonly used by Backbone.js and others
            $env['light.method_override.original_method'] = $env['REQUEST_METHOD'];
            $env['REQUEST_METHOD'] = strtoupper($env['HTTP_X_HTTP_METHOD_OVERRIDE']);
        } elseif (isset($env['REQUEST_METHOD']) && $env['REQUEST_METHOD'] === 'POST') {
            // HTML Form Override
            $req = new \Light\Http\Request($env);
            $method = $req->post($this->settings['key']);
            if ($method) {
                $env['light.method_override.original_method'] = $env['REQUEST_METHOD'];
                $env['REQUEST_METHOD'] = strtoupper($method);
            }
        }
        $this->next->call();
    }
}
