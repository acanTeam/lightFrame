<?php
namespace PreDefine;

interface SplSubject
{
	/**
	 * @param $observer SplObserver
	 * @return void
	 */
	public function attach($observer);

	/**
	 * @param $observer SplObserver
	 * @return void
	 */
	public function detach($observer);

	/**
	 * @return void
	 */
	public function notify();
}
