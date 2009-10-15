<?php
/**
 * @author Andrey Lepeshkin (lilipoper@gmail.com)
 * @link http://github.com/undr/phpbuf
 *
 */
class PhpBuf_Field_Abstract  implements PhpBuf_Field_Interface {
	
	/**
	 * Wire types
	 *
	 */
	const WIRETYPE_VARINT = 0;
	const WIRETYPE_FIXED64 = 1;
	const WIRETYPE_LENGTH_DELIMITED = 2;
	const WIRETYPE_START_GROUP = 3;
	const WIRETYPE_END_GROUP = 4;
	const WIRETYPE_FIXED32 = 5;
	
	/**
	 * Value of field
	 *
	 * @var mixed
	 */
	protected $value = null;
	/**
	 * Additional information for field.
	 * If field has enum type, then extra contain array of enumerable values
	 * If field has message type, then extra contain name of message class as string
	 *
	 * @var mixed
	 */
	protected $extra;
	/**
	 * Has 1, 2 or 3. PhpBuf_Rule::REQUIRED, PhpBuf_Rule::OPTIONAL, PhpBuf_Rule::REPEATED
	 *
	 * @var integer
	 */
	protected $rule;
	/**
	 * Index of field tag
	 *
	 * @var integer
	 */
	protected $index;
	/**
	 * Wire type
	 *
	 * @var index
	 */
	protected $wireType;

	/**
	 * Valid wire types
	 *
	 * @var array
	 */
	protected static $wireTypes = array("Varint", "Fixed64", "LenghtDelimited", "StartGroup", "EndGroup", "Fixed32");
	
	/**
	 * Convert wire type id to wire type class name
	 *
	 * @param integer $id
	 * @return string
	 */
	public static function getWireTypeNameById($id) {
		return self::$wireTypes[$id];
	}
	/**
	 * Fabric method, create classes extended from PhpBuf_Field_Abstract
	 *
	 * @param string $type
	 * @param array $args
	 * @return PhpBuf_Field_Abstract
	 */
	public static function create($type, $args) {
		$class = "PhpBuf_Field_" . PhpBuf_Type::getNameById($type) ;
		if(!class_exists($class)) { throw new PhpBuf_Field_Exception("field '$class' not found"); }
		return new $class($args['index'], $args['rule'], $args['extra']);
	}	
	/**
	 * Constructor. Возможно его нужно закрыть
	 *
	 * @param integer $index
	 * @param integer $rule
	 * @param mixed $extra
	 */
	public function __construct($index, $rule, $extra) {
		$this->index = $index;
		$this->rule = $rule;
		$this->extra = $extra;
	}
	/**
	 * To set value of field
	 *
	 * @param mixed $value
	 */
	public function setValue($value) {
		if(!$this->checkTypeOfValue($value)) { throw new PhpBuf_Field_Exception("wrong type of value (value type: " . gettype($value) . ")"); }
		$this->value = $value;
	}
	/**
	 * To get value of field
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
	/**
	 * Read field from reader
	 *
	 * @param PhpBuf_IO_Reader_Interface $reader
	 */
	public function read(PhpBuf_IO_Reader_Interface $reader) {
		if($this->rule == PhpBuf_Rule::REPEATED) {
			$this->value[] = $this->readImpl($reader);
		} else {
			$this->value = $this->readImpl($reader);
		}
	}
	/**
	 * Write field to writer
	 *
	 * @param PhpBuf_IO_Writer_Interface $writer
	 */
	public function write(PhpBuf_IO_Writer_Interface $writer) { 
		if($this->rule == PhpBuf_Rule::OPTIONAL && $this->value === null) { return; }
		
		if($this->rule == PhpBuf_Rule::REPEATED) {
			if($this->value === null) { return; }
			foreach ($this->value as $item) {
				$this->writeHeader($writer);
				$this->writeImpl($writer, $item);
			}
		} else {
			$this->writeHeader($writer);
			$this->writeImpl($writer, $this->value);
		}
	}
	/**
	 * Enter description here...
	 *
	 * @return integer
	 */
	public function getWireType() {
		/**
		 * return $this->wireType; not work, returned null. May by php bug?
		 */
		$wt = $this->wireType;
		return $wt;
	}
	/**
	 * Enter description here...
	 *
	 * @param PhpBuf_IO_Reader_Interface $reader
	 */
	protected function readImpl(PhpBuf_IO_Reader_Interface $reader) {
		throw new PhpBuf_Field_Exception("you mast override function PhpBuf_Field_Abstract#readImpl");
	}
	/**
	 * Enter description here...
	 *
	 * @param PhpBuf_IO_Writer_Interface $writer
	 */
	protected function writeImpl(PhpBuf_IO_Writer_Interface $writer) { 
		throw new PhpBuf_Field_Exception("you mast override function PhpBuf_Field_Abstract#writeImpl");
	}
	/**
	 * Enter description here...
	 *
	 * @param PhpBuf_IO_Reader_Interface $reader
	 * @return mixed
	 */
	protected function readWireTypeData(PhpBuf_IO_Reader_Interface $reader) {
		return call_user_func_array(array("PhpBuf_WireType_" . self::getWireTypeNameById($this->wireType), "read"), array($reader));
	}
	/**
	 * Enter description here...
	 *
	 * @param PhpBuf_IO_Writer_Interface $writer
	 * @param mixed $value
	 */
	protected function writeWireTypeData(PhpBuf_IO_Writer_Interface $writer, $value) {
		call_user_func_array(array("PhpBuf_WireType_" . self::getWireTypeNameById($this->wireType), "write"), array($writer, $value));
	}
	/**
	 * Enter description here...
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	protected function checkTypeOfValue($value) {
		if($this->rule == PhpBuf_Rule::REPEATED && !is_array($value)) {
			return false;
		} else if($this->rule == PhpBuf_Rule::REPEATED) {
			foreach ($value as $item) {
				if(!$this->checkTypeOfValueImpl($item)) { return false; }
			}
			return true;
		}
		return $this->checkTypeOfValueImpl($value);
	}
	/**
	 * Enter description here...
	 *
	 * @param mixed $value
	 */
	protected function checkTypeOfValueImpl($value) { 
		throw new PhpBuf_Field_Exception("you mast override function PhpBuf_Field_Abstract#checkTypeOfValueImpl");
	}
	/**
	 * Enter description here...
	 *
	 * @param PhpBuf_IO_Writer_Interface $writer
	 */
	protected function writeHeader(PhpBuf_IO_Writer_Interface $writer) {
		$value = $this->index << 3;
		$value = $value | $this->wireType;
		PhpBuf_Base128::encodeToWriter($writer, $value);
	}
}
?>