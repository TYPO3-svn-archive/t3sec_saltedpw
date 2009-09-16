<?php
/***************************************************************
*  Copyright notice
*
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


/**
 * class providing configuration checks for saltedpasswords.
 *
 * @author		Steffen Ritter <info@rs-websystems.de>
 *
 * @since       2009-09-04
 * @package     TYPO3
 * @subpackage  tx_saltedpasswords
 */
class tx_saltedpasswords_emconfhelper  {
	
	protected $errorType = t3lib_FlashMessage::OK;
	
	protected $header;
	
	protected $preText;	
	
	/**
	 * Function which is called as userFunc from extEmConf
	 *
	 */
	public function checkConfiguration(&$params,$pObj) {
		$message = '';
		$header = '';
		
			// No configuration at all by now
		if(!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['saltedpasswords']) && !is_array($_REQUEST['data'])) {
			$message = 'The Extension has not yet been configured! <br />Please be careful with the configuration of the extension since it may have impact on the security of your TYPO3 installation and the usability of the backend.';
			$header = 'Extension not configured yet...';
			$this->setErrorLevel('info');
		} else {
			$problems = array();
			
			$extConf = array_merge((array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['saltedpasswords']),(array)$_REQUEST['data']);
				// the backend is called over SSL
			$beSSL = ($_SERVER['HTTPS'] ? true : false);
				// rsaAuth is loaded
			$loadedRSAAuth = t3lib_extMgm::isLoaded('rsaauth');
				// rsaauth is active for 
			$activeRSAAuth = ($GLOBALS['TYPO3_CONF_VARS']['BE']['loginSecurityLevel'] == 'rsa' ? true : false);

				// saltedpasswords is not active in BE since missing rsaauth
			if(!$loadedRSAAuth || !$activeRSAAuth) {
				
				$tempMsg = '<b>IMPORTANT:</b><br/>saltedpasswords is not activated for backend users, even if saltedpasswords is installed.<br>To make use of saltedpasswords in the backend, make sure you follow these steps:<br />';
				if (!$loadedRSAAuth) {
					$tempMsg .= '&nbsp;-&nbsp;Use the Extension-Manager to install the sysext &quot;rsaauth&quot;.<br />';
				}
				if (!$activeRSAAuth) {
					$tempMsg .= '&nbsp;-&nbsp;Use the Install-Tool to set the Security-Level for the Backend to "rsa" ([$TYPO3_CONF_VARS][BE][loginSecurityLevel]).<br />';
				}
				$tempMsg .= 'After following these instructions, saltedpasswords will automatically be activated for the backend.';
				$problems[] = $tempMsg;
				$this->setErrorLevel('warning');
			}
				// forceSalted is set
			if($extConf['forceSalted']) {
				$this->setErrorLevel('warning');
				$problems[] = 'You set forceSalted to 1.<br>This means that only passwords in the format of this extension will succeed for login.<br /><b><i>The result will be, that there is no possibility to create new Backend Users via the Install Tool!</i></b>';
			}

				// updatePasswd
			if($extConf['updatePasswd']) {
					// updatePasswd will not work with "forceSalted"
				if($extConf['forceSalted']) {
					$this->setErrorLevel('error');
					$problems[] = 'The Extension is misconfigured and will not work as expected. The reason is that you set "updatePasswd" and "forceSalted". Using "forceSalted" prevents any other password formats from being recognized. Therefore old passwords cannot be converted.';
					
					// inform the user how passwords are updated and that there is a convert script for fe_users
				} else {
					$this->setErrorLevel('info');
					$problems[] = 'You have enabled the option "updatePasswd". The passwords will be converted to the chosen salted hashing method during authentication if necessary. For FE-Users there is a cli-script available, converting ALL FE user passwords immediately.';
				}
			}
			
				// only saltedpasswords as authsservice
			if($extConf['onlyAuthService']) {
					// warn user that the combination of "onlyAuthService" and "forceSalted" may lock him out of the Backend
				if($extConf['forceSalted']) {
					$this->setErrorLevel('warning');
					$problems[] = 'You configured saltedpasswords to be the only authentication service AND to force salted-passwords. The result is that users not having a salted password hash will not be able to login. Are you sure you want to keep these settings? This might lock you out of the backend!';
					//inform the user that things like openid won't work anymore
				} else {
					$this->setErrorLevel('info');
					$problems[] = 'You configured saltedpasswords to be the only authentication service. This means that other services like ipauth, openid etc will not work anymore. This does not affect "rsaauth", which will be used implicitly.';
				}
			}
			
				// MD5 not available, but configured
			if ($extConf['saltedPWHashingMethod'] == '1' && !CRYPT_MD5) {
				$this->setErrorLevel('error');
				$problems[] = 'You\' configured saltedpasswords to use MD5 encryption which is not available on your system. Please use another method!';
			}

				// Blowfish not available, but configured
			if ($extConf['saltedPWHashingMethod'] == '2' && !CRYPT_BLOWFISH) {
				$this->setErrorLevel('error');
				$problems[] = 'You configured saltedpasswords to use Blowfish encryption which is not available on your system. Please use another encryption method!';
			}
			
				// inform the user if securityLevel in FE is superchallenged or blank --> extension won't work
			if(!t3lib_div::inList('normal,rsa', $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'])) {
				$this->setErrorLevel('info');
				$problems[] = 'Salted passwords are not activated for frontend logins. Use the Install-Tool to set the Security Level for the Frontend to "rsa" or "normal" ([$TYPO3_CONF_VARS][FE][loginSecurityLevel]). Make sure, that it is not blank or superchallenged.';
			}
			
				// if there are problems, render them into an unordered list
			if(count($problems)>0) {
				$message = 'Please have a look at the following list and take care:<br />&nbsp;<ul><li>' . implode('<br />&nbsp;</li><li>',$problems) . '</li></ul><br />Note, that a wrong configuration might have impact on the security of your TYPO3 installation and the usability of the backend.';
			}
		}
		if (empty($message))  $this->setErrorLevel('ok');
		
		$message = $this->preText . $message;
		$message = t3lib_div::makeInstance('t3lib_FlashMessage', $message, $this->header, $this->errorType);
		return $message->render();
	}
	
