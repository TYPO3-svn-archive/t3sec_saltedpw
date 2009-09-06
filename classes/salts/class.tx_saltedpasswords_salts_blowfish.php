<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/interfaces/interface.tx_saltedpasswords_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_md5.php');


class tx_saltedpasswords_salts_blowfish extends tx_saltedpasswords_salts_md5 {
	
	static protected $saltLengthBlowfish = 16;
	
	static protected $saltPrefixBlowfish = '$2$';

	/**
	 * Returns length of required salt.
	 * 
	 * @access  public
	 * @return  integer  length of required salt
	 */
	public function getSaltLength() {
		return self::$saltLengthBlowfish;
	}

	/**
	 * Returns prefix of resulting salted hash indicating the type of used method.
	 * 
	 * @access  public
	 * @return  string  prefix of resulting has
	 */
	public function getSaltPrefix() {
		return self::$saltPrefixBlowfish;
	}

	/**
	 * Method determines if a given string is a valid salt
	 * 
	 * @param  string  $salt  string to check
	 * @return boolean        true if it's valid salt, otherwise false
	 */
	public function isValidSalt($salt) {
		$isValid = false;
		if ((strlen($salt) == $this->getSaltLength())
			&& (0 == strncmp('$2$', $salt, 3) || 0 == strncmp('$2a$', $salt, 4))
			&& (($this->getSaltLength() - 1) == strrpos($salt, '$'))) {
			$isValid = true;		
		} 
		return $isValid;
	}
}

?>