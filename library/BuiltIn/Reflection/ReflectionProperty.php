<?php
class ReflectionProperty  implements Reflector  {
    /* 常量 */
    const integer IS_STATIC  = 1 ;
    const integer IS_PUBLIC  = 256 ;
    const integer IS_PROTECTED  = 512 ;
    const integer IS_PRIVATE  = 1024 ;

    /* 属性 */
    public $name ;
    public $class ;

    /* 方法 */
    final private void __clone ( void );
    public __construct ( mixed $class , string $name );
    public static string export ( mixed $class , string $name [, bool $return ] );
    public ReflectionClass getDeclaringClass ( void );
    public string getDocComment ( void );
    public int getModifiers ( void );
    public string getName ( void );
    public mixed getValue ([ object $object ] );
    public bool isDefault ( void );
    public bool isPrivate ( void );
    public bool isProtected ( void );
    public bool isPublic ( void );
    public bool isStatic ( void );
    public void setAccessible ( bool $accessible );
    public void setValue ( object $object , mixed $value );
    public string __toString ( void );
}
