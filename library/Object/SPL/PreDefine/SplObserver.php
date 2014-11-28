<?php
namespace PreDefine; 

interface SplObserver
{
	/**
	 * @param $subject SplSubject
	 * @return void
	 */
	public function update($subject);
}
