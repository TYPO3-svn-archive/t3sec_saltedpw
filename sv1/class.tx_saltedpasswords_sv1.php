<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2009@t3sec.info)
*  (c) Steffen Ritter (info@rs-websystems.de)
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
	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/class.tx_saltedpasswords_div.php');

/**
 * Class implements salted-password hashes authentication service.
 *
 * @author  	Marcus Krause <marcus#exp2009@t3sec.info>
 * @author		Steffen Ritter <info@rs-websystems.de> 
 *
 * @since   	2009-06-14
 * @package     TYPO3
 * @subpackage  tx_saltedpasswords
 */
class tx_saltedpasswords_sv1 extends tx_sv_authbase {


	/**
	 * Keeps class name.
	 *
	 * @var string
	 */
	public $prefixId =      'tx_saltedpasswords_sv1';

	/**
	 * Keeps path to this script relative to the extension directory.
	 *
	 * @var string
	 */
	public $scriptRelPath = 'sv1/class.tx_saltedpasswords_sv1.php';

	/**
	 * Keeps extension key.
	 *
	 * @var string
	 */
	public $extKey =        'saltedpasswords';

	/**
	 * Keeps extension configuration.
	 *
	 * @var  mixed
	 */
	protected $extConf;


	/**
	 * Checks if service is available. In case of this service we check that
	 * following prerequesties are fulfilled:
	 * - loginSecurityLevel of according TYPO3_MODE is set to normal
	 *
	 * @access  public
	 * @return	boolean		true if service is available
	 */
	public function init() {
		$available = false;

	//	if (tx_saltedpasswords_div::isUsageEnabled()) {
			$available = true;
			$this->extConf = tx_saltedpasswords_div::returnExtConf();
	//	}

		return $available ? parent::init() : false;
	}

	/**
	 * Checks the login data with the user record data for builtin login method.
	 *
	 * @param	array		user data array
	 * @param	array		login data array
	 * @param	string		login security level (optional)
	 * @return	boolean		true if login data matched
	 */
	function compareUident($user, $loginData, $security_level = 'normal') {
		$validPasswd = false;
			
			// could be merged; still here to clarify
		if (!strcmp(TYPO3_MODE, 'BE')) {
			$password = $loginData['uident_text'];
		} else if (!strcmp(TYPO3_MODE, 'FE')) {
			$password = $loginData['uident_text'];
		}	
		
			// existing record is in format of Salted Hash password 
		if (t3lib_div::inList('$1$,$2$,$2a',substr($user['password'],0,3))) {
			$validPasswd = tx_saltedpasswords_div::comparePasswordToHash($password,$user['password']);
			
		} 	// we process also clear-text, md5 and passwords updated by Portable PHP password hashing framework
		else if (!intval($this->extConf['forceSalted'])) {
		
				// stored password is in old format of t3secsaltedpw
			if ( t3lib_div::inList('M$P$,C$P$',substr($user['password'],0,4)) || strpos('$P$',$user['password']) !== false ) {
				if($this->extConf['handleOldFormat']) {	//is processing of old format enabled
					$validPasswd =  tx_saltedpasswords_div::compareOldFormatHash($password,$user['password']);
				}
				
				// password is stored as md5
			} else if (preg_match('/[0-9abcdef]{32,32}/', $user['password'])) {	
				$validPasswd = (!strcmp(md5($password), $user['password']) ? true : false);
				
				// password is stored plain or unrecognized format
			} else {
				$validPasswd = (!strcmp($password, $user['password']) ? true : false);
			}
				// should we store the new format value in DB?
			if ($validPasswd && intval($this->extConf['updatePasswd'])) {
				$this->updatePassword(intval($user['uid']), array( 'password' => tx_saltedpasswords_div::getHashedPassword($password)));
			}
		}
		return $validPasswd;
	}

