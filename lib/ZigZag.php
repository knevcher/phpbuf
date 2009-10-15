<?php
/**
 * @author Andrey Lepeshkin (lilipoper@gmail.com)
 * @link http://code.google.com/p/php-protobuf/
 *
 */
class ZigZag {
	/**
	 * Only as static class
	 *
	 */
	private function __construct() {}
	/**
	 * ZigZag encoding
	 *
	 * @param integer $value
	 * @return integer
	 */
	public static function decode($value) {
		if(!is_integer($value) || $value < 0) { throw new ZigZag_Exception("value mast be unsigned integer"); }
		$result = round($value/2);
		if($value % 2 == 1) { $result = -($result); }
		return $result;
	}
	/**
	 * ZigZag decoding
	 *
	 * @param integer $value
	 * @return integer
	 */
	public static function encode($value) {
		if(!is_integer($value)) { throw new ZigZag_Exception("value mast be integer"); }
		if($value >= 0) {return $value * 2;}
		else { return abs($value) * 2 - 1; }
	}
}
?>