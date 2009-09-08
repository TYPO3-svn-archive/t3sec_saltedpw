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
 * Contains interface "tx_saltedpasswords_salts" to be used in
 * classes that provide salted hashing.
 * 
 * $Id$
 */


/**
 * Interface with public methods needed to be implemented
 * in a salting hashing class.
 * 
 * @author      Marcus Krause <marcus#exp2009@t3sec.info>
 * 
 * @since   	2009-09-06
 * @package     TYPO3
 * @subpackage  tx_saltedpasswords
 */
interface tx_saltedpasswords_salts {


	/**
	 * Method checks if a given plaintext password is correct by comparing it with
	 * a given salted hashed password.
	 * 
	 * @access  public
	 * @param   string   $plainPW:  plain-text password to compare with salted hash
	 * @param   string   $saltedHashPW:  salted hash to compare plain-text password with
	 * @return  boolean  true, if plaintext password is correct, otherwise false
	 */
	public function checkPassword($plainPW, $saltedHashPW);

	/**
	 * Returns length of required salt.
	 * 
	 * @access  public
	 * @return  integer  length of required salt 
	 */
	public function getSaltLength();

	/**
	 * Method creates a salted hash for a given plaintext password
	 * 
	 * @access  public
	 * @param   string  $password:  plaintext password to create a salted hash from
	 * @param   string  $salt:  optional custom salt to use
	 * @return  string  salted hashed password
	 */
	public function getHashedPassword($password, $salt = null);

	/**
	 * Method determines if a given string is a valid salt
	 * 
	 * @access  public
	 * @param   string   $salt: string to check
	 * @return  boolean  true if it's valid salt, otherwise false
	 */
	public function isValidSalt($salt);

	/**
	 * Method determines if a given string is a valid salted hashed password.
	 * 
	 * @access  public
	 * @param   string   $saltedPW: string to check
	 * @return  boolean  true if it's valid salted hashed password, otherwise false
	 */
	public function isValidSaltedPW($saltedPW);
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/salts/interfaces/interface.tx_saltedpasswords_salts.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/salts/interfaces/interface.tx_saltedpasswords_salts.php']);
}
?>