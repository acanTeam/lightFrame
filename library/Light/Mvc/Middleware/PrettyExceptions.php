<?php
namespace Light\Mvc\Middleware;

class PrettyExceptions extends AbstractMiddleware
{
    /**
     * @var array
     */
    protected $configs;

    /**
     * Constructor, initialize the prettyException.
     * @param array $configs
     */
    public function __construct($configs = array())
    {
        $this->configs = $configs;
    }

    /**
     * Deal with the prettyException
     */
    public function call()
    {
        try {
            $this->next->call();
        } catch (\Exception $e) {
            $logger = $this->application->getLogger(); // Force Light to append log to env if not already
            $env = $this->application->environment();
            $env['light.logger'] = $logger;
            $env['light.logger']->error($e);
            $this->application->contentType('text/html');
            $this->application->response()->setStatus(500);
            $this->application->response()->body($this->renderBody($env, $e));
        }
    }

    /**
     * Render response body
     *
     * @param array $env
     * @param \Exception $exception
     * @return string
     */
    protected function renderBody(&$env, $exception)
    {
        $title = 'Light Application Error';
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = str_replace(array('#', '\n'), array('<div>#', '</div>'), $exception->getTraceAsString());
        $html = sprintf('<h1>%s</h1>', $title);
        $html .= '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));
        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }
        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        }
        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }

        return sprintf("<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>", $title, $html);
    }
}
