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
 * Implementation of custom eval functions for tceforms.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/lib/class.tx_t3secsaltedpw_phpass.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');

/**
 * Class implementing salted evaluation methods.
 *
 * @author      Marcus Krause <marcus#exp2008@t3sec.info>
 *
 * @since       2008-11-15
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_salted {


	/**
	 * This function just return the field value as it is. No transforming,
	 * hashing will be done on server-side.
	 *
	 * @return	JavaScript code for evaluating the
	 */
	function returnFieldJS() {
		return 'return value;';
	}

	/**
	 * Function uses Portable PHP Hashing Framework to create a proper password string if needed
	 *
	 * @param	mixed		$value: The value that has to be checked.
	 * @param	string		$is_in: Is-In String
	 * @param	integer		$set: Determines if the field can be set (value correct) or not, e.g. if input is required but the value is empty, then $set should be set to false. (PASSED BY REFERENCE!)
	 * @return	The new value of the field
	 */
	function evaluateFieldValue($value, $is_in, &$set) {
		if (tx_t3secsaltedpw_div::isUsageEnabled()) {
			$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
			$updateNeeded = !empty($value) ? $objPHPass->isHashUpdateNeeded( $value ) : false;
	
				// value not recognized as hashed password of Portable PHP hashing framework
				// -> either clear-text one or an updated one created by Portable PHP hashing framework (prefix C||M)
			if ($updateNeeded && !(strlen($value) == 35 && 0 == substr_compare($value, '$P$', 1, 3))) {
					// TODO remove following if TCA eval functions are
					//      properly considered for BE user passwords
				if(TYPO3_MODE == 'BE'
						&& preg_match('/[0-9abcdef]{32,32}/', $value)) {
					$value = 'M' . $objPHPass->getHashedPassword($value);
				} else {
						// default
					$value = $objPHPass->getHashedPassword($value);
				}
			}
		}

		return $value;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/eval/class.tx_t3secsaltedpw_salted.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/eval/class.tx_t3secsaltedpw_salted.php']);
}
?>