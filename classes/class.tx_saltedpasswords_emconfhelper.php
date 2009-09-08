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
	private $errorType = t3lib_FlashMessage::OK;
	private $header = string;
	private $preText = string;	
	
	/**
	 * Function which is called as userFunc from extEmConf
	 *
	 */
	public function checkConfiguration(&$params,$pObj) {
		$message = '';
		$header = '';
		
			// No configuration at all by now
		if(!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['saltedpasswords']) && !is_array($_REQUEST['data'])) {
			$message = 'The Extension has not been configured yet! <br />Please be careful in in configuring the extension since it may have impact on the security of your TYPO3 installation and the usability of the backend.';
			$header = 'Extension not configured yet...';
			$this->setErrorLevel('info');
		} else {
			$problems = array();
			
			$extConf = array_merge(unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['saltedpasswords']),(array)$_REQUEST['data']);
				// the backend is called over SSL
			$beSSL = ($_SERVER['HTTPS'] ? true : false);
				// rsaAuth is loaded
			$loadedRSAAuth = t3lib_extMgm::isLoaded('rsaauth');
				// rsaauth is active for 
			$activeRSAAuth = ($GLOBALS['TYPO3_CONF_VARS']['BE']['loginSecurityLevel'] == 'rsa' ? true : false);

				// saltedpasswords is not active in BE since missing rsaauth
			if(!$loadedRSAAuth || !$activeRSAAuth) {
				
				$tempMsg = '<b>IMPORTANT:</b><br/>saltedpasswords is not activatid for backend users, even if saltedpasswords ist installed.<br>To make use of saltedpasswords in the backend make sure you follow the following steps:<br />';
				if (!$loadedRSAAuth) {
					$tempMsg .= '&nbsp;-&nbsp;use the Extension-Manager to install the sysext &quot;rsaauth&quot;<br />';
				}
				if (!$activeRSAAuth) {
					$tempMsg .= '&nbsp;-&nbsp;use the Install-Tool to set the Security-Level for Backend to "rsa" (\$TYPO3_CONF_VARS][BE][loginSecurityLevel])<br />';
				}
				$tempMsg .= 'After following this step(s) saltedpasswords will automatically be activated for your backend.';
				$problems[] = $tempMsg;
				$this->setErrorLevel('warning');
			}
				// forceSalted is set
			if($extConf['forceSalted']) {
				$this->setErrorLevel('warning');
				$problems[] = 'You set forceSalted to 1.<br>This means that only passwords in the format of this extensions will suceed for login.<br /><b><i>The result will be, that there is no possibility to create new Backend-User via the Install tool!</i></b>';
			}

				// updatePasswd
			if($extConf['updatePasswd']) {
					// updatePasswd wont work with "forceSalted"
				if($extConf['forceSalted']) {
					$this->setErrorLevel('error');
					$problems[] = 'The Extension is misconfigured and won\'t work as expected. You set "updatePasswd" and "forceSalted". Since "forceSalted" prevents any other password-formats from beeing recognized, they cannot be converted either.';
					
					// inform the user how passwords are updated an that there is an convert script for fe_users
				} else {
					$this->setErrorLevel('info');
					$problems[] = 'You set "updatePasswd". Please Passwords will be converted on first login of each user. For FE-User there is a cli-script available, converting ALL fe-user passwords immidiately';
				}
			}
			
				// only saltedpasswords as authsservice
			if($extConf['onlyAuthService']) {
					// warn user taht the combination with "forceSalted" may lock him out from Backend
				if($extConf['forceSalted']) {
					$this->setErrorLevel('warning');
					$problems[] = 'You configured saltedpasswords to be the only aut-service AND forced salted-passwords. The result ist that there won\'t be any chance to login with users not having a salted password hash. Are you sure you wanna do this? This might lock you out from backend!';
					//inform the user that things like openid won't work anymore
				} else {
					$this->setErrorLevel('info');
					$problems[] = 'You configured saltedpasswords to be the only auth-service. This means that other services like ipauth, openid etc. Does not affect "rsauth", which will be implicitely used';
				}
			}
			
			
				// inform the user if securityLevel in FE is superchallenged or blank --> extension won't work
			if(!t3lib_div::inList($extConf['forceSalted'],'normal,rsa')) {
				$this->setErrorLevel('info');
				$problems[] = 'saltedpasswords are not activated for fronted-logins. use the Install-Tool to set the Security-Level for Frontend to "rsa" or "normal" (\$TYPO3_CONF_VARS][FE][loginSecurityLevel]). Make sure, that it is not blank or superchallenged.';
			}
			
			
				// if there are problems, render them into an unordered list
			if(count($problems)>0) {
				$message = 'Please have a look at the following list and take care:<br />&nbsp;<ul><li>' . implode('<br />&nbsp;</li><li>',$problems) . '</li></ul><br />Note, that a wrong configuration might have impact on the security of your TYPO3 installation and the usability of the backend.';
			}
		}

		$message=$this->preText . $message;
		if($message != '') {
			$message = t3lib_div::makeInstance('t3lib_FlashMessage',
				$message,
				$this->header,
				$this->errorType
			);
			return $message->render();
		} else {
			return '';
		}
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
				$this->preText = 'The extension will not work correct in this configuration.<br />';
				break;
			case 'warning':
				if ($this->errorType != t3lib_FlashMessage::ERROR) {
					$this->errorType = t3lib_FlashMessage::WARNING;
					$this->header = 'There are Warnings belonging your configuration.';
					$this->preText = 'The extensions might behave other than you may expect it.<br />';
					
				}
				break;
			case 'info' : 
				if ($this->errorType != t3lib_FlashMessage::ERROR && $this->errorType != t3lib_FlashMessage::WARNING) {
					$this->errorType = t3lib_FlashMessage::INFO;
					$this->header = 'Addditional information about your configuration is available.<br />';
					$this->preText = '';
				}
				break;
			case 'ok' :
				if ($this->errorType != t3lib_FlashMessage::ERROR && $this->errorType != t3lib_FlashMessage::WARNING && $this->errorType != t3lib_FlashMessage::INFO) {
					$this->errorType = t3lib_FlashMessage::OK;
					$this->header = 'No errors found...';
					$this->preText = 'As far as automatic checks can do, your saltedpasswords seems to be configured correct, and should work as expected.<br />';
				}
				break;
		}

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_emconfhelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_emconfhelper.php']);
}
?>