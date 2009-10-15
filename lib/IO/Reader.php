<?php
/**
 * @author Andrey Lepeshkin (lilipoper@gmail.com)
 * @link http://code.google.com/p/php-protobuf/
 *
 */
class IO_Reader implements IO_Reader_Interface  {
	protected $data = null;
	protected $lenght = 0;
	protected $position = 0;
	protected $lastLenght = 0;
	public static function createFromWriter(IO_Writer_Interface $writer) {
		return new IO_Reader($writer->getData());
	}
	public function __construct($data) {
		$this->data = $data;
		$this->lenght = strlen($data);
	}
	public function getByte() {
		$this->check();
		$this->lastLenght = 1;
		return $this->data[$this->position++];
	}
	public function getBytes($lenght = 1) {
		$this->check($lenght);
		$returnData = substr($this->data, $this->position, $lenght);
		$this->position = $this->position + $lenght;
		$this->lastLenght = $lenght;
		return $returnData;
	}
	public function setPosition($position = 0) {
		$this->position = $position;
	}
	public function getPosition() {
		return $this->position;
	}
	public function next($steps = 1) {
		$this->position + $steps;
	}
	public function back() {
		$this->position--;
	}
	public function redo() {
		$this->position = $this->position - $this->lastLenght;
	}
	
	protected function check($lenght = 1) {
		if($this->position + $lenght > $this->lenght) { throw new IO_Exception("end of data"); }
	}
}
?>