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
	
	protected $problems = array();
		
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
					$this->header = 'There are warnings regarding your configuration.';
					$this->preText = 'The extension might behave different from your expectations.<br />';
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
					$this->header = 'No errors found...';
					$this->preText = 'As far as automatic checks can do, your saltedpasswords seems to be configured correct, and should work as expected.<br />';
				}
				break;
		}
	}
	
	private function renderFlashMessage() {
		$message = '';
			// if there are problems, render them into an unordered list
		if(count($this->problems)>0) {
			$message = 'Please have a look at the following list and take care:<br />&nbsp;<ul><li>' . implode('<br />&nbsp;</li><li>',$this->problems) . '</li></ul><br />Note, that a wrong configuration might have impact on the security of your TYPO3 installation and the usability of the backend.';
		}
		if (empty($message))  $this->setErrorLevel('ok');
		
		$message = $this->preText . $message;
		$flashMessage = t3lib_div::makeInstance('t3lib_FlashMessage', $message, $this->header, $this->errorType);
		return $flashMessage->render();
	}
	
	private function init() {
		$requestSetup = $this->processPostData((array)$_REQUEST['data']);
		$extConf = array_merge((array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['saltedpasswords']),$requestSetup);
		$this->extConf['BE'] = $extConf['BE.']; 
		$this->extConf['FE'] = $extConf['FE.']; 
		$GLOBALS['LANG']->includeLLFile('EXT:saltedpasswords/locallang.xml');

	}
	
	public function checkConfigurationBackend(&$params,$pObj) {
		$this->init();
		$extConf = $this->extConf['BE'];
		
			// the backend is called over SSL
		$SSL = (($GLOBALS['TYPO3_CONF_VARS']['BE']['lockSSL'] > 0 ? true : false) && ($GLOBALS['TYPO3_CONF_VARS']['BE']['loginSecurityLevel'] != 'superchallenged'));
			// rsaAuth is loaded/activ
		$RSAauth = (t3lib_extMgm::isLoaded('rsaauth') && ($GLOBALS['TYPO3_CONF_VARS']['BE']['loginSecurityLevel'] == 'rsa'));

		if($extConf['enabled']) {
				// SSL configured?
			if($SSL) {
				$problems[] = 'Your backend is configured to use saltedpasswords over SSL.';
				$this->setErrorLevel('ok');
			} else if ($RSAauth) {
				$problems[] = 'Your backend is configured to use saltedpasswords with RSA authentification.';
				$this->setErrorLevel('ok');
			} else {
				$tempMsg = '<b>IMPORTANT:</b><br/>saltedpasswords is not activated for backend, even if you enabled it for backend usage.<br>To make use of saltedpasswords in the backend make sure you follow the following steps:<br />';
				$tempMsg .= '&nbsp;-&nbsp;either use the Extension-Manager to install the sysext &quot;rsaauth&quot; and use the Install-Tool to set the Security-Level for Backend to "rsa" (\$TYPO3_CONF_VARS][BE][loginSecurityLevel])<br />';
				$tempMsg .= '&nbsp;-&nbsp;or - if you have the option to use SSL - configure your backend for SSL usage: use the Install-Tool to set the Security-Level for Backend to "normal" (\$TYPO3_CONF_VARS][BE][loginSecurityLevel]) and the SSL-Locking to a value greater than 0 (\$TYPO3_CONF_VARS][BE][lockSSL])<br />';
				$tempMsg .= 'After following (at least) one of this step(s) saltedpasswords will automatically be activated for your backend. SSL with RSAauth will be possible, too.';
				$problems[] = $tempMsg;
				$this->setErrorLevel('error');
			}
				
				// only saltedpasswords as authsservice
			if($extConf['onlyAuthService']) {
					// warn user taht the combination with "forceSalted" may lock him out from Backend
				if($extConf['forceSalted']) {
					$this->setErrorLevel('warning');
					$problems[] = 'You\' configured saltedpasswords to be the only authentication service for the backend and forced salted-passwords. The result ist that there won\'t be any chance to login with users not having a salted password hash. Are you sure you wanna do this? This might lock you out from backend!';
				} else {
						//inform the user that things like openid won't work anymore
					$this->setErrorLevel('info');
					$problems[] = 'You\' configured saltedpasswords to be the only authentication service for the backend. This means that other services like ipauth, openid etc will tried if authentication fails. Does not affect "rsauth", which will be implicitely used.';
				}
			}	
				// forceSalted is set
			if( $extConf['forceSalted'] && !$extConf['onlyAuthService'] ) {
				$this->setErrorLevel('warning');
				$problems[] = 'You set forceSalted to 1.<br>This means that only passwords in the format of this extensions will suceed for login.<br /><b><i>The result will be, that there is no possibility to create new Backend-User via the Install tool!</i></b>';
			} 
				// updatePasswd wont work with "forceSalted"
			if($extConf['updatePasswd'] && $extConf['forceSalted']) {
				$this->setErrorLevel('error');
				$problems[] = 'The Extension is misconfigured and won\'t work as expected. You set "updatePasswd" and "forceSalted". Since "forceSalted" prevents any other password-formats from beeing recognized, they cannot be converted either.';
			}
				// check wether configured hash-method is available on system
			if(!$instance = tx_saltedpasswords_salts_factory::getSaltingInstance(NULL,'BE') || @!$instance->isAvailable()) {
				$this->setErrorLevel('error');
				$problems[] = 'The method you have configured to be used to hash your passwords ist not available on your system! Adapt your configuration.';
			}
		} else {
			// not enabled warning
			$problems[] = 'You did not configure saltedpasswords to be used for backend-users.';
			$this->setErrorLevel('info');
		}
		
		$this->problems = $problems;
		return $this->renderFlashMessage();
	}
	
	public function checkConfigurationFrontend(&$params,$pObj) {
		$this->init();
		$extConf = $this->extConf['FE'];
		
		if($extConf['enabled']) {
				// inform the user if securityLevel in FE is superchallenged or blank --> extension won't work
			if(!t3lib_div::inList('normal,rsa', $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'])) {
				$this->setErrorLevel('info');
				$problems[] = 'saltedpasswords is not activated for frontend-logins. use the Install-Tool to set the Security-Level for Frontend to "rsa" or "normal" ($TYPO3_CONF_VARS][FE][loginSecurityLevel]). Make sure, that it is not blank or superchallenged.';
			}
			// only saltedpasswords as authsservice
			if($extConf['onlyAuthService']) {
					// warn user taht the combination with "forceSalted" may lock him out from Backend
				if($extConf['forceSalted']) {
					$this->setErrorLevel('warning');
					$problems[] = 'You\' configured saltedpasswords to be the only authentication service for the backend and forced salted-passwords. The result ist that there won\'t be any chance to login with users not having a salted password hash. Are you sure you wanna do this? This might lock you out from backend!';
				} else {
						//inform the user that things like openid won't work anymore
					$this->setErrorLevel('info');
					$problems[] = 'You\' configured saltedpasswords to be the only authentication service for the backend. This means that other services like ipauth, openid etc will tried if authentication fails. Does not affect "rsauth", which will be implicitely used.';
				}
			}	
				// forceSalted is set
			if( $extConf['forceSalted'] && !$extConf['onlyAuthService'] ) {
				$this->setErrorLevel('warning');
				$problems[] = 'You set forceSalted to 1.<br>This means that only passwords in the format of this extensions will suceed for login.<br /><b><i>The result will be, that there is no possibility to create new Backend-User via the Install tool!</i></b>';
			} 
				// updatePasswd wont work with "forceSalted"
			if($extConf['updatePasswd'] && $extConf['forceSalted']) {
				$this->setErrorLevel('error');
				$problems[] = 'The Extension is misconfigured and won\'t work as expected. You set "updatePasswd" and "forceSalted". Since "forceSalted" prevents any other password-formats from beeing recognized, they cannot be converted either.';
			}
		} else {
			// not enabled warning
			$problems[] = 'You did not configure saltedpasswords to be used for frontend-users.';
			$this->setErrorLevel('info');
		}
		$this->problems = $problems;
		return $this->renderFlashMessage();
	}
	
	private function buildHashMethodSelector(&$params,$pObj,$mode=TYPO3_MODE) {
		$this->init();
		$fieldName=substr($params['fieldName'],5,-1);
		$raname = substr(md5($fieldName),0,10);
		$aname='\''.$raname.'\'';

		$p_field='';
		foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/saltedpasswords']['saltMethods'] AS $class => $reference)     {
				@$classInstance = t3lib_div::getUserObj($reference,'tx_');
				if($classInstance instanceof tx_saltedpasswords_salts && $classInstance->isAvailable()) {
					$sel = ( $this->extConf[$mode]['saltedPWHashingMethod'] == $class ? ' selected="selected" ' : '');
					$label = 'ext.saltedpasswords.title.'.$class;
					$p_field.='<option value="'.htmlspecialchars($class).'"'.$sel.'>'.$GLOBALS['LANG']->getLL($label).'</option>';
				}

				
		}
		$p_field='<select id="'.$fieldName.'" name="'.$params['fieldName'].'" onChange="uFormUrl('.$aname.')">'.$p_field.'</select>';
		return $p_field;
	}
	
	public function buildHashMethodSelectorFE(&$params,$pObj) {
		return $this->buildHashMethodSelector($params,$pObj,'FE');
	}
	
	public function buildHashMethodSelectorBE(&$params,$pObj) {
		return $this->buildHashMethodSelector($params,$pObj,'BE');
	}
	
	private function processPostData($postArray = array()) {
		foreach ($postArray AS $key => $value) {
			$parts = explode('.',$key,2);
			if(count($parts)==2) {
				$postArray[$parts[0].'.'] = array_merge((array)$postArray[$parts[0].'.'],$this->processPostData(array($parts[1] => $value)));
			} else {
				$postArray[$key] = $value;			
			} 
		}
		return $postArray;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_emconfhelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_emconfhelper.php']);
}
?>