<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2009@t3sec.info)
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
 * Class for updating FE user passwords.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2009@t3sec.info>
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once (PATH_t3lib.'class.t3lib_div.php');

/**
 * Class that provides update functionality for existing user records.
 *
 * @author  	Marcus Krause <marcus#exp2009@t3sec.info>
 *
 * @since   	2008-11-16
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class ext_update {


	/**
	 * Keeps this extension's key.
	 */
	const EXTKEY = 't3sec_saltedpw';

	/**
	 * Defines number of user records that will be updated per run.
	 */
	const PASSWD_UPDATE_RUN = 1000;

	/**
	 * Defines constant, used to indicate that BE users table needs an update.
	 *
	 * bitmask for 1st bit (0x1)
	 */
	const NEED_UPDATE_BE = 1;

	/**
	 * Defines constant, used to indicate that FE users table needs an update.
	 *
	 * bitmask for 1st bit (0x2)
	 */
	const NEED_UPDATE_FE = 2;

	/**
	 * Keeps the table name of frontend users the update will do the update on.
	 *
	 * @var  string
	 */
	var $tableBE = 'be_users';

	/**
	 * Keeps the table name of frontend users the update will do the update on.
	 *
	 * @var  string
	 */
	var $tableFE = 'fe_users';


	/**
	 * Retrieves a column name by according TCA table property.
	 *
	 * @param   string  $property     table property to get the column name of
	 * @param   string  $defaultName  default column name (optional)
	 * @return  string                column name if TCA entry could be found,
	 *                                otherwise null
	 */
	function getColumnNameByTCA ($table, $property, $defaultName = null) {
		$columnName = null;
		$columnName = isset($GLOBALS['TCA'][$table]['ctrl'][$property])
						? $GLOBALS['TCA'][$table]['ctrl'][$property]
						: $defaultName;
		return $columnName;
	}

	/**
	 * Enter description here...
	 *
	 * @return integer
	 */
	function checkRecentRecord() {
		return $this->checkRecentRecordByTable($this->tableBE)
				| $this->checkRecentRecordByTable($this->tableFE);
	}

	function checkRecentRecordByTable(&$table) {
		$colCrdate = $this->getColumnNameByTCA($table, 'crdate', 'crdate');

					// retrieving recent records from fe_users table
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(  'password',           // SELECT
														$table,               // FROM
														'', 		          // WHERE
														'',                   // GROUP BY
														$colCrdate . ' DESC', // ORDER BY
														1                     // LIMIT
		);
		$result = 0;
		$sumRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($sumRows) {
			$passLen = array();
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			if (!$this->isSaltPWHash($row[0])) {
				switch($table) {
					case 'be_users':    $result = self::NEED_UPDATE_BE;
										break;
					case 'fe_users':	$result = self::NEED_UPDATE_FE;
										break;
					default:            break;
				}
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $result;
	}

	/**
	 * Enter description here...
	 *
	 * @return  boolean  true, if user record passwords need an update,
	 *                   otherwise false
	 */
	function access() {
		$accessAllowed = false;
			// allow updating existing passwords only when ext usage
			// is enabled for either BE or FE or both
		if (t3lib_extMgm::isLoaded(self::EXTKEY)) {
			require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');
			if (tx_t3secsaltedpw_div::isUsageEnabled('FE') || tx_t3secsaltedpw_div::isUsageEnabled('BE')) {
				if ($this->checkRecentRecord() > 0) $accessAllowed = true;
			}
		}
		return $accessAllowed;
	}

	/**
	 * Main method
	 *
	 * @return  string  html text
	 */
	function main() {

		$content = '<h2 class="typo3-tstemplate-ceditor-subcat">Update of BE/FE user passwords</h2><p>&nbsp;</p>';

		if (t3lib_div::_GP('update')) {
			$sumRecords = 0;
				// BE user update
			if (in_array('be', t3lib_div::_GP('update'))) {
				$sumRecords += $this->updateUsersRecords($this->tableBE, in_array('fe',t3lib_div::_GP('update')) ? ceil(1/3 * self::PASSWD_UPDATE_RUN) : self::PASSWD_UPDATE_RUN);
			}
				// FE user update
			if (in_array('fe', t3lib_div::_GP('update'))) {
				$sumRecords += $this->updateUsersRecords($this->tableFE, in_array('be',t3lib_div::_GP('update')) ? ceil(2/3 * self::PASSWD_UPDATE_RUN) : self::PASSWD_UPDATE_RUN);
			}
			$content .= '<p>Updated records: ' . $sumRecords . '</p><p>&nbsp;</p>';
			$intNeedUpdate = $this->checkRecentRecord();
			if ($intNeedUpdate > 0) {
				$content .= '<p>You will need to run this script again to update '
						.  'the remaining user records.</p></p><p>&nbsp;</p>'
						.  $this->getUpdateForm($this->checkRecentRecord());
			} else {
				require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';
				$extConfDefault = tx_t3secsaltedpw_div::returnExtConfDefaults();
				$extConf = tx_t3secsaltedpw_div::returnExtConf();
				$content .= '<p>All records have been updated.</p><p>&nbsp;</p>'
						.  '<strong>Please make sure that extension configuration variable '
						.  '<i>forcePHPasswd</i> is disabled to use the updated passwords.</strong><br>'
						.  'This variable is <strong>by default '
						. (intval($extConfDefault['forcePHPasswd']) == 1 ? 'enabled' : 'disabled')
						. '</strong>. ';

				$content .= 'This variable is <strong>currently '
						. (intval($extConf['forcePHPasswd']) == 1 ? 'enabled' : 'disabled')
						. '</strong>.</p>';
			}
		} else {
			$content.= '<p>It\'s most likely necessary to update user passwords as it seems that '
					.  'they are not encrypted/hashed with the method brought with this extension.</p><p>&nbsp;</p>'
					.  '<p>Do <strong>not execute</strong> the update if all or part of existing '
					.  'passwords are <strong>neither clear-text nor md5 hashed</strong> ones!<p>&nbsp;</p>'
					.  '<p>Do you want me to update the passwords for all records? Please mind that '
					.  'it might take some time!<br>'
					.  'Every script run will convert a <strong>maximum of '
					.  self::PASSWD_UPDATE_RUN . ' user records</strong>.'
					.  '</p><p>&nbsp;</p>'
					.  $this->getUpdateForm($this->checkRecentRecord());

		}
		return $content;
	}

	/**
	 * Enter description here...
	 *
	 * @param   integer  $bitUpdateTables  bitmask indicating which table needs an update
	 * @return  string                     html text form
	 */
	function getUpdateForm($bitUpdateTables) {
		$content = '<form name="tx_t3secsaltedpw_form" action="'
				.  htmlspecialchars(t3lib_div::linkThisScript()) .  '" method="post">'
				.  '<fieldset><legend>Update user records</legend>';
		if ($bitUpdateTables & self::NEED_UPDATE_BE) {
			$content.= '<input type="checkbox" name="update[]" value="be">backend users<br>';
		}
		if ($bitUpdateTables & self::NEED_UPDATE_FE) {
			$content.= '<input type="checkbox" name="update[]" value="fe">frontend users<br>';
		}
		$content.= '</fieldset><br><input name="update[]" value="Update" type="submit" style="font-weight: bold;"/>'
				.  '</form>';
		return $content;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $passStr
	 * @return unknown
	 */
	function isSaltPWHash($passStr) {
		if ((34 == strlen($passStr) && 0 == strncmp($passStr, '$P$', 3))
				|| (35 == strlen($passStr) && 0 == substr_compare($passStr, '$P$', 1, 3)))
			return true;
		else
			return false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $table
	 * @param unknown_type $numRecords
	 * @return unknown
	 */
	function updateUsersRecords(&$table, $numRecords) {
		$colCrdate = $this->getColumnNameByTCA($table, 'crdate', 'crdate');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(  'uid, password',                     // SELECT
														$table,                              // FROM
														'1 = 1 '
														.'AND password NOT LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('M$P$%', $table) . ' '
														.'AND password NOT LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('C$P$%', $table) . ' '
														.'AND password NOT LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('$P$%',  $table), // WHERE
														'',                                  // GROUP BY
														$colCrdate . ' ASC',                 // ORDER BY
														$numRecords                          // LIMIT
		);
		$sumRow = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($sumRow) {
			$this->processUserRecords($res, $table);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $sumRow;
	}

	function processUserRecords(&$res, &$table) {
		require_once (t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php');
		$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
		$updatedPassword = '';

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$updatedPassword = $objPHPass->getHashedPassword($row['password']);
				// testing of clear-text or md5 hashed passwords
				// prefix C or prefix M
			if(preg_match('/[0-9abcdef]{32,32}/', $row['password']))
				$this->updatePassword($table, intval($row['uid']) ,'M' . $updatedPassword);
			else
				$this->updatePassword($table, intval($row['uid']) ,'C' . $updatedPassword);
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param string $table
	 * @param unknown_type $uid
	 * @param unknown_type $updatedPassword
	 */
	function updatePassword(&$table, $uid, $updatedPassword) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery( $table,
												'uid = ' . $uid,
												array('password' => $updatedPassword,));
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/class.ext_update.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/class.ext_update.php']);
}
?>