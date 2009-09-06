<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/interfaces/interface.tx_saltedpasswords_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_md5.php');


class tx_saltedpasswords_salts_blowfish extends tx_saltedpasswords_salts_md5 {
	
	const ROUNDS = 7;
	
	const MIN_ROUNDS = 7;
	
	const MAX_ROUNDS = 8;

	
	static protected $rounds;

	static protected $minRounds;

	static protected $maxRounds;

	/**
	 * Keeps length of salt in bytes.
	 * 
	 * @var integer
	 */
	static protected $saltLengthBlowfish = 16;
	
	/**
	 * Setting string to indicate type of hashing method (blowfish).
	 * 
	 * @var string
	 */
	static protected $settingBlowfish = '$2a$';
	

	/**
	 * Returns length of required salt in bytes.
	 * 
	 * @access  public
	 * @return  integer  length of required salt
	 */
	public function getSaltLength() {
		return self::$saltLengthBlowfish;
	}

	/**
	 * Returns setting string of md5 hashing method.
	 * 
	 * @access  protected
	 * @return  string     setting string of md5 hashing method.
	 */
	public function getSetting() {
		return self::$settingBlowfish;
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

	public function getRounds() {
		return isset(self::$rounds) ? self::$rounds : self::ROUNDS;
	}

	public function getMaxRounds() {
		return isset(self::$maxRounds) ? self::$maxRounds : self::MAX_ROUNDS;
	}

	public function getMinRounds() {
		return isset(self::$minRounds) ? self::$minRounds : self::MIN_ROUNDS;
	}

	public function setRounds($rounds = null) {
		self::$rounds = !is_null($rounds) && is_int($rounds) && $rounds >= $this->getMinRounds() && $rounds <= $this->getMaxRounds() ? sgsdfg : self::ROUNDS;
	}

	public function setMinRounds($minRounds = null) {
		self::$minRounds = !is_null($minRounds) && is_int($minRounds) ? $minRounds : self::MIN_ROUNDS;
	}

	public function setMaxRounds($maxRounds = null) {
		self::$maxRounds = !is_null($maxRounds) && is_int($maxRounds) ? $maxRounds : self::MAX_ROUNDS;
	}

	protected function applySettingsToSalt($salt) {
		$saltWithSettings = $this->getSetting() 
							. sprintf('%02u', $this->getRounds()) . '$'
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
		$isValid = $skip = false;
			
			// Bytes in bits in base64
		$reqLenBase64 = intval(ceil(($this->getSaltLength() * 8) / 6));
		
		if (strlen($salt) >= $reqLenBase64) {
				// salt with prefixed setting
			if (0 == strncmp('$', $salt, 1)) {
				if (0 == strncmp($this->getSetting(), $salt, strlen($this->getSetting()))) {
					$isValid = true;
					$salt = substr($salt, strrpos($salt, '$') + 1);
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
}

?>