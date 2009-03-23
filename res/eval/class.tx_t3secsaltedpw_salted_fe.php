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
 * Implementation of custom eval functions for tceforms (FE only).
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2009@t3sec.info>
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/eval/class.tx_t3secsaltedpw_salted.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/lib/class.tx_t3secsaltedpw_phpass.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');

/**
 * Class implementing salted evaluation methods for FE users.
 *
 * @author      Marcus Krause <marcus#exp2009@t3sec.info>
 *
 * @since       2009-03-23
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_salted_fe extends tx_t3secsaltedpw_salted {


	/**
	 * Class constructor.
	 * 
	 * @access  public
	 * @return  tx_t3secsaltedpw_salted_fe  instance of object  
	 */
	public function __construct() {
		$this->mode = 'FE';
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/eval/class.tx_t3secsaltedpw_salted_fe.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/eval/class.tx_t3secsaltedpw_salted_fe.php']);
}
?>