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
		 * Keeps pool of possible password characters.
		 *
		 */
		const PASSWORDCHARS = 'abcdefghkmnopqrstuvwxyz023456789';


		/**
		 * Function creates a password.
		 *
		 * @param   integer  length of password to be created
		 * @return  string   created password
		 */
		public static function generatePassword($len) {

			if (version_compare(TYPO3_branch, '4.3', '>=')) {
				$randomBytes = t3lib_div::generateRandomBytes($len);
			} else {
				$randomBytes = self::generateRandomBytes($len);
			}
			$passwordChars = self::PASSWORDCHARS;
			$password = '';
			while ($len-- > 0) {
				$password .= $passwordChars{ord($randomBytes{$len}) & 0x1F};
			}
			return $password;
		}


		/**
		 * Returns a string of highly randomized bytes (over the full 8-bit range).
		 *
		 * @copyright  Drupal CMS
		 * @license    GNU General Public License version 2
		 *
		 * @static
		 * @access  public
		 * @param   integer  number of characters (bytes) to return
		 * @return  string   random bytes
		 */
		public static function generateRandomBytes($count)  {

			$output = '';

				// /dev/urandom is available on many *nix systems and is considered the best
				// commonly available pseudo-random source.
			if (strcmp(TYPO3_OS, 'WIN') && ($fh = @fopen('/dev/urandom', 'rb'))) {
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
		 * Returns pool of password characters.
		 *
		 * @static
		 * @access  public
		 * @return  string  password character pool
		 */
		public static function getPasswordChars() {
			return self::PASSWORDCHARS;
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
				$salt = '$2$' . self::generatePassword(16);
			} else {	//md5 crypt is used
				$salt = '$1$' . self::generatePassword(12);
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
			if (t3lib_div::inList('normal,rsa',$GLOBALS['TYPO3_CONF_VARS'][$mode]['loginSecurityLevel'])) {
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