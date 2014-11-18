<?php
namespace Light\Logger;

class Writer
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * Constructor initialize the logger writer
     *
     * @param resource $resource
     * @throws \InvalidArgumentException If invalid resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Cannot create LogWriter. Invalid resource handle.');
        }

        $this->resource = $resource;
    }

    /**
     * Write message
     *
     * @param mixed $message
     * @param int $level
     * @return int | bool
     */
    public function write($message, $level = null)
    {
        return fwrite($this->resource, (string) $message . PHP_EOL);
    }
}
