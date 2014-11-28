<?php
class ReflectionExtension  implements Reflector  
{
    /* 属性 */
    public $name ;

    /* 方法 */
    final private void __clone ( void );
    public __construct ( string $name );
    public static string export ( string $name [, string $return = false ] );
    public array getClasses ( void );
    public array getClassNames ( void );
    public array getConstants ( void );
    public array getDependencies ( void );
    public array getFunctions ( void );
    public array getINIEntries ( void );
    public string getName ( void );
    public string getVersion ( void );
    public void info ( void );
    public void isPersistent ( void );
    public void isTemporary ( void );
    public string __toString ( void );
}
