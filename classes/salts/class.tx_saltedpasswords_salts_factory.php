<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Marcus Krause <marcus#exp2009@t3sec.info>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Contains class "tx_saltedpasswords_salts_factory" 
 * that provides a salted hashing method factory.
 * 
 * $Id$
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_abstract_salts.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_md5.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_blowfish.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_phpass.php');
require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');


/**
 * Class that implements Blowfish salted hashing based on PHP's
 * crypt() function.
 * 
 * @author      Marcus Krause <marcus#exp2009@t3sec.info>
 * 
 * @since   	2009-09-06
 * @package     TYPO3
 * @subpackage  tx_saltedpasswords
 */
class tx_saltedpasswords_salts_factory {


	/**
	 * Keeps a comma-separated list of class names
	 * whose objects implement different salted hashing
	 * methods. 
	 * 
	 * @var string
	 */
	static protected $defaultMethods = 'tx_saltedpasswords_salts_md5,tx_saltedpasswords_salts_blowfish,tx_saltedpasswords_salts_phpass';
	
	/**
	 * An instance of the salted hashing method.
	 * This member is set in the getSaltingInstance() function.
	 * 
	 * @var tx_saltedpasswords_abstract_salts
	 */
	static protected $instance = null;


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
		if (!is_object(self::$instance) || !is_null($saltedHash)) {
			
				// non existing instance and no salted hash to check
				// -> use default method
			if (is_null($saltedHash)) {
				$classNameToUse = tx_saltedpasswords_div::getDefaultSaltingHashingMethod();
				self::$instance = t3lib_div::makeInstance($classNameToUse);
			}  // salted hash to check 
			else {
				$result = self::determineSaltingHashingMethod($saltedHash);
				if(!$result) {
					self::$instance = null;
				}
			}
		}
		return self::$instance;
	}

	/**
	 * Method tries to determine the salting hashing method used for given salt.
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
			$methodFound = $objectInstance->isValidSaltedPW($saltedHash);
			if ($methodFound) {
				self::$instance = &$objectInstance;
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
		self::$instance = null;
		$objectInstance = t3lib_div::getUserObj($resource);
		if (is_object($objectInstance)
			&& is_subclass_of($objectInstance, 'tx_saltedpasswords_abstract_salts')) {
				self::$instance = &$objectInstance;
		}
		return self::$instance;
	}
}
?>