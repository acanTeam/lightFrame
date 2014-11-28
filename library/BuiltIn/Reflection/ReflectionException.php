<?php
class ReflectionException  extends Exception  {
    /* 属性 */
    protected string $message ;
    protected int $code ;
    protected string $file ;
    protected int $line ;

    /* 继承的方法 */
    final public string Exception::getMessage ( void );
    final public Exception Exception::getPrevious ( void );
    final public int Exception::getCode ( void );
    final public string Exception::getFile ( void );
    final public int Exception::getLine ( void );
    final public array Exception::getTrace ( void );
    final public string Exception::getTraceAsString ( void );
    public string Exception::__toString ( void );
    final private void Exception::__clone ( void );
}
