<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_md5.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_blowfish.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');

class tx_saltedpasswords_salts_factory {


	static protected $defaultMethods = 'tx_saltedpasswords_salts_md5,tx_saltedpasswords_salts_blowfish';

	
	/**
	 * An instance of the salting hashing method.
	 * This member is set in the getSaltingInstance() function.
	 * 
	 * @var tx_saltedpasswords_abstract_salts
	 */
	static protected $saltingHashingMethodInstance = null;

	/**
	 * Obtains a salting hashing method instance.
	 * 
	 * This function will return an instance of a class that implements
	 * tx_saltedpasswords_abstract_salts.
	 * 
	 * @param   string  (optional) salted hashed password to determine the type of used method from
	 * @return  tx_saltedpasswords_abstract_salts  an instance of salting hashing method object
	 */
	static public function getSaltingInstance($saltedHash = null) {
		if (is_null(self::$saltingHashingMethodInstance) || !is_null($saltedHash)) {
			
				// non existing instance and no salted hash to check
				// -> use default method
			if (is_null($saltedHash)) {
				$classNameToUse = tx_saltedpasswords_div::getDefaultSaltingHashingMethod();
				self::$saltingHashingMethodInstance = t3lib_div::makeInstance($classNameToUse);
			}  // salted hash to check 
			else {
				$result = self::determineSaltingHashingMethod($saltedHash);
				if(!$result) {
					self::$saltingHashingMethodInstance = null;
				}
			}
		}
		return self::$saltingHashingMethodInstance;
	}

	/**
	 * Method tries to determine the salting hashing method used for giving salt.
	 * 
	 * Method implicitly sets the instance of the found method object in the class property when found.
	 * 
	 * @access  protected
	 * @param   string    $saltedHash
	 * @return  boolean   true, if salting hashing method has been found, otherwise false
	 */
	static protected function determineSaltingHashingMethod($saltedHash) {
		$methodFound = false;
		$classNameToUse = '';
		foreach(explode(',', self::$defaultMethods) as $method) {
			$objectInstance = t3lib_div::makeInstance($method);
			$methodFound = $objectInstance->isValidSalt($saltedHash);
			if ($methodFound) {
				self::$saltingHashingMethodInstance = &$objectInstance;
				break;
			}
		}
		return $methodFound;
	}

	/**
	 * Method sets a custom salting hashing method class.
	 * 
	 * @access  public
	 * @param   string  $resource  object resource to use (e.g. 'EXT:saltedpasswords/classes/salts/class.tx_saltedpasswords_salts_blowfish.php:tx_saltedpasswords_salts_blowfish')
	 * @return  tx_saltedpasswords_abstract_salts  an instance of salting hashing method object
	 */
	static public function setPreferredHashingMethod($resource) {
		self::$saltingHashingMethodInstance = null;
		$objectInstance = self::$saltingHashingMethodInstance = t3lib_div::getUserObj($resource);
		if (!is_null($objectInstance)
			&& is_subclass_of($objectInstance, 'tx_saltedpasswords_abstract_salts')) {
				self::$saltingHashingMethodInstance = &$objectInstance;
		}
		return self::$saltingHashingMethodInstance;
	}
}
?>