<?php
class ReflectionMethod  extends ReflectionFunctionAbstract  implements Reflector  {
    /* 常量 */
    const integer IS_STATIC  = 1 ;
    const integer IS_PUBLIC  = 256 ;
    const integer IS_PROTECTED  = 512 ;
    const integer IS_PRIVATE  = 1024 ;
    const integer IS_ABSTRACT  = 2 ;
    const integer IS_FINAL  = 4 ;

    /* 属性 */
    public $name ;
    public $class ;

    /* 方法 */
    public __construct ( mixed $class , string $name );
    public static string export ( string $class , string $name [, bool $return = false ] );
    public Closure getClosure ( object $object );
    public ReflectionClass getDeclaringClass ( void );
    public int getModifiers ( void );
    public ReflectionMethod getPrototype ( void );
    public mixed invoke ( object $object [, mixed $parameter [, mixed $... ]] );
    public mixed invokeArgs ( object $object , array $args );
    public bool isAbstract ( void );
    public bool isConstructor ( void );
    public bool isDestructor ( void );
    public bool isFinal ( void );
    public bool isPrivate ( void );
    public bool isProtected ( void );
    public bool isPublic ( void );
    public bool isStatic ( void );
    public void setAccessible ( bool $accessible );
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
