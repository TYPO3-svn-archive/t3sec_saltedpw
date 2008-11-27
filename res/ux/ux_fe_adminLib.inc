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

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php';
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
	 * Subfunction for displayEditScreen(); Takes a record and creates an edit form based on the template code for it.
	 * This function is called if the user is editing a record and permitted to do so. Checked in displayEditScreen()
	 *
	 * This function overrides that one in the parent class. It clears
	 * the password field.
	 *
	 * @param	array		The array with the record to edit
	 * @return	string		HTML content
	 * @access  private
	 * @see                 parent::displayEditForm()
	 * @see                 parent::displayEditScreen()
	 */
	function displayEditForm($origArr) {
		$origArr['password'] = '';
		return parent::displayEditForm($origArr);
	}

	/**
	 * This will evaluate the input values from $this->dataArr to see if they conforms with the requirements configured in TypoScript per field.
	 * For example this could be checking if a field contains a valid email address, a unique value, a value within a certain range etc.
	 * It will populate arrays like $this->failure and $this->failureMsg with error messages (which can later be displayed in the template). Mostly it does NOT alter $this->dataArr (such parsing of values was done by parseValues())
	 * Works based on configuration in TypoScript key [create/edit].evalValues
	 *
	 * @return	void
	 * @see init(), parseValues()
	 */
	function evalValues()	{
		// Check required, set failure if not ok.
		reset($this->requiredArr);
		$tempArr=array();
		while(list(,$theField)=each($this->requiredArr))	{
			if (!trim($this->dataArr[$theField]))	{
				$tempArr[]=$theField;
			}
		}

		// Evaluate: This evaluates for more advanced things than 'required' does. But it returns the same error code, so you must let the required-message tell, if further evaluation has failed!
		$recExist=0;
		if (is_array($this->conf[$this->cmdKey.'.']['evalValues.']))	{
			switch($this->cmd)	{
				case 'edit':
					if (isset($this->dataArr['pid']))	{			// This may be tricked if the input has the pid-field set but the edit-field list does NOT allow the pid to be edited. Then the pid may be false.
						$recordTestPid = intval($this->dataArr['pid']);
					} else {
						$tempRecArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable,$this->dataArr['uid']);
						$recordTestPid = intval($tempRecArr['pid']);
					}
					$recExist=1;
				break;
				default:
					$recordTestPid = $this->thePid ? $this->thePid : t3lib_div::intval_positive($this->dataArr['pid']);
				break;
			}

			reset($this->conf[$this->cmdKey.'.']['evalValues.']);
			while(list($theField,$theValue)=each($this->conf[$this->cmdKey.'.']['evalValues.']))	{
				$listOfCommands = t3lib_div::trimExplode(',',$theValue,1);
				while(list(,$cmd)=each($listOfCommands))	{
					$cmdParts = split('\[|\]',$cmd);	// Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
					$theCmd = trim($cmdParts[0]);
					switch($theCmd)	{
						case 'uniqueGlobal':
							if ($DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable,$theField,$this->dataArr[$theField],'','','','1'))	{
								if (!$recExist || $DBrows[0]['uid']!=$this->dataArr['uid'])	{	// Only issue an error if the record is not existing (if new...) and if the record with the false value selected was not our self.
									$tempArr[]=$theField;
									$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'The value existed already. Enter a new value.');
								}
							}
						break;
						case 'uniqueLocal':
							if ($DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable,$theField,$this->dataArr[$theField], 'AND pid IN ('.$recordTestPid.')','','','1'))	{
								if (!$recExist || $DBrows[0]['uid']!=$this->dataArr['uid'])	{	// Only issue an error if the record is not existing (if new...) and if the record with the false value selected was not our self.
									$tempArr[]=$theField;
									$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'The value existed already. Enter a new value.');
								}
							}
						break;
						case 'twice':
							if (strcmp($this->dataArr[$theField], $this->dataArr[$theField.'_again']))	{
								$tempArr[]=$theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter the same value twice');
							}
						break;
						case 'email':
							if (!$this->cObj->checkEmail($this->dataArr[$theField]))	{
								$tempArr[]=$theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter a valid email address');
							}
						break;
						case 'required':
							if (!trim($this->dataArr[$theField]))	{
								$tempArr[]=$theField;
								$this->failureMsg[$theField][] = $this->getFailure($theField, $theCmd, 'You must enter a value!');
							}
						break;
						case 'atLeast':
							$chars=intval($cmdParts[1]);
							if (strlen($this->dataArr[$theField])<$chars)	{
								$tempArr[]=$theField;
								$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'You must enter at least %s characters!'), $chars);
							}
						break;
						case 'atLeastOrEmpty':
							$chars=intval($cmdParts[1]);
							if (strlen($this->dataArr[$theField]) && strlen($this->dataArr[$theField])<$chars)	{
								$tempArr[]=$theField;
								$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'You must enter at least %s characters!'), $chars);
							}
						break;
						case 'atMost':
							$chars=intval($cmdParts[1]);
							if (strlen($this->dataArr[$theField])>$chars)	{
								$tempArr[]=$theField;
								$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'You must enter at most %s characters!'), $chars);
							}
						break;
						case 'inBranch':
							$pars = explode(';',$cmdParts[1]);
							if (intval($pars[0]))	{
								$pid_list = $this->cObj->getTreeList(
									intval($pars[0]),
									intval($pars[1]) ? intval($pars[1]) : 999,
									intval($pars[2])
								);
								if (!$pid_list || !t3lib_div::inList($pid_list,$this->dataArr[$theField]))	{
									$tempArr[]=$theField;
									$this->failureMsg[$theField][] = sprintf($this->getFailure($theField, $theCmd, 'The value was not a valid valud from this list: %s'), $pid_list);
								}
							}
						break;
						case 'unsetEmpty':
							if (!$this->dataArr[$theField])	{
								$hash = array_flip($tempArr);
								unset($hash[$theField]);
								$tempArr = array_keys($hash);
								unset($this->failureMsg[$theField]);
								unset($this->dataArr[$theField]);	// This should prevent the field from entering the database.
							}
						break;
					}
				}
				$this->markerArray['###EVAL_ERROR_FIELD_'.$theField.'###'] = is_array($this->failureMsg[$theField]) ? implode('<br />',$this->failureMsg[$theField]) : '';
			}
		}
		$this->failure=implode(',',$tempArr);	 //$failure will show which fields were not OK
	}

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
							$objPHPass = new tx_t3secsaltedpw_phpass();
							$updateNeeded = !empty($this->dataArr[$theField]) ? $objPHPass->isHashUpdateNeeded( $this->dataArr[$theField] ) : false;

								// value not recognized as hashed password of Portable PHP hashing framework
								// -> either clear-text one or an updated one created by Portable PHP hashing framework (prefix C||M)
							if ($updateNeeded && !(strlen($this->dataArr[$theField]) == 35 && 0 == strcmp(substr($this->dataArr[$theField],1,3), '$P$'))) {
								$this->dataArr[$theField . '_parsed'] = $objPHPass->getHashedPassword($this->dataArr[$theField]);
							}
							if ($updateNeeded && isset($this->dataArr[$theField . '_again'])) {
								$this->dataArr[$theField . '_again_parsed'] = $this->dataArr[$theField . '_parsed'];
							}
						break;
					}
				}
			}
		}
	}

	/**
	 * Performs the saving of records, either edited or created.
	 *
	 * @return	void
	 * @see init()
	 */
	function save()	{
		switch($this->cmd)	{
			case 'edit':
				$theUid = $this->dataArr['uid'];
				$origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable,$theUid);		// Fetches the original record to check permissions
				if ($this->conf['edit'] && ($GLOBALS['TSFE']->loginUser || $this->aCAuth($origArr)))	{	// Must be logged in in order to edit  (OR be validated by email)
					$newFieldList = implode(',',array_intersect(explode(',',$this->fieldList),t3lib_div::trimExplode(',',$this->conf['edit.']['fields'],1)));
					if ($this->aCAuth($origArr) || $this->cObj->DBmayFEUserEdit($this->theTable,$origArr,$GLOBALS['TSFE']->fe_user->user,$this->conf['allowedGroups'],$this->conf['fe_userEditSelf']))	{
						$dataArr_new = array();
						foreach ($this->dataArr as $k=>$v) {
							$matches = array();
							if (preg_match('#^(.*)_parsed$#', $k, $matches)) {
								$dataArr_new[$matches[1]] = $v;
							} else {
								$dataArr_new[$k] = $v;
							}
						}
						$this->dataArr = $dataArr_new;
						$this->cObj->DBgetUpdate($this->theTable, $theUid, $this->dataArr, $newFieldList, TRUE);
						$this->currentArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable,$theUid);
						$this->userProcess_alt($this->conf['edit.']['userFunc_afterSave'],$this->conf['edit.']['userFunc_afterSave.'],array('rec'=>$this->currentArr, 'origRec'=>$origArr));
						$this->saved=1;
					} else {
						$this->error='###TEMPLATE_NO_PERMISSIONS###';
					}
				}
			break;
			default:
				if ($this->conf['create'])	{
					$newFieldList = implode(',',array_intersect(explode(',',$this->fieldList),t3lib_div::trimExplode(',',$this->conf['create.']['fields'],1)));
					$this->cObj->DBgetInsert($this->theTable, $this->thePid, $this->dataArr, $newFieldList, TRUE);
					$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();

					if ($this->theTable=='fe_users' && $this->conf['fe_userOwnSelf'])	{		// enables users, creating logins, to own them self.
						$extraList='';
						$dataArr = array();
						if ($GLOBALS['TCA'][$this->theTable]['ctrl']['fe_cruser_id'])		{
							$field=$GLOBALS['TCA'][$this->theTable]['ctrl']['fe_cruser_id'];
							$dataArr[$field]=$newId;
							$extraList.=','.$field;
						}
						if ($GLOBALS['TCA'][$this->theTable]['ctrl']['fe_crgroup_id'])	{
							$field=$GLOBALS['TCA'][$this->theTable]['ctrl']['fe_crgroup_id'];
							list($dataArr[$field])=explode(',',$this->dataArr['usergroup']);
							$dataArr[$field]=intval($dataArr[$field]);
							$extraList.=','.$field;
						}
						if (count($dataArr))	{
							$this->cObj->DBgetUpdate($this->theTable, $newId, $dataArr, $extraList, TRUE);
						}
					}

					$this->currentArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable,$newId);
					$this->userProcess_alt($this->conf['create.']['userFunc_afterSave'],$this->conf['create.']['userFunc_afterSave.'],array('rec'=>$this->currentArr));
					$this->saved=1;
				}
			break;
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
					$objPHPass = new tx_t3secsaltedpw_phpass();
					foreach ($DBrows as $key => $row)	{
						if ($DBrows[$key]['password'])	{
							$newPass = tx_t3secsaltedpw_div::generatePassword(8);
							$saltedHash = $objPHPass->getHashedPassword($newPass);
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
