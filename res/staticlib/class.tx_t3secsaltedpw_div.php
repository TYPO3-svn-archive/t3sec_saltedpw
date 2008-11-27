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
		 * Function creates a password.
		 *
		 * @author  Kraft Bernhard <kraftb@gmx.net>
		 *
		 * @access  public
		 * @param   integer  $len  length of password to be created
		 * @return  string         created password.
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
		 * Function returns salt of a md5 hashed password string.
		 *
		 * @access  protected
		 * @param   string     $passString  password string including salt prefix
		 * @return  string                  salt used to generate the md5 hash with
		 */
		protected static function getMD5Salt( &$passString ) {
			$pos = strrpos($passString, '$');
			$salt = substr($passString, 3, $pos);
			return $salt;
		}

		/**
		 * Function returns salt of a password string.
		 *
		 * @access  public
		 * @param   string  $passString  password string including salt prefix
		 * @return  string               salt used to generate the hash with
		 */
		public static function getSaltByPasswdString( &$passString ) {
			$salt = '';

			$hashMethod = self::getHashMethodByPasswdString( $passString );

			if ( !empty($hashMethod)
				&& method_exists( get_class(), 'get' . strtoupper($hashMethod) . 'Salt')) {
				$salt = call_user_func( get_class() . '::get' . strtoupper($hashMethod) . 'Salt', $passString );
			}
			return $salt;
		}

		/**
		 * Function returns hashing method by looking at password string.
		 *
		 * @access  protected
		 * @param   string     $passString  password string including salt prefix
		 * @return  string                  salting method, if it is used (currently md5 only), otherwise empty string
		 */
		protected static function getHashMethodByPasswdString( &$passString ) {
			$method = '';

			if (!strncmp($passString, '$1$', 3)) {
				$method = 'md5';
			}
			return $method;
		}

		/**
		 * Returns the basic extension configuration data from localconf.php (configurable in Extension Manager)
		 *
		 * @author  Rainer Kuhn <kuhn@punkt.de>
		 * @author  Marcus Krause <marcus#exp2008@t3sec.info>
		 * @param   string      extension key of the extension to get its configuration
		 * @global  array       $TYPO3_CONF_VARS
		 * @return  array       basic extension configuration data from localconf.php
		 * @since   2006-05-18
		 */
		public static function returnExtConfArray($extKey) {

			require(PATH_typo3conf.'localconf.php');  // don't use require_once here!

			$baseConfArr = array();
			$baseConfArr = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$extKey]);

			return $baseConfArr;
		}

		/**
		 * Hashes a password with specified method in extension configuration using salt
		 *
		 * @access  public
		 * @param   string  cleartext password
		 * @param   string  (optional) salt (default: generate random salt)
		 * @return  string  encrypted password including salt
		 */
		public static function salt($cleartext, $salt = '') {

			$passwdString = '';
			$baseConfArr = self::returnExtConfArray('t3sec_saltedpw');

			$hashMethod = !empty($baseConfArr['hashingMethod']) ? strtoupper($baseConfArr['hashingMethod']) : 'MD5';

			if ( !empty($hashMethod)
				&& method_exists( get_class(), 'salt' . $hashMethod )) {
				$passwdString = call_user_func( get_class() . '::salt' . $hashMethod, $cleartext, $salt );
			} else {
				$passwdString = self::saltMD5($cleartext, $salt );
			}
			return $passwdString;
		}

		/**
		 * Hashes a password with md5 using salt.
		 *
		 * @access  public
		 * @param   string  cleartext password
		 * @param   string  (optional) salt (default: generate random salt)
		 * @return  string  encrypted password including salt
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


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/staticlib/class.tx_t3secsaltedpw_div.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/staticlib/class.tx_t3secsaltedpw_div.php']);
}
?>