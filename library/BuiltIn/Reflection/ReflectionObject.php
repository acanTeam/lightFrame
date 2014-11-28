<?php
class ReflectionObject  extends ReflectionClass  implements Reflector  {
    /* 常量 */
    const integer IS_IMPLICIT_ABSTRACT  = 16 ;
    const integer IS_EXPLICIT_ABSTRACT  = 32 ;
    const integer IS_FINAL  = 64 ;

    /* 属性 */
    public $name ;

    /* 方法 */
    public __construct ( object $argument );
    public static string export ( string $argument [, bool $return ] );

    /* 继承的方法 */
    public ReflectionClass::__construct ( mixed $argument );
    public static string ReflectionClass::export ( mixed $argument [, bool $return = false ] );
    public mixed ReflectionClass::getConstant ( string $name );
    public array ReflectionClass::getConstants ( void );
    public ReflectionMethod ReflectionClass::getConstructor ( void );
    public array ReflectionClass::getDefaultProperties ( void );
    public string ReflectionClass::getDocComment ( void );
    public int ReflectionClass::getEndLine ( void );
    public ReflectionExtension ReflectionClass::getExtension ( void );
    public string ReflectionClass::getExtensionName ( void );
    public string ReflectionClass::getFileName ( void );
    public array ReflectionClass::getInterfaceNames ( void );
    public array ReflectionClass::getInterfaces ( void );
    public ReflectionMethod ReflectionClass::getMethod ( string $name );
    public array ReflectionClass::getMethods ([ int $filter ] );
    public int ReflectionClass::getModifiers ( void );
    public string ReflectionClass::getName ( void );
    public string ReflectionClass::getNamespaceName ( void );
    public object ReflectionClass::getParentClass ( void );
    public array ReflectionClass::getProperties ([ int $filter ] );
    public ReflectionProperty ReflectionClass::getProperty ( string $name );
    public string ReflectionClass::getShortName ( void );
    public int ReflectionClass::getStartLine ( void );
    public array ReflectionClass::getStaticProperties ( void );
    public mixed ReflectionClass::getStaticPropertyValue ( string $name [, mixed &$def_value ] );
    public array ReflectionClass::getTraitAliases ( void );
    public array ReflectionClass::getTraitNames ( void );
    public array ReflectionClass::getTraits ( void );
    public bool ReflectionClass::hasConstant ( string $name );
    public bool ReflectionClass::hasMethod ( string $name );
    public bool ReflectionClass::hasProperty ( string $name );
    public bool ReflectionClass::implementsInterface ( string $interface );
    public bool ReflectionClass::inNamespace ( void );
    public bool ReflectionClass::isAbstract ( void );
    public bool ReflectionClass::isCloneable ( void );
    public bool ReflectionClass::isFinal ( void );
    public bool ReflectionClass::isInstance ( object $object );
    public bool ReflectionClass::isInstantiable ( void );
    public bool ReflectionClass::isInterface ( void );
    public bool ReflectionClass::isInternal ( void );
    public bool ReflectionClass::isIterateable ( void );
    public bool ReflectionClass::isSubclassOf ( string $class );
    public bool ReflectionClass::isTrait ( void );
    public bool ReflectionClass::isUserDefined ( void );
    public object ReflectionClass::newInstance ( mixed $args [, mixed $... ] );
    public object ReflectionClass::newInstanceArgs ([ array $args ] );
    public object ReflectionClass::newInstanceWithoutConstructor ( void );
    public void ReflectionClass::setStaticPropertyValue ( string $name , string $value );
    public string ReflectionClass::__toString ( void );
}
