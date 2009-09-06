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
}
?>