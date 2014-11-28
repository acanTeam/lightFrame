<?php
namespace PreDefine; 

interface OuterIterator extends Iterator
{
	/**
	 * @return Iterator
	 */
	public function getInnerIterator();
}
