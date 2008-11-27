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
	 * Shows the forgot password form
	 *
	 * @access  protected
	 * @return	string	   content
	*/
	protected function showForgot() {
		$subpart = $this->cObj->getSubpart($this->template, '###TEMPLATE_FORGOT###');
		$subpartArray = $linkpartArray = array();

		if ($this->piVars['forgot_email']) {
			if (t3lib_div::validEmail($this->piVars['forgot_email'])) {
					// look for user record and send the password
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid, username, password',
					'fe_users',
					'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['forgot_email'], 'fe_users').' AND pid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($this->spid).') '.$this->cObj->enableFields('fe_users')
				);

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$msg = sprintf($this->pi_getLL('ll_forgot_email_password', '', 0), $this->piVars['forgot_email'], $row['username'], $row['password']);
				} else {
					$msg = sprintf($this->pi_getLL('ll_forgot_email_nopassword', '', 0), $this->piVars['forgot_email']);
				}

					// Generate new password with salted md5 and save it in user record
					// assumption: extension t3sec_saltedpw loaded and is enabled for FE usage
				if (t3lib_extMgm::isLoaded('t3sec_saltedpw')) {
					require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';

					if (tx_t3secsaltedpw_div::isUsageEnabled()
							&& $GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
						require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php';

						$newPass = $this->generatePassword(8);
						$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
						$saltedPass = $objPHPass->getHashedPassword($newPass);
						$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'fe_users',
							'uid=' . $row['uid'],
							array('password' => $saltedPass)
						);
						$msg = sprintf($this->pi_getLL('ll_forgot_email_password', '', 0),$this->piVars['forgot_email'], $row['username'], $newPass);
					}
				}

				$this->cObj->sendNotifyEmail($msg, $this->piVars['forgot_email'], '', $this->conf['email_from'], $this->conf['email_fromName'], $this->conf['replyTo']);
				$markerArray['###STATUS_MESSAGE###'] = $this->cObj->stdWrap(sprintf($this->pi_getLL('ll_forgot_message_emailSent', '', 1), '<em>' . htmlspecialchars($this->piVars['forgot_email']) .'</em>'), $this->conf['forgotMessage_stdWrap.']);
				$subpartArray['###FORGOT_FORM###'] = '';


			} else {
					//wrong email
				$markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText('forgot_message',$this->conf['forgotMessage_stdWrap.']);
				$markerArray['###BACKLINK_LOGIN###'] = '';
			}
		} else {
			$markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText('forgot_message',$this->conf['forgotMessage_stdWrap.']);
			$markerArray['###BACKLINK_LOGIN###'] = '';
		}

		$markerArray['###BACKLINK_LOGIN###'] = $this->getPageLink($this->pi_getLL('ll_forgot_header_backToLogin', '', 1), array());
		$markerArray['###STATUS_HEADER###'] = $this->getDisplayText('forgot_header',$this->conf['forgotHeader_stdWrap.']);

		$markerArray['###LEGEND###'] = $this->pi_getLL('send_password', '', 1);
		$markerArray['###ACTION_URI###'] = $this->getPageLink('',array($this->prefixId.'[forgot]'=>1),true);
		$markerArray['###EMAIL_LABEL###'] = $this->pi_getLL('your_email', '', 1);
		$markerArray['###FORGOT_PASSWORD_ENTEREMAIL###'] = $this->pi_getLL('forgot_password_enterEmail', '', 1);
		$markerArray['###FORGOT_EMAIL###'] = $this->prefixId.'[forgot_email]';
		$markerArray['###SEND_PASSWORD###'] = $this->pi_getLL('send_password', '', 1);

		return $this->cObj->substituteMarkerArrayCached($subpart, $markerArray, $subpartArray, $linkpartArray);
	}
}
?>