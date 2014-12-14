<?php
namespace Light\View;

class View
{
    /**
     * Datas available to the view
     * @var \Light\Stdlib\Parameters
     */
    protected $datas;

    /**
     * Paths of view
     * @var string
     */
    protected $templatePaths;

    /**
     * Constructor initialize the View.
     */
    public function __construct()
    {
        $this->datas = new \Light\Stdlib\Parameters();
    }

    /**
     * Does view datas have value with key?
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->datas->has($key);
    }

    /**
     * Return view datas value with key
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->datas->get($key);
    }

    /**
     * Set view datas value with key
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->datas->set($key, $value);
    }

    /**
     * Set view datas value as Closure with key
     *
     * @param string $key
     * @param mixed $value
     */
    public function keep($key, Closure $value)
    {
        $this->datas->keep($key, $value);
    }

    /**
     * Get view datas
     *
     * @return array
     */
    public function all()
    {
        return $this->datas->all();
    }

    /**
     * Replace view datas
     *
     * @param  array  $datas
     */
    public function replace(array $datas)
    {
        $this->datas->replace($datas);
    }

    /**
     * Clear view datas
     */
    public function clear()
    {
        $this->datas->clear();
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Get datas from view
     */
    public function getData($key = null)
    {
        if (!is_null($key)) {
            return isset($this->datas[$key]) ? $this->datas[$key] : null;
        } else {
            return $this->datas->all();
        }
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Set datas for view
     */
    public function setDatas()
    {
        $args = func_get_args();
        if (count($args) === 1 && is_array($args[0])) {
            $this->datas->replace($args[0]);
        } elseif (count($args) === 2) {
            // Ensure original behavior is maintained. DO NOT invoke stored Closures.
            if (is_object($args[1]) && method_exists($args[1], '__invoke')) {
                $this->datas->set($args[0], $this->datas->protect($args[1]));
            } else {
                $this->datas->set($args[0], $args[1]);
            }
        } else {
            throw new \InvalidArgumentException('Cannot set View datas with provided arguments. Usage: `View::setData( $key, $value );` or `View::setData([ key => value, ... ]);`');
        }
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Append datas to view
     * @param  array $datas
     */
    public function appendData($datas)
    {
        if (!is_array($datas)) {
            throw new \InvalidArgumentException('Cannot append view datas. Expected array argument.');
        }
        $this->datas->replace($datas);
    }

    /**
     * Set the base paths that contains view templates
     *
     * @param array $paths
     * @throws \InvalidArgumentException If directory is not a directory
     */
    public function setTemplatePaths($paths)
    {
        $templatePaths = array();
        foreach ($paths as $path) {
            $templatePaths[] = rtrim($path, DIRECTORY_SEPARATOR);
        }

        $this->templatePaths = $templatePaths;
    }

    /**
     * Get templates base paths
     *
     * @return array 
     */
    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }

    /**
     * Get fully qualified path to template file using templates base directory
     * @param  string $file The template file pathname relative to templates base directory
     * @return string
     */
    public function getTemplateFile($template)
    {
        $templateFile = '';
        foreach ($this->templatePaths as $templatePath) {
            $templateFile = $templatePath . DIRECTORY_SEPARATOR . ltrim($template, DIRECTORY_SEPARATOR);
            $templateFile .= strpos($template, '.') === false ? '.php' : '';
            if (file_exists($templateFile)) {
                break;
            }
        }

        return $templateFile;
    }

    /**
     * Display template. This method echoes the rendered template to the current output buffer
     *
     * @param string $template Pathname of template file relative to templates directory
     * @param array $datas Any additonal datas to be passed to the template.
     */
    public function display($template, $datas = null)
    {
        echo $this->fetch($template, $datas);
    }

    /**
     * Return the contents of a rendered template file
     *
     * @param string $template The template pathname, relative to the template base directory
     * @param array  $datas Any additonal datas to be passed to the template.
     * @return string The rendered template
     */
    public function fetch($template, $datas = null)
    {
        return $this->render($template, $datas);
    }

    /**
     * Render a template file. This method should be overridden by custom view subclasses
     *
     * @param string $template The template pathname, relative to the template base directory
     * @param array $datas Any additonal datas to be passed to the template.
     * @return string The rendered template
     * @throws \RuntimeException If resolved template pathname is not a valid file
     */
    protected function render($template, $datas = null)
    {
        $templateFile = $this->getTemplateFile($template);
        if (!is_file($templateFile)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        $datas = array_merge($this->datas->all(), (array) $datas);
        extract($datas);
        ob_start();
        require $templateFile;

        return ob_get_clean();
    }
}
