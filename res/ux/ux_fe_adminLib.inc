<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  (c) 2008      Marcus Krause (marcus#exp2008@t3sec.info)
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
 * Modifying the user_feAdmin class so the salted password hashes
 * are created.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';


/**
 * This library provides a HTML-template file based framework for Front End creating/editing/deleting records authenticated by email or fe_user login.
 * It is used in the extensions "direct_mail_subscription" and "feuser_admin" (and the deprecated(!) static template "plugin.feadmin.dmailsubscription" and "plugin.feadmin.fe_users" which are the old versions of these two extensions)
 * Further the extensions "t3consultancies" and "t3references" also uses this library but contrary to the "direct_mail_subscription" and "feuser_admin" extensions which relies on external HTML templates which must be adapted these two extensions delivers the HTML template code from inside.
 * Generally the fe_adminLib appears to be hard to use. Personally I feel turned off by all the template-file work involved and since it is very feature rich (and for that sake pretty stable!) there are lots of things that can go wrong - you feel. Therefore I like the concept used by "t3consultancies"/"t3references" since those extensions uses the library by supplying the HTML-template code automatically.
 * Suggestions for improvement and streamlining is welcome so this powerful class could be used more and effectively.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tslib
 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=396&cHash=d267c36546
 */
class ux_user_feAdmin extends user_feAdmin	{


	/**
	 * Performs processing on the values found in the input data array, $this->dataArr.
	 * The processing is done according to configuration found in TypoScript
	 * Examples of this could be to force a value to an integer, remove all non-alphanumeric characters, trimming a value, upper/lowercase it, or process it due to special types like files submitted etc.
	 * Called from init() if the $this->dataArr is found to be an array
	 *
	 * @return	void
	 * @see init()
	 */
	function parseValues()	{
		if (is_array($this->conf['parseValues.']))	{
			reset($this->conf['parseValues.']);
			while(list($theField,$theValue)=each($this->conf['parseValues.']))	{
				$listOfCommands = t3lib_div::trimExplode(',',$theValue,1);
				while(list(,$cmd)=each($listOfCommands))	{
					$cmdParts = split('\[|\]',$cmd);	// Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd=trim($cmdParts[0]);
					switch($theCmd)	{
						case 'int':
							$this->dataArr[$theField]=intval($this->dataArr[$theField]);
						break;
						case 'lower':
						case 'upper':
							$this->dataArr[$theField] = $this->cObj->caseshift($this->dataArr[$theField],$theCmd);
						break;
						case 'nospace':
							$this->dataArr[$theField] = str_replace(' ', '', $this->dataArr[$theField]);
						break;
						case 'alpha':
							$this->dataArr[$theField] = ereg_replace('[^a-zA-Z]','',$this->dataArr[$theField]);
						break;
						case 'num':
							$this->dataArr[$theField] = ereg_replace('[^0-9]','',$this->dataArr[$theField]);
						break;
						case 'alphanum':
							$this->dataArr[$theField] = ereg_replace('[^a-zA-Z0-9]','',$this->dataArr[$theField]);
						break;
						case 'alphanum_x':
							$this->dataArr[$theField] = ereg_replace('[^a-zA-Z0-9_-]','',$this->dataArr[$theField]);
						break;
						case 'trim':
							$this->dataArr[$theField] = trim($this->dataArr[$theField]);
						break;
						case 'random':
							$this->dataArr[$theField] = substr(md5(uniqid(microtime(),1)),0,intval($cmdParts[1]));
						break;
						case 'files':
							if ($this->cmdKey=='create' && !t3lib_div::_GP('doNotSave'))	{
								$this->processFiles($cmdParts,$theField);
							} else unset($this->dataArr[$theField]);	// Fields with files cannot be edited - only created.
						break;
						case 'setEmptyIfAbsent':
							if (!isset($this->dataArr[$theField]))	{
								$this->dataArr[$theField]='';
							}
						break;
						case 'multiple':
							if (is_array($this->dataArr[$theField]))	{
								$this->dataArr[$theField] = implode(',',$this->dataArr[$theField]);
							}
						break;
						case 'checkArray':
							if (is_array($this->dataArr[$theField]))	{
								reset($this->dataArr[$theField]);
								$val = 0;
								while(list($kk,$vv)=each($this->dataArr[$theField]))	{
									$kk = t3lib_div::intInRange($kk,0);
									if ($kk<=30)	{
										if ($vv)	{
											$val|=pow(2,$kk);
										}
									}
								}
								$this->dataArr[$theField] = $val;
							} else {$this->dataArr[$theField]=0;}
						break;
						case 'uniqueHashInt':
							$otherFields = t3lib_div::trimExplode(';',$cmdParts[1],1);
							$hashArray=array();
							while(list(,$fN)=each($otherFields))	{
								$vv = $this->dataArr[$fN];
								$vv = ereg_replace('[[:space:]]','',$vv);
								$vv = ereg_replace('[^[:alnum:]]','',$vv);
								$vv = strtolower($vv);
								$hashArray[]=$vv;
							}
							$this->dataArr[$theField]=hexdec(substr(md5(serialize($hashArray)),0,8));
						break;
						case 'saltedHash':
							$salt = tx_t3secsaltedpw_div::getSaltByPasswdString( $this->dataArr[$theField] );
							if (strlen($this->dataArr[$theField]) < 44 || empty($salt)) {
								$passwdString = tx_t3secsaltedpw_div::salt( $this->dataArr[$theField] );
								$this->dataArr[$theField] = $passwdString;
								if (isset($this->dataArr[$theField . '_again'])) {
									$this->dataArr[$theField . '_again'] =  $passwdString;
								}
							}
						break;
					}
				}
			}
		}
	}

	/**
	 * Sends info mail to user
	 *
	 * @return	string		HTML content message
	 * @see init(),compileMail(), sendMail()
	 */
	function sendInfoMail()	{
		if ($this->conf['infomail'] && $this->conf['email.']['field'])	{
			$fetch = t3lib_div::_GP('fetch');
			if ($fetch)	{
					// Getting infomail config.
				$key= trim(t3lib_div::_GP('key'));
				if (is_array($this->conf['infomail.'][$key.'.']))		{
					$config = $this->conf['infomail.'][$key.'.'];
				} else {
					$config = $this->conf['infomail.']['default.'];
				}
				$pidLock='';
				if (!$config['dontLockPid'])	{
					$pidLock='AND pid IN ('.$this->thePid.') ';
				}

					// Getting records
				if (t3lib_div::testInt($fetch))	{
					$DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable,'uid',$fetch,$pidLock,'','','1');
				} elseif ($fetch) {	// $this->conf['email.']['field'] must be a valid field in the table!
					$DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable,$this->conf['email.']['field'],$fetch,$pidLock,'','','100');
				}

					// Processing records
				if (is_array($DBrows))	{

					//$GLOBALS['TSFE']->includeTCA();
					//t3lib_div::loadTCA($this->theTable);
					$recipient = $DBrows[0][$this->conf['email.']['field']];

					foreach ($DBrows as $key => $row)	{
						if ($DBrows[$key]['password'])	{
							$newPass = tx_t3secsaltedpw_div::generatePassword(intval(8));
							$saltedHash = tx_t3secsaltedpw_div::salt($newPass);
							$DBrows[$key]['password'] = $newPass;
							$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->theTable, 'uid='.$row['uid'], array('password' => $saltedHash));
						}
					}
					$this->compileMail($config['label'], $DBrows, $recipient, $this->conf['setfixed.']);
				} elseif ($this->cObj->checkEmail($fetch)) {
					$this->sendMail($fetch, '', trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.'NORECORD###')));
				}

				$content = $this->getPlainTemplate('###TEMPLATE_INFOMAIL_SENT###');
			} else {
				$content = $this->getPlainTemplate('###TEMPLATE_INFOMAIL###');
			}
		} else $content='Error: infomail option is not available or emailField is not setup in TypoScript';
		return $content;
	}
}
?>
