<?php
namespace app\common\library;

class Service {

	protected $error = null;

	public function __construct() {
		$this->_initialize();
	}

	protected function _initialize() {}

	public function getError() {
		return $this->error;
	}
}