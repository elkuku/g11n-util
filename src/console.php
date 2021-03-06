#!/usr/bin/env php
<?php
/**
 * Date: 28/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil;

/**
 * @param   string   $file  The file.
 *
 * @return boolean
 */
function includeIfExists(string $file): bool
{
	return file_exists($file) && include $file;
}

if (!includeIfExists(__DIR__ . '/../vendor/autoload.php')
	&& !includeIfExists(__DIR__ . '/../../../../vendor/autoload.php')
)
{
	fwrite(STDERR, 'Install dependencies using Composer.' . PHP_EOL);
	exit(1);
}

use ElKuKu\G11nUtil\Command\MakeLangfilesCommand;
use ElKuKu\G11nUtil\Command\MakeTemplatesCommand;
use Symfony\Component\Console\Application;

$application = new Application('G11n Utility', '1.0');

$application->add(new MakeTemplatesCommand);
$application->add(new MakeLangfilesCommand);

$application->run();
