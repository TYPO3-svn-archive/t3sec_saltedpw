<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Ingo Renner <ingo@typo3.org>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_blowfish.php');


/**
 * Testcase for class tx_saltedpasswords_salts_blowfish
 *
 * @author  Marcus Krause <marcus#exp2009@t3sec.info>
 * @package  TYPO3
 * @subpackage  tx_saltedpasswords
 */
class tx_saltedpasswords_salts_blowfish_testcase extends tx_phpunit_testcase {


	/**
	 * Keeps instance of object to test.
	 * 
	 * @var tx_saltedpasswords_salts_blowfish
	 */
	protected $objectInstance = null;


	public function __construct() {
		$this->objectInstance = t3lib_div::makeInstance('tx_saltedpasswords_salts_blowfish');
	}
	
	/**
	 * @test
	 */
	public function hasCorrectBaseClass() {
		
		$hasCorrectBaseClass = (0 === strcmp('tx_saltedpasswords_salts_blowfish', get_class($this->objectInstance))) ? true : false;
		
			// XCLASS ?
		if (!$hasCorrectBaseClass && false != get_parent_class($this->objectInstance)) {
			$hasCorrectBaseClass = is_subclass_of($this->objectInstance, 'tx_saltedpasswords_salts_blowfish');
		}

		$this->assertTrue($hasCorrectBaseClass);
	}

	/**
	 * @test
	 */
	public function nonZeroSaltLength() {
		$this->assertTrue($this->objectInstance->getSaltLength() > 0);
	}

	/**
	 * @test
	 */
	public function createdSaltedHashOfProperStructure() {
		$plaintextPassword = 'password';
		$saltedHash = $this->objectInstance->getSaltedHashedPassword($plaintextPassword);
		$salt = substr($saltedHash, 0, $this->objectInstance->getSaltLength());
		$this->assertTrue($this->objectInstance->isValidSalt($salt));
	}

	/**
	 * @test
	 */
	public function authenticationWithValidPassword() {
		$plaintextPassword = 'password';
		$saltedHash = $this->objectInstance->getSaltedHashedPassword($plaintextPassword);
		$this->assertTrue($this->objectInstance->isCorrectPassword($password, $saltedHash), 'Blowfish might not be supported on your platform.');
	}

	/**
	 * @test
	 */
	public function authenticationWithNonValidPassword() {
		$plaintextPassword = 'password';
		$plaintextPassword1 = $plaintextPassword . 'INVALID';
		$saltedHash = $this->objectInstance->getSaltedHashedPassword($plaintextPassword);
		$this->assertFalse($this->objectInstance->isCorrectPassword($plaintextPassword1, $saltedHash));
	}
}
?>
}