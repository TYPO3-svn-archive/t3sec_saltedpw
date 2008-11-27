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
 * Implementation of authentication service.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php';
require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';

/**
 * Class implements Portable PHP Hashing Framework authentication service.
 *
 * @author  	Marcus Krause <marcus#exp2008@t3sec.info>
 *
 * @since   	2008-11-14
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_sv1 extends tx_sv_authbase {

	public $prefixId =      'tx_t3secsaltedpw_sv1';
	public $scriptRelPath = 'sv1/class.tx_t3secsaltedpw_sv1.php';
	public $extKey =        't3sec_saltedpw';


	/**
	 * Method adds a further authUser method.
	 *
	 * @access  public
	 * @param   array     Array containing FE user data of the logged user.
	 * @return  mixed     boolean false - false - this service was the right one to authenticate the user but it failed
	 * 					  integer 100 - just go on. User is not authenticated but there is still no reason to stop
	 *                    integer 200 - the service was able to authenticate the user
	 */
	public function authUser($user)	{
		$OK = 100;
		$login = $GLOBALS['TSFE']->fe_user->getLoginFormData();
		$extConf = tx_t3secsaltedpw_div::returnExtConf();
		$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');

		$validPasswd = false;

			// we process only valid passwords inserted by Portable PHP password hashing framework
		if (0 == strncmp($user['password'], '$P$', 3)) {
			$validPasswd = $objPHPass->checkPassword($login['uident'], $user['password']);
				// test if password needs hash update due to change of hash count value
			if ($objPHPass->isHashUpdateNeeded($user['password'])) {
					$this->updatePassword(intval($user['uid']));
			}
		} 	// we process also clear-text, md5 and passwords updated by Portable PHP password hashing framework
		else if (1 != intval($extConf['forcePHPasswd'])) {
			if (0 == strncmp($user['password'], 'M$P$', 4)) {
				$validPasswd = $objPHPass->checkPassword(md5($login['uident']), substr($user['password'], 1));
					// test if password needs to be updated
				if ($validPasswd && 1 == intval($extConf['updatePasswd'])) {
					$this->updatePassword(intval($user['uid']), array( 'password' => $objPHPass->getHashedPassword($login['uident'])));
				}
			} else if (0 == strncmp($user['password'], 'C$P$', 4)) {
				$validPasswd = $objPHPass->checkPassword($login['uident'], substr($user['password'], 1));
					// test if password needs to be updated
				if ($validPasswd && 1 == intval($extConf['updatePasswd'])) {
					$this->updatePassword(intval($user['uid']), array( 'password' => $objPHPass->getHashedPassword($login['uident'])));
				}
			} else if (preg_match('/[0-9abcdef]{32,32}/', $user['password'])) {
				$validPasswd = (0 == strcmp(md5($login['uident']), $user['password']) ? true : false);
					// test if password needs to be updated
				if ($validPasswd && 1 == intval($extConf['updatePasswd'])) {
					$this->updatePassword(intval($user['uid']), array( 'password' => $objPHPass->getHashedPassword($login['uident'])));
				}
			} else {
				$validPasswd = (0 == strcmp(md5($login['uident']), $user['password']) ? true : false);
					// test if password needs to be updated
				if ($validPasswd && 1 == intval($extConf['updatePasswd'])) {
					$this->updatePassword(intval($user['uid']), array( 'password' => $objPHPass->getHashedPassword($login['uident'])));
				}
			}
		}

		if ($validPasswd) {
			$OK = 200;
			t3lib_div::devLog('Authentication successful for user with uid ' . $user['uid'], $this->extKey, 0);
		} else if (!$validPasswd && 1 == intval($extConf['onlyAuthService'])) {
			$OK = false;
			t3lib_div::devLog('Authentication failed - "' . $cmp . '" is the wrong password for user with uid ' . $user['uid'], $this->extKey, 2);
		} else {
			t3lib_div::devLog('Authentication failed - "' . $cmp . '" is the wrong password for user with uid - skipping this service' . $user['uid'], $this->extKey, 1);
		}
		return $OK;
	}

	/**
	 * Method updates a fe user record - in this case a new password string will be set.
	 *
	 * @access  protected
	 * @param   integer    $uid           uid of user record that will be updated
	 * @param   mixed      $updateFields  Field values as key=>value pairs to be updated in database
	 */
	protected function updatePassword($uid, $updateFields) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery( 'fe_users', 'uid = ' . $uid, $updateFields);
		t3lib_div::devLog('Automatic password update for user with uid ' . $user['uid'], $this->extKey, 1);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/sv1/class.tx_t3secsaltedpw_sv1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/sv1/class.tx_t3secsaltedpw_sv1.php']);
}
?>