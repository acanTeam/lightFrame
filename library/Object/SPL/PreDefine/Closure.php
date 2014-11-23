<?php
namespace PreDefine;

interface Closure 
{
	public function __construct();

	/**
	 * @param $closure Closure
	 * @newthis object
	 * @param $newscope string
	 * @return Closure
	 */
	public static function bind($closure, $newthis, $newscope = 'static');

	/**
	 * @parma $newthis object
	 * @parma newscope string
	 * @return Closure
	 */
	public function bindTo($newthis, $newscope = 'static');
}
