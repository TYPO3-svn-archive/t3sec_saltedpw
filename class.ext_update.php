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
 * Class for updating FE user passwords.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */


/**
 * Class that provides update functionality for existing user records.
 *
 * @author  	Marcus Krause <marcus#exp2008@t3sec.info>
 *
 * @since   	2008-11-16
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class ext_update {


	/**
	 * Defines number of user records that will be updated per run.
	 */
	const PASSWD_UPDATE_RUN = 1000;


	/**
	 * Keeps the table name the update will do the update on.
	 *
	 * @var  string
	 */
	var $table = 'fe_users';


	/**
	 * Retrieves a column name by according TCA table property.
	 *
	 * @param   string  $property     table property to get the column name of
	 * @param   string  $defaultName  default column name (optional)
	 * @return  string                column name if TCA entry could be found,
	 *                                otherwise null
	 */
	function getColumnNameByTCA ($property, $defaultName = null) {
		$columnName = null;
		$columnName = isset($GLOBALS['TCA'][$this->table]['ctrl'][$property])
						? $GLOBALS['TCA'][$this->table]['ctrl'][$property]
						: $defaultName;
		return $columnName;
	}

	function access() {

		$colCrdate = $this->getColumnNameByTCA('crdate', 'crdate');

			// retrieving recent records from fe_users table
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(  'password',           // SELECT
														'fe_users',           // FROM
														'', 		          // WHERE
														'',                   // GROUP BY
														$colCrdate . ' DESC', // ORDER BY
														1                     // LIMIT
		);

		$sumRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($sumRows) {
			$passLen = array();
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			if ((0 == strncmp($row[0], '$P$', 3))
					|| 0 == substr_compare($row[0], '$P$', 1, 3)) {
				$showFunction = false;
			} else {
				$showFunction = true;
			}
		}
		return $showFunction;
	}

	function main() {

		$content = '<h2 class="typo3-tstemplate-ceditor-subcat">Update of FE user passwords</h2><p>&nbsp;</p>';

		if (t3lib_div::_GP('update')) {
			$sumRecords = $this->updateUsersRecords();
			$content = '<p>Updated records: ' . $sumRecords . '</p><p>&nbsp;</p>';
			if (intval($sumRecords) == self::PASSWD_UPDATE_RUN) {
				$content .= '<p>You will need to run this script again to update '
						.  'the remaining user records.</p></p><p>&nbsp;</p>'
						.  $this->getUpdateForm();
			} else {
				require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';
				$extConfDefault = tx_t3secsaltedpw_div::returnExtConfDefaults();
				$extConf = tx_t3secsaltedpw_div::returnExtConf();
				$content .= '<p>All records have been updated.</p><p>&nbsp;</p>'
						.  '<strong>Please make sure that extension configuration variable '
						.  '<i>forcePHPasswd</i> is disabled to use the updated passwords.</strong><br>'
						.  'This variable is by <strong>default</strong> '
						. (intval($extConfDefault['forcePHPasswd']) == 1 ? 'enabled' : 'disabled')
						. '. ';

				$content .= 'This variable is currently '
						. (intval($extConf['forcePHPasswd']) == 1 ? 'enabled' : 'disabled')
						. '.</p>';
			}
		} else {
			$content = '<p>It\'s most likely necessary to update FE user passwords as it seems that '
					.  'they are not encrypted/hashed with the method brought with this extension.</p><p>&nbsp;</p>'
					.  '<p>Do <strong>not execute</strong> the update if all or part of existing '
					.  'passwords are <strong>neither clear-text nor md5 hashed</strong> ones!<p>&nbsp;</p>'
					.  '<p>Do you want me to update the passwords for all records? Please mind that '
					.  'it might take some time!<br>'
					.  'Every script run will convert a <strong>maximum of '
					.  self::PASSWD_UPDATE_RUN . ' user records</strong>.'
					.  '</p><p>&nbsp;</p>'
					.  $this->getUpdateForm();

		}
		return $content;
	}

	function getUpdateForm() {
		$content = '<form name="tx_t3secsaltedpw_form" action="'
				.  htmlspecialchars(t3lib_div::linkThisScript()) .  '" method="post">'
				.  '<input name="update" value="Update" type="submit" style="font-weight: bold;"/>'
				.  '</form>';
		return $content;
	}

	function updateUsersRecords() {
		$colCrdate = $this->getColumnNameByTCA('crdate', 'crdate');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(  'uid, password',                     // SELECT
														'fe_users',                          // FROM
														'1 = 1 '
														.'AND password NOT LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('M$P$%', $this->table) . ' '
														.'AND password NOT LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('C$P$%', $this->table) . ' '
														.'AND password NOT LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('$P$%', $this->table), // WHERE
														'',                                  // GROUP BY
														$colCrdate . ' ASC',                 // ORDER BY
														self::PASSWD_UPDATE_RUN              // LIMIT
		);
		$sumRow = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($sumRow) {
			$this->processUserRecords($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $sumRow;
	}

	function processUserRecords(&$res) {
		require_once (t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php');
		$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
		$updatedPassword = '';

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$updatedPassword = $objPHPass->getHashedPassword($row['password']);
				// testing of clear-text or md5 hashed passwords
				// prefix C or prefix M
			if(preg_match('/[0-9abcdef]{32,32}/', $row['password']))
				$this->updatePassword(intval($row['uid']) ,'M' . $updatedPassword);
			else
				$this->updatePassword(intval($row['uid']) ,'C' . $updatedPassword);
		}
	}

	function updatePassword($uid, $updatedPassword) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery( $this->table,
												'uid = ' . $uid,
												array('password' => $updatedPassword,));
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/class.ext_update.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/class.ext_update.php']);
}
?>