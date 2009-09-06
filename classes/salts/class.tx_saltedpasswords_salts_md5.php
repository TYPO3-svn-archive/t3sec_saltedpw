<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/interfaces/interface.tx_saltedpasswords_salts.php');


class tx_saltedpasswords_salts_md5 extends tx_saltedpasswords_abstract_salts implements tx_saltedpasswords_salts {


	const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	

	/**
	 * Keeps length of salt in bytes.
	 * 
	 * @var integer
	 */
	static protected $saltLengthMD5 = 6;
	
	static protected $saltPrefixMD5 = '$1$';

	static protected $saltSuffixMD5 = '$';

	/**
	 * Setting string to indicate type of hashing method (md5).
	 * 
	 * @var string
	 */
	static protected $settingMD5 = '$1$';


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
	 * Generates a random base 64-encoded salt prefixed and suffixed with settings for the hash.
	 *
	 * Proper use of salts may defeat a number of attacks, including:
	 *  - The ability to try candidate passwords against multiple hashes at once.
	 *  - The ability to use pre-hashed lists of candidate passwords.
	 *  - The ability to determine whether two users have the same (or different)
	 *    password without actually having to guess one of the passwords.
	 *
	 * @access  protected
	 * @return  string     a 12 character string containing settings and a random salt
	 */
	protected function getGeneratedSalt() {
		$randomBytes = t3lib_div::generateRandomBytes($this->getSaltLength());
		$output = $this->base64Encode($randomBytes, $this->getSaltLength());
		return $this->applySettingsToSalt($output);
	}
	
	protected function applySettingsToSalt($salt) {
		$saltWithSettings = $this->getSetting() 
							. $salt
							. $this->getSaltSuffix();
		return $saltWithSettings;
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
	 * @deprecated
	 * @return  string  prefix of salt
	 */
	protected function getSaltPrefix() {
		return self::$saltPrefixMD5;
	}

	/**
	 * Returns setting string of blowfish hashing method.
	 * 
	 * @access  protected
	 * @return  string     setting string of md5 hashing method.
	 */
	public function getSetting() {
		return self::$settingMD5;
	}

	/**
	 * Returns suffic of salt indicating the type of used method.
	 * 
	 * @access  public
	 * @return  string  suffix of salt
	 */
	protected function getSaltSuffix() {
		return self::$saltSuffixMD5;
	}

	/**
	 * Method determines if a given string is a valid salt
	 * 
	 * @param  string  $salt  string to check
	 * @return boolean        true if it's valid salt, otherwise false
	 */
	public function isValidSalt($salt) {
		$isValid = $skip = false;
			
			// Bytes in bits in base64
		$reqLenBase64 = intval(ceil(($this->getSaltLength() * 8) / 6));

		if (strlen($salt) >= $reqLenBase64) {
						// salt with prefixed setting
			if (0 == strncmp('$', $salt, 1)) {
				if (0 == strncmp($this->getSetting(), $salt, strlen($this->getSetting()))) {
					$isValid = true;
					$salt = substr($salt, strlen($this->getSetting()));
				} else {
					$skip = true;
				}
			}
				
				// checking base64 characters
			if (!$skip && (strlen($salt) >= $reqLenBase64)) {
				if (preg_match('/^[' . preg_quote($this->getItoa64(),'/') . ']{' . $reqLenBase64 . ',' . $reqLenBase64 . '}$/', substr($salt, 0, $reqLenBase64))) {
					$isValid = true;
				}
			}
		}
		return $isValid;
	}

	/**
	 * Method creates a salted hash for a given plaintext password
	 * 
	 * @access  public
	 * @param   string  $password:  plaintext password to create a salted hash from
	 * @param   string  $salt:  optional custom salt to use
	 * @return  string  salted hashed password
	 */
	public function getHashedPassword($password, $salt = null) {
		$saltedPW = null;
		if (!empty($password)) {
			if (empty($salt) || !$this->isValidSalt($salt)) {
				$salt = $this->getGeneratedSalt();
			}
			$saltedPW = crypt($password, $salt);
		}
		return $saltedPW;
	}

	/**
	 * Method checks if a given plaintext password is correct by comparing it with
	 * a given salted hashed password.
	 * 
	 * @access  public
	 * @param   string   $plainPW: plain-text password to compare with salted hash
	 * @param   string   $saltedHashPW: salted hash to compare plain-text password with
	 * @return  boolean  true if plain-text password matches the salted
	 *                   hash, otherwise false
	 */
	public function checkPassword($plainPW, $saltedHashPW) {
		$isCorrect = false;
		if ($this->isValidSalt($saltedHashPW)) {
			$isCorrect = (crypt($plainPW,$saltedHashPW) == $saltedHashPW);
		} else {
			// TODO throw Exception catch to find the proper hashing method
		}
		return $isCorrect;
	}
}
?>