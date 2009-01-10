<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2006 Solar Designer (solar at openwall.com)
*  (c) 2008      Dries Buytaert (dries at buytaert.net)
*  (c) 2008      Marcus Krause  (marcus#exp2009@t3sec.info)
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
 * Secure password hashing class for user authentication.
 *
 * Derived from Drupal CMS
 * original license: GNU General Public License (GPL)
 * @see http://drupal.org/node/29706/
 *
 * Based on the Portable PHP password hashing framework
 * original license: Public Domain
 * @see http://www.openwall.com/phpass/
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2009@t3sec.info>
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once (PATH_t3lib.'class.t3lib_div.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');


/**
 * Class implements Portable PHP password hashing framework.
 *
 * @author  	Marcus Krause <marcus#exp2009@t3sec.info>
 *
 * @since   	2008-11-16
 * @package     TYPO3
 * @subpackage  tx_t3secsaltedpw
 */
class tx_t3secsaltedpw_phpass {


	/**
	 * The default log2 number of iterations for password stretching.
	 * This should increased by 1 from time to time to counteract
	 * increases in the speed and power of computers available to
	 * crack the hashes.
	 */
	const HASH_COUNT = 14;

	/**
	 * The default minimum allowed log2 number of iterations for
	 * password stretching.
	 */
	const MIN_HASH_COUNT = 7;

	/**
	 * The default maximum allowed log2 number of iterations for
	 * password stretching.
	 */
	const MAX_HASH_COUNT = 30;


	/**
	 * Keeps log2 number
	 * of iterations for password stretching.
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $hashCount;

	/**
	 * Keeps minimum allowed log2 number
	 * of iterations for password stretching.
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $minHashCount;

	/**
	 * Keeps maximum allowed log2 number
	 * of iterations for password stretching.
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $maxHashCount;


	/**
	 * Class constructor.
	 *
	 * @access  public
	 * @param   integer  $hashCount  log2 number of iterations for password stretching (optional)
	 */
	public function __construct( $hashCount = null ) {
		$this->setHashCount($hashCount);
		$this->setMinHashCount();
		$this->setMaxHashCount();
	}

	/**
	 * Encodes bytes into printable base 64 using the *nix standard from crypt().
	 *
	 * @access protected
	 * @param  string     $input  the string containing bytes to encode.
	 * @param  integer    $count  the number of characters (bytes) to encode.
	 * @return string             encoded string
	 */
	protected function base64Encode($input, $count)  {
		$output = '';
		$i = 0;
		$itoa64 = $this->getItoa64();
		do {
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];
			if ($i < $count) {
				$value |= ord($input[$i]) << 8;
			}
			$output .= $itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count) {
				break;
			}
			if ($i < $count) {
				$value |= ord($input[$i]) << 16;
			}
			$output .= $itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count) {
				break;
			}
			$output .= $itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);
		return $output;
	}

	/**
	 * Checks whether a plain text password matches a stored hashed password.
	 *
	 * @param   string  $plainPW         plain-text password to compare with salted hash
	 * @param   string  $saltedHashPW    salted hash to compare plain-text password with
	 * @return  boolean                  true if plain-text password matches the salted
	 *                                   hash, otherwise false
	 */
	public function checkPassword($plainPW, $saltedHashPW) {
		$hash = $this->cryptPassword($plainPW, $saltedHashPW);
		return ($hash && !strcmp($saltedHashPW, $hash));
	}

	/**
	 * Hashes a password using a secure stretched hash.
	 *
	 * By using a salt and repeated hashing the password is "stretched". Its
	 * security is increased because it becomes much more computationally costly
	 * for an attacker to try to break the hash by brute-force computation of the
	 * hashes of a large number of plain-text words or strings to find a match.
	 *
	 * @param   string  $password  plain-text password to hash
	 * @param   string  $setting   an existing hash or the output of getGeneratedSalt()
	 * @return  mixed              a string containing the hashed password (and salt)
	 *                             or boolean FALSE on failure.
	 */
	protected function cryptPassword($password, $setting)  {
			// The first 12 characters of an existing hash are its setting string.
		$setting = substr($setting, 0, 12);

		if (strncmp($setting, '$P$', 3)) return FALSE;

		$count_log2 = self::getCountLog2($setting);
			// Hashes may be imported from elsewhere, so we allow != HASH_COUNT
		if ($count_log2 < $this->getMinHashCount() || $count_log2 > $this->getMaxHashCount()) {
			return FALSE;
		}
		$salt = substr($setting, 4, 8);
			// Hashes must have an 8 character salt.
		if (!isset($salt{7})) return FALSE;

			// We must use md5() or sha1() here since they are the only cryptographic
			// primitives always available in PHP 5. To implement our own low-level
			// cryptographic function in PHP would result in much worse performance and
			// consequently in lower iteration counts and hashes that are quicker to crack
			// (by non-PHP code).
		$count = 1 << $count_log2;

		$hash = md5($salt . $password, TRUE);
		do {
			$hash = md5($hash . $password, TRUE);
		} while (--$count);

		$output =  $setting . $this->base64Encode($hash, 16);
			// base64Encode() of a 16 byte MD5 will always be 22 characters.
		return (strlen($output) == 34) ? $output : FALSE;
	}

	/**
	 * Parses the log2 iteration count from a stored hash or setting string.
	 *
	 * @access  protected
	 * @param   string     $setting  complete hash or a hash's setting string or to get log2 iteration count from
	 * @return  int                  used hashcount for given hash string
	 */
	protected function getCountLog2($setting) {
		$itoa64 = $this->getItoa64();
		return strpos($itoa64, $setting[3]);
	}

	/**
	 * Generates a random base 64-encoded salt prefixed with settings for the hash.
	 *
	 * Proper use of salts may defeat a number of attacks, including:
	 *  - The ability to try candidate passwords against multiple hashes at once.
	 *  - The ability to use pre-hashed lists of candidate passwords.
	 *  - The ability to determine whether two users have the same (or different)
	 *    password without actually having to guess one of the passwords.
	 *
	 * @access  protected
	 * @param   integer    $countLog2  determines the number of iterations used in the hashing
	 *                                 process; a larger value is more secure, but takes more
	 *                                 time to complete.
	 * @return  string                 a 12 character string containing the iteration count and
	 *                                 a random salt.
	 */
	protected function getGeneratedSalt($countLog2) {
		$output = '$P$';
			// Minimum log2 iterations is MIN_HASH_COUNT.
		$countLog2 = max($countLog2, $this->getMinHashCount());
			// Maximum log2 iterations is MAX_HASH_COUNT.
			// We encode the final log2 iteration count in base 64.
		$itoa64 = $this->getItoa64();
		$output .= $itoa64[min($countLog2, $this->getMaxHashCount())];

			// 6 bytes is the standard salt for a portable phpass hash.
		if (version_compare(TYPO3_branch, '4.3', '>=')) {
			$randomBytes = t3lib_div::generateRandomBytes(6);
		} else {
			$randomBytes = tx_t3secsaltedpw_div::generateRandomBytes(6);
		}
		$output .= $this->base64Encode($randomBytes, 6);
		return $output;
	}

	/**
	 * Method returns log2 number of iterations for password stretching.
	 *
	 * @access  protected
	 * @return  integer    log2 number of iterations for password stretching
	 * @see                HASH_COUNT
	 * @see                $hashCount
	 * @see                setHashCount()
	 */
	public function getHashCount() {
		return isset($this->hashCount) ? $this->hashCount : self::HASH_COUNT;
	}

	/**
	 * Hashes a password using a secure hash.
	 *
	 * @access  public
	 * @param   string   $password   plain-text password.
	 * @param   integer  $countLog2  optional integer to specify the iteration count;
	 *                               generally used only during bulk operations where
	 *                               a value less than the default is needed for speed
	 * @return  mixed                string containing the hashed password (and a salt),
	 *                               or boolean FALSE on failure.
	 */
	public function getHashedPassword($password, $countLog2 = 0) {
		if (empty($countLog2)) {
				// uses the standard iteration count
			$countLog2 = $this->getHashCount();
		}
		return $this->cryptPassword($password, $this->getGeneratedSalt($countLog2));
	}

	/**
	 * Returns a string for mapping an int to the corresponding base 64 character.
	 *
	 * @access  protected
	 * @return  string     string for mapping an int to the corresponding
	 *                     base 64 character
	 */
	protected function getItoa64() {
		return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	}

	/**
	 * Method returns maximum allowed log2 number of iterations for password stretching.
	 *
	 * @return  integer  maximum allowed log2 number of iterations for password stretching
	 * @see              MAX_HASH_COUNT
	 * @see              $maxHashCount
	 * @see              setMaxHashCount()
	 */
	public function getMaxHashCount() {
		return isset($this->maxHashCount) ? $this->maxHashCount : self::MAX_HASH_COUNT;
	}

	/**
	 * Method returns minimum allowed log2 number of iterations for password stretching.
	 *
	 * @return  integer  minimum allowed log2 number of iterations for password stretching
	 * @see              MIN_HASH_COUNT
	 * @see              $minHashCount
	 * @see              setMinHashCount()
	 */
	public function getMinHashCount() {
		return isset($this->minHashCount) ? $this->minHashCount : self::MIN_HASH_COUNT;
	}

	/**
	 * Checks whether a user's hashed password needs to be replaced with a new hash.
	 *
	 * This is typically called during the login process when the plain text
	 * password is available.  A new hash is needed when the desired iteration
	 * count has changed through a change in the variable $hashCount or
	 * HASH_COUNT or if the user's password hash was generated in an bulk update
	 * with class ext_update.
	 *
	 * @access  public
	 * @param   string   $passString  salted hash to check if it needs an update
	 * @return  boolean	              true if salted hash needs an update,
	 *                                otherwise false
	 */
	public function isHashUpdateNeeded($passString) {
			// Check whether this was an updated password.
		if ((strncmp($passString, '$P$', 3)) || (strlen($passString) != 34)) {
			return true;
		}
			// Check whether the iteration count used differs from the standard number.
		return ($this->getCountLog2($passString) < $this->getHashCount());
	}

	/**
	 * Method sets log2 number of iterations for password stretching.
	 *
	 * @param  integer  $hashCount  log2 number of iterations for password stretching to set
	 * @see                         HASH_COUNT
	 * @see                         $hashCount
	 * @see                         getHashCount()
	 */
	public function setHashCount($hashCount = null) {
		$this->hashCount = isset($hashCount) ? intval($hashCount) : self::HASH_COUNT;
	}

	/**
	 * Method sets minimum allowed log2 number of iterations for password stretching.
	 *
	 * @param  integer  $minHashCount  minimum allowed log2 number of iterations
	 *                                 for password stretching to set
	 * @see                            MIN_HASH_COUNT
	 * @see                            $minHashCount
	 * @see                            getMinHashCount()
	 */
	public function setMinHashCount($minHashCount = null) {
		$this->minHashCount = isset($minHashCount) ? intval($minHashCount) : self::MIN_HASH_COUNT;
	}

	/**
	 * Method sets maximum allowed log2 number of iterations for password stretching.
	 *
	 * @param  integer  $maxHashCount  maximum allowed log2 number of iterations
	 *                                 for password stretching to set
	 * @see                            MAX_HASH_COUNT
	 * @see                            $maxHashCount
	 * @see                            getMaxHashCount()
	 */
	public function setMaxHashCount($maxHashCount = null) {
		$this->maxHashCount = isset($maxHashCount) ? $maxHashCount : self::MAX_HASH_COUNT;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php']);
}
?>