<?php

/**
* Session Data Handle 
* Call This Class 
*
*	require_once 'Session.php';
*	Session::start();
*	Session::set('best','test');
*	Session::set('name',Array('name'=>'jees','number'=>'9633'));
*
*	echo Session::get('name','number');
*	Session::destroy();
*	Session::display();
*	
* 	@author  Jees K Denny
* 	@version 1.0, 28/12/16
* 	@since   28/12/16
*/

class Session
{	
	private static $isSessionStart = false;

	public static function start()
	{
		if(self::$isSessionStart == false)
		{
			session_start();
			self::$isSessionStart = true;
		}
		
	}
	
	public static function set($key, $value)
	{
		if(self::$sessionStarted == false)
		{
			self::start();
			$_SESSION[$key]=$value;
			
		}else{

			$_SESSION[$key]=$value;
		}
	}

	public static function get($key, $secondKey= false)
	{
		if($secondKey==true){

			if(isset($_SESSION[$key][$secondKey]))
				return $_SESSION[$key][$secondKey];

		}else{

			if(isset($_SESSION[$key]))
			return $_SESSION[$key];

		}

		return false;

	}

	public static function display()
	{
		echo '<pre>';
		print_r($_SESSION);
		echo '</pre>';
	}

	public static function destroy()
	{
		if(self::$isSessionStart==true){
			session_unset();
			session_destroy();
		}
	}

}