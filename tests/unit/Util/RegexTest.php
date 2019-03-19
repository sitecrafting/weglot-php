<?php

use Weglot\Util\Regex;
use Weglot\Util\Regex\RegexEnum;

class RegexTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	* @param array $option
	* @return Regex
	*/
	protected function _regexInstance(array $option) {
		return new Regex(
			$option['type'],
			$option['value']
		);
	}

	public function testRegexStartWith() {
		$option = [
			'type'  => RegexEnum::START_WITH,
			'value' => 'http://',
		];

		$regex = $this->_regexInstance($option);

		$this->assertEquals('^http:\/\/', $regex->getRegex());
		$this->assertRegExp("#" . $regex->getRegex() . "#", 'http://');
	}

	public function testRegexEndWith() {
		$option = [
			'type'  => RegexEnum::END_WITH,
			'value' => 'http://',
		];

		$regex = $this->_regexInstance($option);

		$this->assertEquals('http:\/\/$', $regex->getRegex());
		$this->assertRegExp("#" . $regex->getRegex() . "#", 'test string http://');
	}

	public function testRegexContain() {
		$option = [
			'type'  => RegexEnum::CONTAIN,
			'value' => 'http://',
		];

		$regex = $this->_regexInstance($option);

		$this->assertEquals('http:\/\/', $regex->getRegex());
		$this->assertRegExp("#" . $regex->getRegex() . "#", 'test http:// string');
	}

	public function testRegexIsExactly() {
		$option = [
			'type'  => RegexEnum::IS_EXACTLY,
			'value' => 'http://',
		];

		$regex = $this->_regexInstance($option);

		$this->assertEquals('^http:\/\/$', $regex->getRegex());
		$this->assertRegExp("#" . $regex->getRegex() . "#", 'http://');
	}
	public function testMatchRegex() {
		$option = [
			'type'  => RegexEnum::MATCH_REGEX,
			'value' => '^http:\/\/',
		];

		$regex = $this->_regexInstance($option);

		$this->assertEquals('^http:\/\/', $regex->getRegex());
		$this->assertRegExp("#" . $regex->getRegex() . "#", 'http://weglot.com');
	}
}
