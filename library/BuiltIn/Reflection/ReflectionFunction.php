<?php
class ReflectionFunction  extends ReflectionFunctionAbstract  implements Reflector
{
    /* 常量 */
    const integer IS_DEPRECATED  = 262144 ;

    /* 属性 */
    public $name ;

    /* 方法 */
    public __construct ( mixed $name );
    public static string export ( string $name [, string $return ] );
    public Closure getClosure ( void );
    public mixed invoke ([ mixed $parameter [, mixed $... ]] );
    public mixed invokeArgs ( array $args );
    public bool isDisabled ( void );
    public string __toString ( void );

    /* 继承的方法 */
    final private void ReflectionFunctionAbstract::__clone ( void );
    public ReflectionClass ReflectionFunctionAbstract::getClosureScopeClass ( void );
    public object ReflectionFunctionAbstract::getClosureThis ( void );
    public string ReflectionFunctionAbstract::getDocComment ( void );
    public int ReflectionFunctionAbstract::getEndLine ( void );
    public ReflectionExtension ReflectionFunctionAbstract::getExtension ( void );
    public string ReflectionFunctionAbstract::getExtensionName ( void );
    public string ReflectionFunctionAbstract::getFileName ( void );
    public string ReflectionFunctionAbstract::getName ( void );
    public string ReflectionFunctionAbstract::getNamespaceName ( void );
    public int ReflectionFunctionAbstract::getNumberOfParameters ( void );
    public int ReflectionFunctionAbstract::getNumberOfRequiredParameters ( void );
    public array ReflectionFunctionAbstract::getParameters ( void );
    public string ReflectionFunctionAbstract::getShortName ( void );
    public int ReflectionFunctionAbstract::getStartLine ( void );
    public array ReflectionFunctionAbstract::getStaticVariables ( void );
    public bool ReflectionFunctionAbstract::inNamespace ( void );
    public bool ReflectionFunctionAbstract::isClosure ( void );
    public bool ReflectionFunctionAbstract::isDeprecated ( void );
    public bool ReflectionFunctionAbstract::isGenerator ( void );
    public bool ReflectionFunctionAbstract::isInternal ( void );
    public bool ReflectionFunctionAbstract::isUserDefined ( void );
    public bool ReflectionFunctionAbstract::returnsReference ( void );
    abstract public void ReflectionFunctionAbstract::__toString ( void );
}
