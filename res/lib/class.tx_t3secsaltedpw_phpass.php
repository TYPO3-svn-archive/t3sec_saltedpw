<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2006 Solar Designer (solar at openwall.com)
*  (c) 2008      Dries Buytaert (dries at buytaert.net)
*  (c) 2008      Marcus Krause  (marcus#exp2008@t3sec.info)
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
 * Secure password hashing functions for user authentication.
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
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */

if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

/**
 * The standard log2 number of iterations for password stretching. This should
 * increased by 1 from time to time to counteract  increases in the speed and
 * power of computers available to crack the hashes.
 */
define('T3X_T3SECSALTEDPW_HASH_COUNT', 14);

/**
 * The minimum allowed log2 number of iterations for password stretching.
 */
define('T3X_T3SECSALTEDPW_MIN_HASH_COUNT', 7);

/**
 * The maximum allowed log2 number of iterations for password stretching.
 */
define('T3X_T3SECSALTEDPW_MAX_HASH_COUNT', 30);

require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php';


class tx_t3secsaltedpw_phpass {

	protected $hashCount;

	protected $minHashCount;

	protected $maxHashCount;

	public function __construct( $hashCount = null ) {
		$this->setHashCount($hashCount);
		$this->setMinHashCount();
		$this->setMaxHashCount();
	}

	protected function getHashCount() {
		return isset($this->hashCount) ? $this->hashCount : T3X_T3SECSALTEDPW_HASH_COUNT;
	}

	protected function setHashCount($hashCount = null) {
		$this->hashCount = isset($hashCount) ? intval($hashCount) : T3X_T3SECSALTEDPW_HASH_COUNT;
	}

	protected function getMinHashCount() {
		return isset($this->minHashCount) ? $this->minHashCount : T3X_T3SECSALTEDPW_MIN_HASH_COUNT;
	}

	protected function setMinHashCount($minHashCount = null) {
		$this->minHashCount = isset($minHashCount) ? intval($minHashCount) : T3X_T3SECSALTEDPW_MIN_HASH_COUNT;
	}

	protected function getMaxHashCount() {
		return isset($this->maxHashCount) ? $this->maxHashCount : T3X_T3SECSALTEDPW_MAX_HASH_COUNT;
	}

	protected function setMaxHashCount($maxHashCount = null) {
		$this->maxHashCount = isset($maxHashCount) ? $maxHashCount : T3X_T3SECSALTEDPW_MAX_HASH_COUNT;
	}


	/**
	 * Check whether a plain text password matches a stored hashed password.
	 *
	 * Alternative implementations of this function may use other data in the
	 * $account object, for example the uid to look up the hash in a custom table
	 * or remote database.
	 *
	 * @param $plainPW
	 *   A plain-text password
	 * @param $saltedHashPW
	 *   A salted hashed password string
	 *
	 * @return
	 *   TRUE or FALSE.
	 */
	public function checkPassword($plainPW, $saltedHashPW) {
		$hash = $this->cryptPassword($plainPW, $saltedHashPW);
		return ($hash && $saltedHashPW == $hash);
	}

	/**
	 * Hash a password using a secure stretched hash.
	 *
	 * By using a salt and repeated hashing the password is "stretched". Its
	 * security is increased because it becomes much more computationally costly
	 * for an attacker to try to break the hash by brute-force computation of the
	 * hashes of a large number of plain-text words or strings to find a match.
	 *
	 * @param $password
	 *   The plain-text password to hash.
	 * @param $setting
	 *   An existing hash or the output of _password_generate_salt().
	 *
	 * @return
	 *   A string containing the hashed password (and salt) or FALSE on failure.
	 */
	protected function cryptPassword($password, $setting)  {
			// The first 12 characters of an existing hash are its setting string.
		$setting = substr($setting, 0, 12);

		if (substr($setting, 0, 3) != '$P$') {
			return FALSE;
		}
		$count_log2 = self::getCountLog2($setting);
			// Hashes may be imported from elsewhere, so we allow != T3X_T3SECSALTEDPW_HASH_COUNT
		if ($count_log2 < $this->getMinHashCount() || $count_log2 > $this->getMaxHashCount()) {
			return FALSE;
		}
		$salt = substr($setting, 4, 8);
			// Hashes must have an 8 character salt.
		if (strlen($salt) != 8) {
			return FALSE;
		}
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
		// _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
		return (strlen($output) == 34) ? $output : FALSE;
	}

	/**
	 * Parse the log2 iteration count from a stored hash or setting string.
	 */
	protected function getCountLog2($setting) {
		$itoa64 = $this->getItoa64();
		return strpos($itoa64, $setting[3]);
	}

	/**
	 * Returns a string for mapping an int to the corresponding base 64 character.
	 */
	protected function getItoa64() {
		return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	}

		/**
		 * Check whether a user's hashed password needs to be replaced with a new hash.
		 *
		 * This is typically called during the login process when the plain text
		 * password is available.  A new hash is needed when the desired iteration count
		 * has changed through a change in the variable password_count_log2 or
		 * T3X_T3SECSALTEDPW_HASH_COUNT or if the user's password hash was generated in an update
		 * like user_update_7000().
		 *
		 * Alternative implementations of this function might use other criteria based
		 * on the fields in $account.
		 *
		 * @param $account
		 *   A user object with at least the fields from the {users} table.
		 *
		 * @return
		 *   TRUE or FALSE.
		 */
		public function isHashUpdateNeeded($passString) {
				// Check whether this was an updated password.
			if ((substr($passString, 0, 3) != '$P$') || (strlen($passString) != 34)) {
				return true;
			}
				// Check whether the iteration count used differs from the standard number.
			return ($this->getCountLog2($passString) != T3X_T3SECSALTEDPW_HASH_COUNT);
		}

		/**
		 * Hash a password using a secure hash.
		 *
		 * @param $password
		 *   A plain-text password.
		 * @param $count_log2
		 *   Optional integer to specify the iteration count. Generally used only during
		 *   mass operations where a value less than the default is needed for speed.
		 *
		 * @return
		 *   A string containing the hashed password (and a salt), or FALSE on failure.
		 */
		public function getHashedPassword($password, $count_log2 = 0) {
			if (empty($count_log2)) {
					// Use the standard iteration count.
				$count_log2 = $this->getHashCount();
			}
			return $this->cryptPassword($password, $this->getGeneratedSalt($count_log2));
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
		 * @param $count_log2
		 *   Integer that determines the number of iterations used in the hashing
		 *   process. A larger value is more secure, but takes more time to complete.
		 *
		 * @return
		 *   A 12 character string containing the iteration count and a random salt.
		 */
		protected function getGeneratedSalt($count_log2) {
			$output = '$P$';
				// Minimum log2 iterations is T3X_T3SECSALTEDPW_MIN_HASH_COUNT.
			$count_log2 = max($count_log2, $this->getMinHashCount());
				// Maximum log2 iterations is T3X_T3SECSALTEDPW_MAX_HASH_COUNT.
				// We encode the final log2 iteration count in base 64.
			$itoa64 = $this->getItoa64();
			$output .= $itoa64[min($count_log2, $this->getMaxHashCount())];
				// 6 bytes is the standard salt for a portable phpass hash.
			$output .= $this->base64Encode(tx_t3secsaltedpw_div::generateRandomBytes(6), 6);
			return $output;
		}

		/**
		 * Encode bytes into printable base 64 using the *nix standard from crypt().
		 *
		 * @param $input
		 *   The string containing bytes to encode.
		 * @param $count
		 *   The number of characters (bytes) to encode.
		 *
		 * @return
		 *   Encoded string
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
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sec_saltedpw/res/lib/class.tx_t3secsaltedpw_phpass.php']);
}
?>