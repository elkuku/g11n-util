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
 * @since 1.0
 */
class G11nUtilTest extends TestCase
{
	/**
	 * @var G11nUtil
	 */
	private $g11nUtil;

	/**
	 * This method is called before each test.
	 * @return void
	 */
	protected function setUp(): void
	{
		$this->g11nUtil = new G11nUtil;
	}

	/**
	 * @return void
	 */
	public function testCheckRequirements(): void
	{
		$value = $this->g11nUtil->checkRequirements();

		$this->assertArrayHasKey('xgettext', $value);
		$this->assertNotEmpty($value['xgettext']);
		$this->assertContains('xgettext', $value['xgettext']);
	}

	/**
	 * @return void
	 */
	public function testVerbosity0(): void
	{
		ob_start();
		$this->g11nUtil->checkRequirements();
		$output = ob_get_clean();
		$this->assertEmpty($output);
	}

	/**
	 * @return void
	 */
	public function testVerbosity1(): void
	{
		$this->g11nUtil->setVerbosity(G11nUtil::VERBOSITY_VERBOSE);
		ob_start();
		$this->g11nUtil->checkRequirements();
		$output = ob_get_clean();
		$this->assertNotEmpty($output);
	}

	/**
	 * @return void
	 */
	public function testVerbosity2(): void
	{
		$this->g11nUtil->setVerbosity(G11nUtil::VERBOSITY_VERY_VERBOSE);
		ob_start();
		$this->g11nUtil->checkRequirements();
		$output = ob_get_clean();
		$this->assertNotEmpty($output);
	}
}
