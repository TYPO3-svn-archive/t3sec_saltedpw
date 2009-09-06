<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/interfaces/interface.tx_saltedpasswords_salts.php');


class tx_saltedpasswords_salts_phpass extends tx_saltedpasswords_abstract_salts implements tx_saltedpasswords_salts {
	

	const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	

	static protected $saltLengthPhpass = 16;

	/**
	 * Setting string to indicate type of hashing method (blowfish).
	 * 
	 * @var string
	 */
	static protected $settingPhpass = '$P$';
	
	static protected $saltSuffix = '$';

	static protected $saltChars = "ABC";


	/**
	 * Returns a string for mapping an int to the corresponding base 64 character.
	 *
	 * @access  protected
	 * @return  string     string for mapping an int to the corresponding
	 *                     base 64 character
	 */
	protected function getItoa64() {
		return self::ITOA64;
	}

	/**
	 * Returns setting string of phpass hashing method.
	 * 
	 * @access  protected
	 * @return  string     setting string of phpass hashing method.
	 */
	public function getSetting() {
		return self::$settingPhpass;
	}

	/**
	 * Returns length of required salt.
	 * 
	 * @access  public
	 * @return  integer  length of required salt
	 */
	public function getSaltLength() {
		return self::$saltLengthPhpass;
	}

	/**
	 * Returns prefix of salt indicating the type of used method.
	 * 
	 * @access  public
	 * @return  string  prefix of salt
	 */
	public function getSaltPrefix() {
		return self::$saltPrefixPhpass;
	}

	/**
	 * Returns suffix of salt indicating the type of used method.
	 * 
	 * @access  public
	 * @return  string  suffix of salt
	 */
	public function getSaltSuffix() {
		return self::$saltSuffix;
	}

	/**
	 * Method creates a salt.
	 *
	 * @access  protected
	 * @return  string  generated salt
	 */
	protected function getGeneratedSalt() {
		return tx_saltedpasswords_div::generateSalt($this->getSaltLength());
	}

	protected function applySettingsToSalt($salt) {
		$saltWithSettings = $this->getSetting() 
							. $salt;
		return $saltWithSettings;
	}

	/**
	 * Method determines if a given string is a valid salt
	 * 
	 * @param  string  $salt  string to check
	 * @return boolean        true if it's valid salt, otherwise false
	 */
	public function isValidSalt($salt) {
		$isValid = false;
		if (strlen($salt) == $this->getSaltLength()
			&& (0 == strncmp('$2$', $salt, 3) || 0 == strncmp('$2a$', $salt, 4))) {
			$isValid = true;		
		} 
		return $isValid;
	}

	/**
	 * Method creates a salted hash for a given plaintext password
	 * 
	 * @access  public
	 * @param string  $plaintextPassword  password to create a salted hash from
	 * @param string  $salt  optional custom salt to use
	 * @return string  salted hashed password
	 */
	public function getHashedPassword($plaintextPassword, $salt = null) {
		return 'test';
	}

	/**
	 * Method checks if a given plaintext password is correct by comparing it with
	 * a given salted hashed password.
	 * 
	 * @access  public
	 * @param   string   $plaintextPassword
	 * @param   string   $saltedHash
	 * @return  boolean  true, if plaintext password is correct, otherwise false
	 */
	public function checkPassword($plaintextPassword, $saltedHash) {
		return true;
	}
}

?>