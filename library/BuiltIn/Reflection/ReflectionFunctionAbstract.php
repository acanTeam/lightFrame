<?php
class ReflectionFunctionAbstract  implements Reflector
{
    /* 属性 */
    public $name ;

    /* 方法 */
    final private void __clone ( void );
    public ReflectionClass getClosureScopeClass ( void );
    public object getClosureThis ( void );
    public string getDocComment ( void );
    public int getEndLine ( void );
    public ReflectionExtension getExtension ( void );
    public string getExtensionName ( void );
    public string getFileName ( void );
    public string getName ( void );
    public string getNamespaceName ( void );
    public int getNumberOfParameters ( void );
    public int getNumberOfRequiredParameters ( void );
    public array getParameters ( void );
    public string getShortName ( void );
    public int getStartLine ( void );
    public array getStaticVariables ( void );
    public bool inNamespace ( void );
    public bool isClosure ( void );
    public bool isDeprecated ( void );
    public bool isGenerator ( void );
    public bool isInternal ( void );
    public bool isUserDefined ( void );
    public bool returnsReference ( void );
    abstract public void __toString ( void );
}
