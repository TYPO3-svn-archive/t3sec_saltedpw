<?php

require_once t3lib_extMgm::extPath('saltedpasswords', 'classes/salts/class.tx_saltedpasswords_salts_factory.php');

class tx_saltedpasswords_salts_factory_testcase extends tx_phpunit_testcase {


	/**
	 * Keeps instance of object to test.
	 * 
	 * @var tx_saltedpasswords_abstract_salts
	 */
	protected $objectInstance = null;

	protected function setUp() {
		$this->objectInstance = tx_saltedpasswords_salts_factory::getSaltingInstance();
	}

	protected function tearDown() {
		$this->objectInstance = null;
	}

	/**
	 * @test
	 */
	public function objectInstanceNotNull() {
		$this->assertNotNull($this->objectInstance);
	}

	/**
	 * @test
	 */
	public function objectInstanceExtendsAbstractClass() {
		$this->assertTrue(is_subclass_of($this->objectInstance, 'tx_saltedpasswords_abstract_salts'));
	}

	/**
	 * @test
	 */
	public function objectInstanceImplementsInterface() {
		$this->assertTrue(method_exists($this->objectInstance,'getSaltLength'), 'Missing method getSaltLength() from interface tx_saltedpasswords_salts.');
		$this->assertTrue(method_exists($this->objectInstance,'isValidSalt') , 'Missing method isValidSalt() from interface tx_saltedpasswords_salts.');
		$this->assertTrue(method_exists($this->objectInstance,'getHashedPassword'), 'Missing method getHashedPassword() from interface tx_saltedpasswords_salts.');
		$this->assertTrue(method_exists($this->objectInstance,'checkPassword'), 'Missing method checkPassword() from interface tx_saltedpasswords_salts.');
	}

	/**
	 * @test
	 */
	public function base64EncodeReturnsProperLength() {
			// 3 Bytes should result in a 6 char length base64 encoded string
			// used for MD5 and PHPass salted hashing
		$byteLength = 3;
		$reqLengthBase64 = intval(ceil(($byteLength * 8) / 6));
		$randomBytes = t3lib_div::generateRandomBytes($byteLength);
		$this->assertTrue(strlen($this->objectInstance->base64Encode($randomBytes, $byteLength)) == $reqLengthBase64);
		
			// 16 Bytes should result in a 22 char length base64 encoded string
			// used for Blowfish salted hashing
		$byteLength = 16;
		$reqLengthBase64 = intval(ceil(($byteLength * 8) / 6));
		$randomBytes = t3lib_div::generateRandomBytes($byteLength);
		$this->assertTrue(strlen($this->objectInstance->base64Encode($randomBytes, $byteLength)) == $reqLengthBase64);
	}

	/**
	 * @test
	 */
	public function objectInstanceForMD5Salts() {
		$saltMD5 = '$1$rasmusle$rISCgZzpwk3UhDidwXvin0';
		$this->objectInstance = tx_saltedpasswords_salts_factory::getSaltingInstance($saltMD5);
		
		$this->assertTrue((get_class($this->objectInstance) == 'tx_saltedpasswords_salts_md5') || (is_subclass_of($this->objectInstance, 'tx_saltedpasswords_salts_md5')) );
	}

	/**
	 * @test
	 */
	public function objectInstanceForBlowfishSalts() {
		$saltBlowfish = '$2a$07$abcdefghijklmnopqrstuuIdQV69PAxWYTgmnoGpe0Sk47GNS/9ZW';
		$this->objectInstance = tx_saltedpasswords_salts_factory::getSaltingInstance($saltBlowfish);
		$this->assertTrue((get_class($this->objectInstance) == 'tx_saltedpasswords_salts_blowfish') || (is_subclass_of($this->objectInstance, 'tx_saltedpasswords_salts_blowfish')) );
	}

	/**
	 * @test
	 */
	public function objectInstanceForPhpassSalts() {
		$saltPhpass = '$P$CWF13LlG/0UcAQFUjnnS4LOqyRW43c.';
		$this->objectInstance = tx_saltedpasswords_salts_factory::getSaltingInstance($saltPhpass);
		$this->assertTrue((get_class($this->objectInstance) == 'tx_saltedpasswords_salts_phpass') || (is_subclass_of($this->objectInstance, 'tx_saltedpasswords_salts_phpass')) );
	}
}
?>