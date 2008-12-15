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
 * Modifying the tx_felogin_pi1.php class file so the salted password hashes
 * are created in case of forgotten passwords.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");


/**
 * Plugin 'Website User Login' for the 'felogin' extension.
 *
 * XCLASS that creates salted password hashes.
 *
 * @author	   Steffen Kamper <info@sk-typo3.de>
 * @author     Marcus Krause <marcus#exp2008@t3sec.info>
 * @since      2008-11-15
 * @package    TYPO3
 * @subpackage felogin
 */
class ux_tx_felogin_pi1 extends tx_felogin_pi1	{


	/**
	 * Returns the header / message value from flexform if present, else from locallang.xml
	 *
	 * @param	string		label name
	 * @param	string		TS stdWrap array
	 * @return	string		label text
	 */
	protected function getDisplayText($label, $stdWrapArray=array()) {
		global $LANG;
		//return $this->flexFormValue($label,'s_messages') ? $this->cObj->stdWrap($this->flexFormValue($label,'s_messages'),$stdWrapArray) : $this->cObj->stdWrap($this->pi_getLL('ll_'.$label, '', 1), $stdWrapArray);
		return $this->flexFormValue($label,'s_messages') ? $this->cObj->stdWrap($this->flexFormValue($label,'s_messages'),$stdWrapArray) : $this->cObj->stdWrap($LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_'.$label,1), $stdWrapArray);
	}

	/**
	 * Returns Array of markers filled with user fields
	 *
	 * @return	array		marker array
	 */
	protected function getUserFieldMarkers() {
		$marker = array();
		// replace markers with fe_user data
		if ($GLOBALS['TSFE']->fe_user->user) {
			// all fields of fe_user will be replaced, scheme is ###FEUSER_FIELDNAME###
			foreach ($GLOBALS['TSFE']->fe_user->user as $field => $value) {
				$marker['###FEUSER_' . t3lib_div::strtoupper($field) . '###'] = $this->cObj->stdWrap($value, $this->conf['userfields.'][$field . '.']);
			}
			// add ###USER### for compatibility
			$marker['###USER###'] = $marker['###FEUSER_USERNAME###'];
		}
		return $marker;
	}

	/**
		* Shows the forgot password form
		*
		* @return	string		content
		*/
	protected function showForgot() {
		global $LANG;

			// Get Template
		$templateFile = 'EXT:t3sec_saltedpw/res/tmpl/felogin_template.html';
		$template = $this->cObj->fileResource($templateFile);

		$subpart = $this->cObj->getSubpart($template, '###TEMPLATE_FORGOT###');
		$subpartArray = $linkpartArray = array();
		$postData =  t3lib_div::_POST($this->prefixId);

		if ($postData['forgot_email']) {

				// get hashes for compare
			$postedHash = $postData['forgot_hash'];
			$hashData = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'forgot_hash');


			if ($postedHash == $hashData['forgot_hash']) {
				$row = FALSE;

					// look for user record
				$data = $GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['forgot_email'], 'fe_users');
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid, username, password, email',
					'fe_users',
					'(email=' . $data .' OR username=' . $data . ') AND pid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($this->spid).') '.$this->cObj->enableFields('fe_users')
				);

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					//$msg = sprintf($this->pi_getLL('ll_forgot_email_password', '', 0), $row['email'], $row['username'], $row['password']);
					$msg = sprintf($LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_forgot_email_password',1), $row['email'], $row['username'], $row['password']);
				} else {
					//$msg = sprintf($this->pi_getLL('ll_forgot_email_nopassword', '', 0), $postData['forgot_email']);
					$msg = sprintf($LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_forgot_email_nopassword',1), $postData['forgot_email']);
				}

				if ($row) {
						// only generate email and possible password if user record was found

						// Generate new password with salted md5 and save it in user record
						// assumption: extension t3sec_saltedpw loaded
					if (t3lib_extMgm::isLoaded('t3sec_saltedpw')) {
						require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';

						if (tx_t3secsaltedpw_div::isUsageEnabled()) {
							require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php';

							$newPass = $this->generatePassword(8);
							$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
							$saltedPass = $objPHPass->getHashedPassword($newPass);
							$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
								'fe_users',
								'uid=' . $row['uid'],
								array('password' => $saltedPass)
							);
							//$msg = sprintf($this->pi_getLL('ll_forgot_email_password', '', 0), $postData['forgot_email'], $row['username'], $newPass);
							$msg = sprintf($LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_forgot_email_password',1), $postData['forgot_email'], $row['username'], $newPass);
						}
					}


					$this->cObj->sendNotifyEmail($msg, $row['email'], '', $this->conf['email_from'], $this->conf['email_fromName'], $this->conf['replyTo']);
				}
					// generate message
				//$markerArray['###STATUS_MESSAGE###'] = $this->cObj->stdWrap(sprintf($this->pi_getLL('ll_forgot_message_emailSent', '', 1), '<em>' . htmlspecialchars($postData['forgot_email']) .'</em>'), $this->conf['forgotMessage_stdWrap.']);
				$markerArray['###STATUS_MESSAGE###'] = $this->cObj->stdWrap(sprintf($LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_forgot_message_emailSent',1), '<em>' . htmlspecialchars($postData['forgot_email']) .'</em>'), $this->conf['forgotMessage_stdWrap.']);
				$subpartArray['###FORGOT_FORM###'] = '';


			} else {
					//wrong email
				$markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText('forgot_message', $this->conf['forgotMessage_stdWrap.']);
				$markerArray['###BACKLINK_LOGIN###'] = '';
			}
		} else {
			$markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText('forgot_message', $this->conf['forgotMessage_stdWrap.']);
			$markerArray['###BACKLINK_LOGIN###'] = '';
		}

		//$markerArray['###BACKLINK_LOGIN###'] = $this->getPageLink($this->pi_getLL('ll_forgot_header_backToLogin', '', 1), array());
		$markerArray['###BACKLINK_LOGIN###'] = $this->getPageLink($LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_forgot_header_backToLogin',1), array());
		$markerArray['###STATUS_HEADER###'] = $this->getDisplayText('forgot_header', $this->conf['forgotHeader_stdWrap.']);

		//$markerArray['###LEGEND###'] = $this->pi_getLL('send_password', '', 1);
		$markerArray['###LEGEND###'] = $LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:send_password',1);
		$markerArray['###ACTION_URI###'] = $this->getPageLink('', array($this->prefixId . '[forgot]'=>1), true);
		//$markerArray['###EMAIL_LABEL###'] = $this->pi_getLL('your_email', '', 1);
		$markerArray['###EMAIL_LABEL###'] = $LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:your_email',1);
		//$markerArray['###FORGOT_PASSWORD_ENTEREMAIL###'] = $this->pi_getLL('forgot_password_enterEmail', '', 1);
		$markerArray['###FORGOT_PASSWORD_ENTEREMAIL###'] = $LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:forgot_password_enterEmail',1);
		$markerArray['###FORGOT_EMAIL###'] = $this->prefixId.'[forgot_email]';
		//$markerArray['###SEND_PASSWORD###'] = $this->pi_getLL('send_password', '', 1);
		$markerArray['###SEND_PASSWORD###'] = $LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:send_password',1);
		//$markerArray['###DATA_LABEL###'] = $this->pi_getLL('ll_enter_your_data', '', 1);
		$markerArray['###DATA_LABEL###'] = $LANG->sL('LLL:EXT:t3sec_saltedpw/res/LL/felogin_locallang.xml:ll_enter_your_data',1);



		$markerArray = array_merge($markerArray, $this->getUserFieldMarkers());

			// generate hash
		$hash = md5($this->generatePassword(3));
		$markerArray['###FORGOTHASH###'] = $hash;
			// set hash in feuser session
		$GLOBALS["TSFE"]->fe_user->setKey('ses', 'forgot_hash', array('forgot_hash' => $hash));

		return $this->cObj->substituteMarkerArrayCached($subpart, $markerArray, $subpartArray, $linkpartArray);
	}
}
?>