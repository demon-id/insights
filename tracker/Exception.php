<?php

namespace insights\api\tracker;

class Exception extends \Exception {

	private $answer;

	public function __construct($message, $code, $previous, $answer = null) {
		parent::__construct($message, $code, $previous);
		$this->answer = $answer;
	}

	public final function getAnswer(){
		return $this->answer;
	}
}