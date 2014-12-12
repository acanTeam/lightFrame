<?php
class ReflectionClass implements Reflector
{
    /* Constants */
    const integer IS_IMPLICIT_ABSTRACT  = 16 ;
    const integer IS_EXPLICIT_ABSTRACT  = 32 ;
    const integer IS_FINAL  = 64 ;
    /* Properties */
    public $name ;
    /* Methods */
    public __construct ( mixed $argument )
    public static string export ( mixed $argument [, bool $return = false ] )
    public mixed getConstant ( string $name )
    public array getConstants ( void )
    public ReflectionMethod getConstructor ( void )
    public array getDefaultProperties ( void )
    public string getDocComment ( void )
    public int getEndLine ( void )
    public ReflectionExtension getExtension ( void )
    public string getExtensionName ( void )
    public string getFileName ( void )
    public array getInterfaceNames ( void )
    public array getInterfaces ( void )
    public ReflectionMethod getMethod ( string $name )
    public array getMethods ([ int $filter ] )
    public int getModifiers ( void )
    public string getName ( void )
    public string getNamespaceName ( void )
    public object getParentClass ( void )
    public array getProperties ([ int $filter ] )
    public ReflectionProperty getProperty ( string $name )
    public string getShortName ( void )
    public int getStartLine ( void )
    public array getStaticProperties ( void )
    public mixed getStaticPropertyValue ( string $name [, mixed &$def_value ] )
    public array getTraitAliases ( void )
    public array getTraitNames ( void )
    public array getTraits ( void )
    public bool hasConstant ( string $name )
    public bool hasMethod ( string $name )
    public bool hasProperty ( string $name )
    public bool implementsInterface ( string $interface )
    public bool inNamespace ( void )
    public bool isAbstract ( void )
    public bool isCloneable ( void )
    public bool isFinal ( void )
    public bool isInstance ( object $object )
    public bool isInstantiable ( void )
    public bool isInterface ( void )
    public bool isInternal ( void )
    public bool isIterateable ( void )
    public bool isSubclassOf ( string $class )
    public bool isTrait ( void )
    public bool isUserDefined ( void )
    public object newInstance ( mixed $args [, mixed $... ] )
    public object newInstanceArgs ([ array $args ] )
    public object newInstanceWithoutConstructor ( void )
    public void setStaticPropertyValue ( string $name , string $value )
    public string __toString ( void )
}