<?php
/**
 * Date: 28/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil\Tests;


use ElKuKu\G11nUtil\G11nUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class G11nTest
 * @package ElKuKu\G11nUtil\Tests
 * @since 1.0
 */
class G11nTest extends TestCase
{
	/**
	 * @var G11nUtil
	 */
	private $g11nUtil;

	/**
	 * This method is called before each test.
	 * @return void
	 */
	protected function setUp()
	{
		$this->g11nUtil = new G11nUtil;

	}

	/**
	 * @throws \ElKuKu\G11nUtil\Exception\G11nUtilityException
	 * @return void
	 */
	public function testCheckRequirements()
	{
		$value = $this->g11nUtil->checkRequirements();

		$this->assertArrayHasKey('xgettext', $value);
		$this->assertNotEmpty($value['xgettext']);
		$this->assertContains('GNU gettext-tools', $value['xgettext']);
	}

	/**
	 * @throws \ElKuKu\G11nUtil\Exception\G11nUtilityException
	 * @return void
	 */
	public function testVerbosity0()
	{
		$this->g11nUtil->setVerbosity(0);
		ob_start();
		$this->g11nUtil->checkRequirements();
		$output = ob_get_clean();
		$this->assertEmpty($output);
	}

	/**
	 * @throws \ElKuKu\G11nUtil\Exception\G11nUtilityException
	 * @return void
	 */
	public function testVerbosity1()
	{
		$this->g11nUtil->setVerbosity(1);
		ob_start();
		$this->g11nUtil->checkRequirements();
		$output = ob_get_clean();
		$this->assertNotEmpty($output);
	}

	/**
	 * @throws \ElKuKu\G11nUtil\Exception\G11nUtilityException
	 * @return void
	 */
	public function testVerbosity2()
	{
		$this->g11nUtil->setVerbosity(2);
		ob_start();
		$this->g11nUtil->checkRequirements();
		$output = ob_get_clean();
		$this->assertNotEmpty($output);
	}
}