	/**
	 * set's the error level if no higher level
	 * is set already
	 * 
	 * @param	string	$level, one of: error,ok,warning,info
	 */
	private function setErrorLevel($level = string) {
		switch($level) {
			case 'error' :
				$this->errorType = t3lib_FlashMessage::ERROR;
				$this->header = 'Errors in your configuration of saltedpasswords have been found...';
				$this->preText = 'The extension will not work correctly with this configuration.<br />';
				break;
			case 'warning':
				if ($this->errorType != t3lib_FlashMessage::ERROR) {
					$this->errorType = t3lib_FlashMessage::WARNING;
					$this->header = 'There are warnings regarding your configuration.';
					$this->preText = 'The extension might behave other than expected.<br />';
				}
				break;
			case 'info' : 
				if ($this->errorType != t3lib_FlashMessage::ERROR && $this->errorType != t3lib_FlashMessage::WARNING) {
					$this->errorType = t3lib_FlashMessage::INFO;
					$this->header = 'Additional information about your configuration is available.<br />';
					$this->preText = '';
				}
				break;
			case 'ok' :
				if ($this->errorType != t3lib_FlashMessage::ERROR && $this->errorType != t3lib_FlashMessage::WARNING && $this->errorType != t3lib_FlashMessage::INFO) {
					$this->errorType = t3lib_FlashMessage::OK;
					$this->header = 'Everything is fine.';
					$this->preText = 'As far as automatic checks can do, your salted passwords seem to be configured correctly and should work as expected.<br />';
				}
				break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_emconfhelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_emconfhelper.php']);
}
?>