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

require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';

/**
 * Class implementing salted md5 evaluation methods.
 *
 * @author      Marcus Krause <marcus#exp2008@t3sec.info>
 * @since       2008-11-15
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_md5 {


	/**
	 * This function needs to return JavaScript code for client side evaluation of the
	 * field value. The JavaScript variable "value" is set to the field value in the context
	 * of this JS snippet.
	 * In this example we just add the string "[added by JS]" to the field value.
	 *
	 * @return	JavaScript code for evaluating the
	 */
	function returnFieldJS() {
		return '
		var asciiSimplified =   "!#%&()*+,-./0123456789:;<=>?" +
								"@ABCDEFGHIJKLMNOPQRSTUVWXYZ" +
								"[\\]^_abcdefghijklmnopqrstuvwxyz{|}~";
		var stringLen = 8;
		var salt = \'\';

		for (var i=0; i<stringLen; i++) {
			var randomNum = Math.floor(Math.random() * asciiSimplified.length);
			salt += asciiSimplified.substring(randomNum,randomNum + 1);
		}

		return \'$1$\' + salt + \'$\' + MD5(value + salt);';
	}

	/**
	 * This is the server side (i.e. PHP) side of the field evaluation.
	 * In this example we just add the string "[added by PHP]" to the field value.
	 *
	 * @param	mixed		$value: The value that has to be checked.
	 * @param	string		$is_in: Is-In String
	 * @param	integer		$set: Determines if the field can be set (value correct) or not (PASSED BY REFERENCE!)
	 * @return	The new value of the field
	 */
	function evaluateFieldValue($value, $is_in, &$set) {

		if(!empty($value) && 0 != strncmp($value, '$1$', 3)) {
			$value = tx_t3secsaltedpw_div::saltMD5($value);
		}

		return $value;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/eval/class.tx_t3secsaltedpw_md5.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/eval/class.tx_t3secsaltedpw_md5.php']);
}
?>