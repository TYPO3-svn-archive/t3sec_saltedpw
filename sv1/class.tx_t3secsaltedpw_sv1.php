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

require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';

/**
 * Class implements salted MD5 authentification service.
 *
 * @access      public
 * @author  	Marcus Krause <marcus#exp2008@t3sec.info>
 * @since   	2008-11-14
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_sv1 extends tx_sv_authbase {
	public $prefixId = 'tx_t3secsaltedpw_sv1';
	public $scriptRelPath = 'sv1/class.tx_t3secsaltedpw_sv1.php';
	public $extKey = 't3sec_saltedpw';

	/**
	 * Method adds a further authUser method.
	 *
	 * @param   array     Array containing FE user data of the logged user.
	 * @return  int       true - the service was able to authenticate the user
	 * 					  false - false - this service was the right one to authenticate the user but it failed
	 * 					  100 - just go on. User is not authenticated but there is still no reason to stop
	 */
	public function authUser($user)	{
		$OK = 100;
		$login = $GLOBALS['TSFE']->fe_user->getLoginFormData();

		if (!strncmp($user['password'], '$1$', 3)) {
			$pos = strrpos($user['password'], '$');
			$salt = substr($user['password'], 3, $pos);
			$cmp = tx_t3secsaltedpw_div::saltMD5($login['uident'], $salt);
			if ($cmp == $user['password']) {
				$OK = 200;
				t3lib_div::devLog('Password ok :)', $this->extKey, 1);
			} else {
				$OK = false;
				t3lib_div::devLog('Authentication failed - "'.$cmp.'" is the wrong password', $this->extKey, 2);
			}
		} else {
			t3lib_div::devLog('No salted password - authentication not possible', $this->extKey, 1);
		}
		return $OK;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/sv1/class.tx_t3secsaltedpw_sv1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/sv1/class.tx_t3secsaltedpw_sv1.php']);
}
?>