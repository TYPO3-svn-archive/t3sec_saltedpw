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
 *
 * @since       2008-11-15
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_div  {


		/**
		 * Keeps this extension's key.
		 */
		const EXTKEY = 't3sec_saltedpw';


		/**
		 * Function creates a password.
		 *
		 * @param   integer $len  length of password to be created
		 * @return  string        created password
		 */
		public static function generatePassword($len) {
			$pass = "";
			while ($len--) {
				$char = rand(0,35);
				if ($char < 10) {
					$pass .= ''.$char;
				} else {
					$pass .= chr($char-10+97);
				}
			}
			return $pass;
		}


		/**
		 * Returns a string of highly randomized bytes (over the full 8-bit range).
		 *
		 * This function is better than simply calling mt_rand() or any other built-in
		 * PHP function because it can return a long string of bytes (compared to < 4
		 * bytes normally from mt_rand()) and uses the best available pseudo-random source.
		 *
		 * retrieved from Drupal CMS
		 *
		 * @param $count
		 *   The number of characters (bytes) to return in the string.
		 */
		public static function generateRandomBytes($count)  {

			$output = '';

				// /dev/urandom is available on many *nix systems and is considered the best
				// commonly available pseudo-random source.
			if (TYPO3_OS != 'WIN' && ($fh = @fopen('/dev/urandom', 'rb'))) {
				$output = fread($fh, $count);
				fclose($fh);
			}

					// fallback if /dev/urandom is not available
			if (!isset($output{$count - 1})) {
					// We initialize with the somewhat random.
				$randomState = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
								. microtime() . getmypid();
				while (!isset($output{$count - 1})) {
					$randomState = md5(microtime() . mt_rand() . $randomState);
					$output .= md5(mt_rand() . $randomState, true);
				}
				$output = substr($output, 0, $count);
			}
			return $output;
		}

		/**
		 * Returns extension configuration data from $TYPO3_CONF_VARS (configurable in Extension Manager)
		 *
		 * @author  Rainer Kuhn <kuhn@punkt.de>
		 * @author  Marcus Krause <marcus#exp2008@t3sec.info>
		 *
		 * @static
		 * @param   string      extension key of the extension to get its configuration (optional);
		 * 						if obmitted, the configuration of this extension is returned
		 * @return  array       extension configuration data
		 */
		public static function returnExtConf( $extKey = self::EXTKEY ) {
			$extConf = array();

			if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey])) {
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
			}

				// load defaults if necessary
			if ( empty($extConf) && 0 == strcmp($extKey, self::EXTKEY)) {
				$extConf = self::returnExtConfDefaults();
			}
			return $extConf;
		}

		/**
		 * Returns default configuration of this extension.
		 *
		 * @static
		 * @return  array  extension configuration data from localconf.php
		 */
		public static function returnExtConfDefaults() {
			return array(   'onlyAuthService' => '0',
							'forcePHPasswd'   => '1',
							'updatePasswd'    => '1');
		}

		/**
		 * Returns information if salted password hashes are
		 * indeed used in the TYPO3_MODE.
		 *
		 * @static
		 * @access  public
		 * @since   2008-11-22
		 * @return  boolean     true, if salted password hashes are used in the
		 *                      TYPO3_MODE, otherwise false
		 */
		public static function isUsageEnabled() {
				// Login Security Level Recognition
			if (0 == strcmp($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['loginSecurityLevel'], 'normal')) {
				return true;
			}
			return false;
		}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/staticlib/class.tx_t3secsaltedpw_div.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/staticlib/class.tx_t3secsaltedpw_div.php']);
}
?>