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

	protected function getWarningWhenBlowfishUnavailable() {
		$warningMsg = '';
		if (!defined(CRYPT_BLOWFISH) || !CRYPT_BLOWFISH) {
			$warningMsg .= 'Blowfish is not supported on your platform. '
						.  'Then, some of the blowfish tests will fail.';
		}
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
	public function emptyPasswordResultsInNullSaltedPassword() {
		$password = '';
		$this->assertNull($this->objectInstance->getHashedPassword($password));
	}

	/**
	 * @test
	 */
	public function nonEmptyPasswordResultsInNonNullSaltedPassword() {
		$password = 'a';
		$this->assertNotNull($this->objectInstance->getHashedPassword($password), $this->getWarningWhenBlowfishUnavailable());
	}

	/**
	 * @test
	 */
	public function createdSaltedHashOfProperStructure() {
		$password = 'password';
		$saltedHash = $this->objectInstance->getHashedPassword($password);
		$this->assertTrue($this->objectInstance->isValidSalt($saltedHash), $this->getWarningWhenBlowfishUnavailable());
	}

	/**
	 * @test
	 */
	public function authenticationWithValidPassword() {
		$password = 'password';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertTrue($this->objectInstance->checkPassword($password, $saltedHashPW), $this->getWarningWhenBlowfishUnavailable());
	}

	/**
	 * @test
	 */
	public function authenticationWithNonValidPassword() {
		$password = 'password';
		$password1 = $password . 'INVALID';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertFalse($this->objectInstance->checkPassword($password1, $saltedHashPW), $this->getWarningWhenBlowfishUnavailable());
	}

	/**
	 * @test
	 */
	public function passwordVariationsResultInDifferentHashes() {
		$pad = 'a';
		$password = '';
		$criticalPwLength = 0;
			// We're using a constant salt.
		$saltedHashPWPrevious = $saltedHashPWCurrent = $salt = $this->objectInstance->getHashedPassword($pad);
		
		for ($i = 0; $i <= 128; $i += 8) {
			$password = str_repeat($pad, max($i, 1));
			$saltedHashPWPrevious = $saltedHashPWCurrent; 
			$saltedHashPWCurrent = $this->objectInstance->getHashedPassword($password, $salt);
			if ($i > 0 && 0 == strcmp($saltedHashPWPrevious, $saltedHashPWCurrent)) {
				$criticalPwLength = $i;
				break;
			}
		}
		$this->assertTrue(($criticalPwLength == 0) || ($criticalPwLength > 32), $this->getWarningWhenBlowfishUnavailable() . 'Duplicates of hashed passwords with plaintext password of length ' . $criticalPwLength . '+.');
	}
}
?>
}