	/**
	 * Method adds a further authUser method.
	 *
	 * Will return one of following authentication status codes:
	 *  - 0 - authentication failure
	 *  - 100 - just go on. User is not authenticated but there is still no reason to stop
	 *  - 200 - the service was able to authenticate the user
	 *
	 * @access  public
	 * @param   array     Array containing FE user data of the logged user.
	 * @return  integer   authentication statuscode, one of 0,100 and 200
	 */
	public function authUser($user)	{
		$OK = 100;
		$validPasswd = false;

		if ($this->login['uident'] && $this->login['uname'])	{

			if (!empty($this->login['uident_text'])) {
				$validPasswd = $this->compareUident(
									$user,
									$this->login);
			}

			if (!$validPasswd && intval($this->extConf['onlyAuthService'])) {
					// Failed login attempt (wrong password) - no delegation to further services
				$this->writeLog(TYPO3_MODE . ' Authentication failed - wrong password for username \'%s\'', $this->login['uname']);
				$OK = 0;
			} else if(!$validPasswd)     {
					// Failed login attempt (wrong password)
				$this->writeLog("Login-attempt from %s, username '%s', password not accepted!",
									$this->authInfo['REMOTE_ADDR'], $this->login['uname']);
			}  else if ($validPasswd && $user['lockToDomain'] && strcasecmp($user['lockToDomain'], $this->authInfo['HTTP_HOST']))	{
					// Lock domain didn't match, so error:
				$this->writeLog("Login-attempt from %s, username '%s', locked domain '%s' did not match '%s'!",
									$this->authInfo['REMOTE_ADDR'], $this->login['uname'], $user['lockToDomain'], $this->authInfo['HTTP_HOST']);
				$OK = 0;
			} else if ($validPasswd) {
				$this->writeLog(TYPO3_MODE . ' Authentication successful for username \'%s\'', $this->login['uname']);
				$OK = 200;
			}
		}
		return $OK;
	}

	/**
	 * Method updates a FE/BE user record - in this case a new password string will be set.
	 *
	 * @access  protected
	 * @param   integer    $uid           uid of user record that will be updated
	 * @param   mixed      $updateFields  Field values as key=>value pairs to be updated in database
	 */
	protected function updatePassword($uid, $updateFields) {
		if (!strcmp(TYPO3_MODE, 'BE')) {
				// BE
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery( 'be_users', sprintf('uid = %u', $uid), $updateFields);
		} else {
				// FE
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery( 'fe_users', sprintf('uid = %u', $uid), $updateFields);
		}
		t3lib_div::devLog(sprintf('Automatic password update for %s user with uid %u', TYPO3_MODE, $uid), $this->extKey, 1);
	}

	/**
	 * Writes log message. Destination log depends on the current system mode.
	 * For FE the function writes to the admin panel log. For BE messages are
	 * sent to the system log. If developer log is enabled, messages are also
	 * sent there.
	 *
	 * This function accepts variable number of arguments and can format
	 * parameters. The syntax is the same as for sprintf()
	 *
	 * @author  Dmitry Dulepov <dmitry@typo3.org>
	 *
	 * @param	string		$message	Message to output
	 * @return	void
	 * @see	sprintf()
	 * @see	t3lib::divLog()
	 * @see	t3lib_div::sysLog()
	 * @see	t3lib_timeTrack::setTSlogMessage()
	 */
	function writeLog($message) {
		if (func_num_args() > 1) {
			$params = func_get_args();
			array_shift($params);
			$message = vsprintf($message, $params);
		}
		if (!strcmp(TYPO3_MODE, 'BE')) {
			t3lib_div::sysLog($message, $this->extKey, 1);
		} else {
			$GLOBALS['TT']->setTSlogMessage($message);
		}
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_DLOG']) {
			t3lib_div::devLog($message, $this->extKey, 1);
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/sv1/class.tx_saltedpasswords_sv1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/sv1/class.tx_saltedpasswords_sv1.php']);
}
?>