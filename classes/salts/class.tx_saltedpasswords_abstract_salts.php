<?php

abstract class tx_saltedpasswords_abstract_salts {


	/**
	 * Returns a string for mapping an int to the corresponding base 64 character.
	 *
	 * @access  protected
	 * @return  string     string for mapping an int to the corresponding
	 *                     base 64 character
	 */
	abstract protected function getItoa64();

	/**
	 * Returns setting string to indicate type of hashing method.
	 * 
	 * @access  protected
	 * @return  string     setting string of hashing method
	 */
	abstract protected function getSetting();
	
	/**
	 * Generates a random base salt settings for the hash.
	 *
	 * @access  protected  
	 * @return  string     a string containing settings and a random salt
	 */
	abstract protected function getGeneratedSalt();

	abstract protected function applySettingsToSalt($salt);

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
}
?>