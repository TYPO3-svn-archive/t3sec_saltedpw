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
/**
 * Contains class "tx_saltedpasswords_div" 
 * that provides various helper functions.
 * 
 * $Id$
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_factory.php');


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
				$this->objInstanceSaltedPW = tx_saltedpasswords_salts_factory::getSaltingInstance();
				$params['newPassword'] = $this->objInstanceSaltedPW->getHashedPassword($params['newPassword']);
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
			return array( 'onlyAuthService'       => '0',
						  'forceSalted'           => '0',
						  'updatePasswd'          => '1',
						  'saltedPWHashingMethod' => '0');
		}

		/**
		 * Function determines the default(=configured) type of 
		 * salted hashing method to be used.
		 * 
		 * @return  string  classname of object to be used
		 */
		public static function getDefaultSaltingHashingMethod() {
			
			$extConf = self::returnExtConf();
			$classNameToUse = 'tx_saltedpasswords_salts_md5';
			switch ($extConf['saltedPWHashingMethod']) {
				case '0': $classNameToUse = 'tx_saltedpasswords_salts_phpass';
						  break;
				case '1': $classNameToUse = 'tx_saltedpasswords_salts_md5';
						  break;
				case '2': $classNameToUse = 'tx_saltedpasswords_salts_blowfish';
						  break;
			}
			return $classNameToUse;
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
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_div.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_div.php']);
}
?>