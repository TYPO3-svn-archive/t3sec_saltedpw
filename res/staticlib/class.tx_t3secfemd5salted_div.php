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
 * General helper methods library.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

/**
 * General library class.
 *
 * @author      Marcus Krause <marcus#exp2008@t3sec.info>
 * @since       2008-11-15
 * @package     TYPO3
 * @subpackage  tx_t3secfemd5salted
 */
class tx_t3secfemd5salted_div  {

		/**
		 * Encrypts a password with md5 using salt
		 *
		 * @param  string cleartext password
		 * @param  string (optional) salt (default: generate random salt)
		 * @return string encrypted password including salt
		 */
		public static function saltMD5($cleartext, $salt='') {

			$saltLength  = 8;
			$excludeList = array(34, 36, 39, 96);

			if ( empty($salt) || strlen($salt) < $saltLength ) {

					// extend salt when char not in exclude list
				while ( strlen($salt) < $saltLength ) {
					$randomInt = rand(33, 126);
					$salt .= !in_array($randomInt, $excludeList) ? chr($randomInt) : '';
				}
			} else {
				$salt = substr($salt, 0 , $saltLength);
			}

			return '$1$' . $salt . '$' . md5($cleartext . $salt);
		}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_femd5salted/res/staticlib/class.tx_t3secfemd5salted_div.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_femd5salted/res/staticlib/class.tx_t3secfemd5salted_div.php']);
}
?>