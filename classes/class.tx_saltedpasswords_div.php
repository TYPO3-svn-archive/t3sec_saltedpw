<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2009@t3sec.info)
*  (c) Steffen Ritter (info@rs-websystems.de)
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
	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once (PATH_t3lib.'class.t3lib_div.php');

/**
 * General library class.
 *
 * @author      Marcus Krause <marcus#exp2009@t3sec.info>
 * @author		Steffen Ritter <info@rs-websystems.de>
 *
 * @since       2009-06-14
 * @package     TYPO3
 * @subpackage  tx_saltedpasswords
 */
class tx_saltedpasswords_div  {


		/**
		 * Keeps this extension's key.
		 */
		const EXTKEY = 'saltedpasswords';

		/**
		 * Keeps pool of possible salt characters.
		 *
		 */
		const SALTCHARS = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';


		/**
		 * Function creates a salt.
		 *
		 * @param   integer  length of salt to be created
		 * @param   string   (optional) character pool to use
		 * @return  string   created salt
		 */
		public static function generateSalt($len, $charPool = null) {

			$randomBytes = t3lib_div::generateRandomBytes($len);
			$allowedChars = !is_null($charPool) ? $charPool : self::SALTCHARS;
			$salt = '';
			while ($len-- > 0) {
				$salt .= $allowedChars{ord($randomBytes{$len}) & 0x1F};
			}
			return $salt;
		}


		/**
		 * Returns pool of password characters.
		 *
		 * @static
		 * @access  public
		 * @return  string  password character pool
		 */
		public static function getSaltChars() {
			return self::SALTCHARS;
		}

		/**
		 * Returns extension configuration data from $TYPO3_CONF_VARS (configurable in Extension Manager)
		 *
		 * @author  Rainer Kuhn <kuhn@punkt.de>
		 * @author  Marcus Krause <marcus#exp2009@t3sec.info>
		 *
		 * @static
		 * @access  public
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
			if ( empty($extConf) && !strcmp($extKey, self::EXTKEY)) {
				$extConf = self::returnExtConfDefaults();
			}
			return $extConf;
		}

		/**
		 * Hook function for felogin "forgotPassword" functionality
		 * encrypts the new password before storing in database
		 *
		 * @param   mixed	$params	Parameter the hook delivers
		 * @param   object	$pObj	Parent Object from which the hook is called
		 *
		 */
		public function feloginForgotPasswordHook(&$params,$pObj) {
			if (self::isUsageEnabled('FE')) {
				$params['newPassword'] = self::getHashedPassword($params['newPassword']);
			}
		}

		/**
		 * Returns default configuration of this extension.
		 *
		 * @static
		 * @access  public
		 * @return  array   extension configuration data from localconf.php
		 */
		public static function returnExtConfDefaults() {
			return array(   'onlyAuthService' => '0',
							'forceSalted'   => '1',
							'updatePasswd'    => '1',
							'useBlowFish'	=> '0',
							'handleOldFormat' => 0);
		}

		/**
		 * Method determines the type of salting hashing method to be used
		 * 
		 * @return  string  classname of object to be used
		 */
		public static function getDefaultSaltingHashingMethod() {
			
			$extConf = self::returnExtConf();
			if( $extConf['useBlowFish'] ) {
				return 'tx_saltedpasswords_salts_blowfish';
			} else {
				return 'tx_saltedpasswords_salts_md5';
			}
		}

		/**
		 *	Returns a newly hashed password
		 *
		 * 	@since   2009-06-14
		 *	@static
		 *	@access public
		 *	@return	string	encoded string
		 */
		public static function getHashedPassword($value) {
			$extConf = self::returnExtConf();
			if( $extConf['useBlowFish'] ) {		//crypt is used with blowfish
				$salt = '$2$' . self::generateSalt(16);
			} else {	//md5 crypt is used
				$salt = '$1$' . self::generateSalt(12);
			}
				// generate salted Password Hash
			return crypt($value,$salt);
		}

		/**
		 *	Checks wether password is correct
		 *
		 * 	@since   2009-06-14
		 *	@static
		 *	@access public
		 *	@return	boolean	password correct
		 */
		public static function comparePasswordToHash($plainPassword = string, $saltedHash = string) {
			return (crypt($plainPassword,$saltedHash) == $saltedHash);
		}

		/**
		 * Returns information if salted password hashes are
		 * indeed used in the TYPO3_MODE.
		 *
		 * @static
		 * @access  public
		 * @since   2009-06-14
		 * @return  boolean     true, if salted password hashes are used in the
		 *                      TYPO3_MODE, otherwise false
		 */
		public static function isUsageEnabled($mode = TYPO3_MODE) {
				// Login Security Level Recognition

			if (t3lib_div::inList( ($mode == 'BE' ? 'rsa' : 'normal,rsa') ,$GLOBALS['TYPO3_CONF_VARS'][$mode]['loginSecurityLevel'])) {
				return true;
			}
			return false;
		}

		/**
		 * Checks wether old-format
		 *
		 *	@return boolean checks wether password would match in old style
		 */
		public static function compareOldFormatHash($plainPassword = string, $saltedHash = string) {
			$passwdValid = false;

				// Try to include file from several locations
				// t3lib_extmgm::extPath cannot be used since the extension is not loaded anymore!
				// temporaly set pathes for later use of t3lib_extmgm
			$libFound = @include_once PATH_site . '/typo3conf/ext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php';
			if ( $libFound ) {
				$GLOBALS['TYPO3_LOADED_EXT']['t3sec_saltedpw'] = array('type'=>'L', 'siteRelPath'=>'typo3conf/ext/t3sec_saltedpw/', 'typo3RelPath'=>'../typo3conf/ext/t3sec_saltedpw/');
			} else {
				$libFound = @include_once PATH_typo3 . '/ext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php';
				if ( $libFound  ) {
					$GLOBALS['TYPO3_LOADED_EXT']['t3sec_saltedpw'] = array('type'=>'G', 'siteRelPath'=>TYPO3_mainDir.'ext/t3sec_saltedpw/', 'typo3RelPath'=>'ext/t3sec_saltedpw/');
				} else {
					$libFound = @include_once PATH_typo3 . '/sysext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php';
					if ($libFound) {
						$GLOBALS['TYPO3_LOADED_EXT']['t3sec_saltedpw'] = array('type'=>'S', 'siteRelPath'=>TYPO3_mainDir.'sysext/t3sec_saltedpw/', 'typo3RelPath'=>'sysext/t3sec_saltedpw/');
					}
				}
			}

			if ( $libFound ) {
				$objPHPass   = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');

				if (!strncmp($saltedHash, '$P$', 3)) {
					$passwdValid = $objPHPass->checkPassword($plainPassword, $saltedHash);
				} else if (!strncmp($saltedHash, 'M$P$', 4)) {
					$passwdValid = $objPHPass->checkPassword(md5($plainPassword), substr($saltedHash, 1));
				} else if (!strncmp($saltedHash, 'C$P$', 4)) {
					$passwdValid = $objPHPass->checkPassword($plainPassword, substr($saltedHash, 1));
				}
					// Reset made changes
				unset($objPHPass);
				unset($GLOBALS['TYPO3_LOADED_EXT']['t3sec_saltedpw']);
			}
			return $passwdValid;
		}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_div.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_div.php']);
}
?>