<?php
class Cookies
{
	
	public $_expiry = 86400;
	
	public function set($name, $val){
		
		//setcookie($name, $val, time()+$this->_expiry);
		setcookie($name, $val);
		
	}

	public function get($name){
		
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default);
		
	}

	public function remove($name){
		
		setcookie ($name, '', time() - 3600);
		
	}

	public function isEmpty($name)
	  {
		  
		return empty($_COOKIE[$name]);
		
	  }
	  
}
?>