<?php
namespace Light\Logger;

class Logger
{
    const EMERGENCY = 1;
    const ALERT     = 2;
    const CRITICAL  = 3;
    const ERROR     = 4;
    const WARN      = 5;
    const NOTICE    = 6;
    const INFO      = 7;
    const DEBUG     = 8;

    /**
     * @var array
     */
    protected static $levels = array(
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARN      => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG'
    );

    /**
     * @var mixed
     */
    protected $writer;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var int
     */
    protected $level = self::DEBUG;

    /**
     * Constructor, initialize the logger
     *
     * @param  mixed $writer
     */
    public function __construct($writer)
    {
        $this->writer = $writer;
    }

    /**
     * Get logger enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set logger enabled
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean) $enabled;
    }

    /**
     * Set logger level
     *
     * @param int $level
     * @throws \InvalidArgumentException If invalid logger level specified
     */
    public function setLevel($level)
    {
        if (!isset(self::$levels[$level])) {
            throw new \InvalidArgumentException('Invalid log level');
        }
        $this->level = $level;
    }

    /**
     * Get logger level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set logger writer
     *
     * @param mixed $writer
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;
    }

    /**
     * Get logger writer
     *
     * @return mixed
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * Is logger enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Logger debug message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function debug($object, $context = array())
    {
        return $this->log(self::DEBUG, $object, $context);
    }

    /**
     * Logger info message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function info($object, $context = array())
    {
        return $this->log(self::INFO, $object, $context);
    }

    /**
     * Logger notice message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function notice($object, $context = array())
    {
        return $this->log(self::NOTICE, $object, $context);
    }

    /**
     * Logger warning message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function warning($object, $context = array())
    {
        return $this->log(self::WARN, $object, $context);
    }

    /**
     * Logger error message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function error($object, $context = array())
    {
        return $this->log(self::ERROR, $object, $context);
    }

    /**
     * Logger critical message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function critical($object, $context = array())
    {
        return $this->log(self::CRITICAL, $object, $context);
    }

    /**
     * Logger alert message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function alert($object, $context = array())
    {
        return $this->log(self::ALERT, $object, $context);
    }

    /**
     * Logger emergency message
     *
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     */
    public function emergency($object, $context = array())
    {
        return $this->log(self::EMERGENCY, $object, $context);
    }

    /**
     * Logger the message
     *
     * @param mixed $level
     * @param mixed $object
     * @param array $context
     * @return mixed | bool returns of the Logger, or false if Logger not set or not enabled
     * @throws \InvalidArgumentException If invalid log level
     */
    public function log($level, $object, $context = array())
    {
        if (!isset(self::$levels[$level])) {
            throw new \InvalidArgumentException('Invalid log level supplied to function');
        }

        if (empty($this->enabled) || empty($this->writer) || $level > $this->level) {
            return false;
        }

        $message = (string) $object;
        if (count($context) > 0) {
            if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
                $message .= ' - ' . $context['exception'];
                unset($context['exception']);
            }
            $message = $this->interpolate($message, $context);
        }
        return $this->writer->write($message, $level);
    }

    /**
     * Interpolate log message
     *
     * @param mixed $message The log message
     * @param array $context An array of placeholder values
     * @return string The processed string
     */
    protected function interpolate($message, $context = array())
    {
        $replace = array();
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = $value;
        }
        return strtr($message, $replace);
    }
}
