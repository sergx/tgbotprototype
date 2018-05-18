<?php
abstract class basicClass {
	protected $registry;
	public function __construct($registry) {
		$this->registry = $registry;
	}
	
	public function __get($key) {
	  //trigger_error($key, E_USER_DEPRECATED);
	  if(!$this->registry){
	    trigger_error($key, E_USER_DEPRECATED);
	  }else{
	    return $this->registry->get($key);
	  }
		
	}
	
	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
}