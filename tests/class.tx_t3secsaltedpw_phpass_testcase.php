<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2009@t3sec.info)
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
 * General helper methods library.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2009@t3sec.info>
 */

require_once t3lib_extMgm::extPath('phpunit', 'class.tx_phpunit_testcase.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/lib/class.tx_t3secsaltedpw_phpass.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');

/**
 * Class implementing unit test(s) for extension t3sec_saltedpw, in detail
 * covering implementation of the Portable PHP password hashing framework.
 *
 * Intended to be used with unit test extension phpunit.
 *
 * @author      Marcus Krause <marcus#exp2009@t3sec.info>
 *
 * @since       2008-11-15
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_phpass_testcase extends tx_phpunit_testcase {


	public $objPHPass;

	public $testPasswd = 'abcdefghijklmnopg';


	public function __construct() {
		$this->objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
	}

	public function testUpdateNecessityMD5() {
		$passwd = tx_t3secsaltedpw_div::generatePassword(1);
		$this->assertTrue($this->objPHPass->isHashUpdateNeeded($passwd));
	}

	public function testUpdateNecessityClearTextSmall() {
		$this->assertTrue($this->objPHPass->isHashUpdateNeeded($this->testPasswd));
	}

	public function testUpdateNecessityClearTextMiddle() {
		$testPasswd = substr($this->testPasswd . $this->testPasswd, 0, 32);
		$this->assertTrue($this->objPHPass->isHashUpdateNeeded($testPasswd));
	}

	public function testUpdateNecessityClearTextLong() {
		$this->assertTrue($this->objPHPass->isHashUpdateNeeded($this->testPasswd.$this->testPasswd));
	}

	public function testUpdateNecessity() {
		$saltedHash = $this->objPHPass->getHashedPassword($this->testPasswd);
		$this->assertFalse($this->objPHPass->isHashUpdateNeeded($saltedHash));
	}

	public function testUpdateNecessityDegraded() {
		$saltedHash = $this->objPHPass->getHashedPassword($this->testPasswd, 11);
		$this->assertTrue($this->objPHPass->isHashUpdateNeeded($saltedHash));
	}

	public function testDifferentPHPHashes() {
		$saltedHash1 = $this->objPHPass->getHashedPassword($this->testPasswd);
		$saltedHash2 = $this->objPHPass->getHashedPassword($this->testPasswd);
		$this->assertNotEquals($saltedHash1, $saltedHash2);
	}

	public function testAuthentication() {
		$refHash = $this->objPHPass->getHashedPassword($this->testPasswd);
		$this->assertTrue($this->objPHPass->checkPassword($this->testPasswd, $refHash));
	}

	public function testAuthenticationAfterUpdatedMD5() {
		$md5Hash = md5($this->testPasswd);
		$updateHash = 'M' . $this->objPHPass->getHashedPassword($md5Hash, 11);
		$this->assertFalse($this->objPHPass->checkPassword($this->testPasswd, $updateHash));
	}

	public function testPasswordLength() {
		$len = 32;
		$pass = tx_t3secsaltedpw_div::generatePassword($len);
		$this->assertEquals(strlen($pass), $len);
	}

	public function testPasswordCharPool() {
		$len = 32;
		$pass = tx_t3secsaltedpw_div::generatePassword($len);
		$passPool = tx_t3secsaltedpw_div::getPasswordChars();
		$found = true;
		while ($len-- > 0) {
			$pos = stripos($passPool,$pass{$len});
			if ($pos === false) $found = false;
		}
		$this->assertTrue($found);
	}
}
?>