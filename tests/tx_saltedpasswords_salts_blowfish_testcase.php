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
/**
 * Contains class "tx_saltedpasswords_salts_blowfish" 
 * that provides Blowfish salted hashing.
 * 
 * $Id$
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_blowfish.php');


/**
 * Testcases for class tx_saltedpasswords_salts_blowfish
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


	/**
	 * Class constructor.
	 *
	 * @access  public
	 */
	public function __construct() {
		$this->objectInstance = t3lib_div::makeInstance('tx_saltedpasswords_salts_blowfish');
	}

	/**
	 * Prepares a message to be shown when a salted hashing is not supported.
	 * 
	 * @access  protected
	 * @return  string     empty string if salted hashing method is available, otherwise an according warning
	 */
	protected function getWarningWhenMethodUnavailable() {
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
		$this->assertNotNull($this->objectInstance->getHashedPassword($password), $this->getWarningWhenMethodUnavailable());
	}

	/**
	 * @test
	 */
	public function createdSaltedHashOfProperStructure() {
		$password = 'password';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertTrue($this->objectInstance->isValidSalt($saltedHashPW), $this->getWarningWhenMethodUnavailable());
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertTrue($this->objectInstance->isValidSalt($saltedHashPW), $this->getWarningWhenMethodUnavailable());
	}

	/**
	 * @test
	 */
	public function authenticationWithValidPassword() {
		$password = 'password';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertTrue($this->objectInstance->checkPassword($password, $saltedHashPW), $this->getWarningWhenMethodUnavailable());
	}

	/**
	 * @test
	 */
	public function authenticationWithNonValidPassword() {
		$password = 'password';
		$password1 = $password . 'INVALID';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertFalse($this->objectInstance->checkPassword($password1, $saltedHashPW), $this->getWarningWhenMethodUnavailable());
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
		$this->assertTrue(($criticalPwLength == 0) || ($criticalPwLength > 32), $this->getWarningWhenMethodUnavailable() . 'Duplicates of hashed passwords with plaintext password of length ' . $criticalPwLength . '+.');
	}
	
	/**
	 * @test
	 */
	public function modifiedMinHashCount() {
		$minHashCount = $this->objectInstance->getMinHashCount();
		$this->objectInstance->setMinHashCount($minHashCount - 1);
		$this->assertTrue($this->objectInstance->getMinHashCount() < $minHashCount);
		$this->objectInstance->setMinHashCount($minHashCount + 1);
		$this->assertTrue($this->objectInstance->getMinHashCount() > $minHashCount);
	}

	/**
	 * @test
	 */
	public function modifiedMaxHashCount() {
		$maxHashCount = $this->objectInstance->getMaxHashCount();
		$this->objectInstance->setMaxHashCount($maxHashCount + 1);
		$this->assertTrue($this->objectInstance->getMaxHashCount() > $maxHashCount);
		$this->objectInstance->setMaxHashCount($maxHashCount - 1);
		$this->assertTrue($this->objectInstance->getMaxHashCount() < $maxHashCount);
	}

	/**
	 * @test
	 */
	public function modifiedHashCount() {
		$hashCount = $this->objectInstance->getHashCount();
		$this->objectInstance->setMaxHashCount($hashCount + 1);
		$this->objectInstance->setHashCount($hashCount + 1);
		$this->assertTrue($this->objectInstance->getHashCount() > $hashCount);
		$this->objectInstance->setMinHashCount($hashCount - 1);
		$this->objectInstance->setHashCount($hashCount - 1);
		$this->assertTrue($this->objectInstance->getHashCount() < $hashCount);
	}

	/**
	 * @test
	 */
	public function updateNecessityForValidSaltedPassword() {
		$password = 'password';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$this->assertFalse($this->objectInstance->isHashUpdateNeeded($saltedHashPW), $this->getWarningWhenMethodUnavailable());
	}

	/**
	 * @test
	 */
	public function updateNecessityForIncreasedHashcount() {
		$password = 'password';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$increasedHashCount = $this->objectInstance->getHashCount() + 1;
		$this->objectInstance->setMaxHashCount($increasedHashCount);
		$this->objectInstance->setHashCount($increasedHashCount);
		$this->assertTrue($this->objectInstance->isHashUpdateNeeded($saltedHashPW), $this->getWarningWhenMethodUnavailable());
	}

	/**
	 * @test
	 */
	public function updateNecessityForDecreasedHashcount() {
		$password = 'password';
		$saltedHashPW = $this->objectInstance->getHashedPassword($password);
		$decreasedHashCount = $this->objectInstance->getHashCount() - 1;
		$this->objectInstance->setMinHashCount($decreasedHashCount);
		$this->objectInstance->setHashCount($decreasedHashCount);
		$this->assertFalse($this->objectInstance->isHashUpdateNeeded($saltedHashPW), $this->getWarningWhenMethodUnavailable());
	}
}
?>