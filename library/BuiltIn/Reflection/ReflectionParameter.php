<?php
class ReflectionParameter  implements Reflector  {
    /* 属性 */
    public $name ;

    /* 方法 */
    public bool allowsNull ( void );
    public bool canBePassedByValue ( void );
    final private void __clone ( void );
    public __construct ( string $function , string $parameter );
    public static string export ( string $function , string $parameter [, bool $return ] );
    public ReflectionClass getClass ( void );
    public ReflectionClass getDeclaringClass ( void );
    public ReflectionFunctionAbstract getDeclaringFunction ( void );
    public mixed getDefaultValue ( void );
    public string getDefaultValueConstantName ( void );
    public string getName ( void );
    public int getPosition ( void );
    public bool isArray ( void );
    public bool isCallable ( void );
    public bool isDefaultValueAvailable ( void );
    public bool isDefaultValueConstant ( void );
    public bool isOptional ( void );
    public bool isPassedByReference ( void );
    public string __toString ( void );
}
