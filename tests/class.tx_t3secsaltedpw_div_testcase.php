<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2008@t3sec.info)
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
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_testcase.php');
require_once (t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php');

/**
 * Class implementing unit test(s) for extension t3sec_saltedpw.
 *
 * Intended to be used with unit test extension phpunit.
 *
 * @author      Marcus Krause <marcus#exp2008@t3sec.info>
 *
 * @since       2008-11-15
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_div_testcase extends tx_phpunit_testcase {


	/**
	 * Method tests if a password will reproducible result
	 * into the same string when getting hashed (MD5) using salt.
	 *
	 * @author  Marcus Krause <marcus#exp2008@t3sec.info>
	 * @access  public
	 */
	public function testEqualMD5SaltStrings() {

		$passwd = $this->generatePassword(8);
		$refSaltString = tx_t3secsaltedpw_div::saltMD5($passwd);

		$saltedPasswd = tx_t3secsaltedpw_div::saltMD5(  $passwd,
														tx_t3secsaltedpw_div::getSaltByPasswdString($refSaltString));

		$this->assertEquals(    $refSaltString,
								$saltedPasswd,
								'Testing salting algorithm.' );
	}

	/**
	 * Is used by forgot password - function with md5 option.
	 *
	 * @author  Bernhard Kraft
	 * @access  protected
	 * @param   int             length of new password
	 * @return  string          new password
	 */
	protected function generatePassword($len) {
		$pass = '';
		while ($len--) {
			$char = rand(0,35);
			if ($char < 10) {
				$pass .= ''.$char;
			} else {
				$pass .= chr($char-10+97);
			}
		}
		return $pass;
	}
}
?>