<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/interfaces/interface.tx_saltedpasswords_salts.php');


class tx_saltedpasswords_salts_md5 extends tx_saltedpasswords_abstract_salts implements tx_saltedpasswords_salts {


	const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	
	
	static protected $saltLengthMD5 = 12;
	
	static protected $saltPrefixMD5 = '$1$';

	static protected $saltSuffix = '$';


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
	 * Returns pool of characters to be used in a salt.
	 * 
	 * @access  protected
	 * @return  string     pool of salt characters
	 */
	protected function getSaltCharPool() {
		return self::$saltChars;
		
	}

	/**
	 * Method creates a salt.
	 *
	 * @access  protected
	 * @return  string  generated salt
	 */
	protected function generateSalt() {
		return $this->getGeneratedSalt();
	}

	/**
	 * Generates a random base 64-encoded salt prefixed with settings for the hash.
	 *
	 * Proper use of salts may defeat a number of attacks, including:
	 *  - The ability to try candidate passwords against multiple hashes at once.
	 *  - The ability to use pre-hashed lists of candidate passwords.
	 *  - The ability to determine whether two users have the same (or different)
	 *    password without actually having to guess one of the passwords.
	 *
	 * @access  protected
	 * @return  string                 a 12 character string containing the iteration count and
	 *                                 a random salt.
	 */
	protected function getGeneratedSalt() {
		$output = $this->getSaltPrefix();

		$randomBytes = t3lib_div::generateRandomBytes($this->getSaltLength());
		$output .= substr($this->base64Encode($randomBytes, $this->getSaltLength()), 0, $this->getSaltLength() - strlen($this->getSaltPrefix()) - strlen($this->getSaltSuffix()));
		$output .= $this->getSaltSuffix();
		return $output;
	}

	/**
	 * Returns length of required salt.
	 * 
	 * @access  public
	 * @return  integer  length of required salt
	 */
	public function getSaltLength() {
		return self::$saltLengthMD5;
	}

	/**
	 * Returns prefix of salt indicating the type of used method.
	 * 
	 * @access  public
	 * @return  string  prefix of salt
	 */
	protected function getSaltPrefix() {
		return self::$saltPrefixMD5;
	}

	/**
	 * Returns suffic of salt indicating the type of used method.
	 * 
	 * @access  public
	 * @return  string  suffix of salt
	 */
	protected function getSaltSuffix() {
		return self::$saltSuffix;
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
			&& (0 == strncmp('$1$', $salt, 3))
			&& (($this->getSaltLength() - 1) == strrpos($salt, '$'))) {
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
	public function getSaltedHashedPassword($plaintextPassword, $salt = null) {
		if (empty($salt) || !$this->isValidSalt()) {
			$salt = $this->generateSalt();
		}
		return crypt($value, $salt);
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
	public function isCorrectPassword($plaintextPassword, $saltedHash) {
		$isCorrect = false;
		if ($this->isValidSalt(substr($saltedHash, 0, $this->getSaltLength()))) {
			$isCorrect = (crypt($plaintextPassword,$saltedHash) == $saltedHash);
		} else {
			// TODO throw Exception catch to find the proper hashing method
		}
		return $isCorrect;
	}
}
?>