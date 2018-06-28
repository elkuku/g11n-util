<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 28/06/18
 * Time: 11:22
 *
 * @copyright 2018 Nikolai Plath
 * @license http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil;

use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class G11nUtil
 * @since 1.0
 */
class G11nUtil
{
	/**
	 * Compile twig templates to PHP.
	 *
	 * @param   string  $rootDir        The root diectory.
	 * @param   string  $twigDir        Path to twig templates.
	 * @param   string  $cacheDir       Path to cache dir.
	 * @param   array   $twigExtensions Array with twig extensions to add.
	 * @param   boolean $recursive      Scan the directory recursively.
	 *
	 * @return  $this
	 */
	protected function makePhpFromTwig(string $rootDir, string $twigDir, string $cacheDir, array $twigExtensions, bool $recursive = false): self
	{
		$loader = new Twig_Loader_Filesystem([$rootDir, $twigDir]);

		// Force auto-reload to always have the latest version of the template
		$twig = new Twig_Environment(
			$loader,
			[
				'cache'       => $cacheDir,
				'auto_reload' => true,
			]
		);

		// Configure Twig the way you want
		foreach ($twigExtensions as $twigExtension)
		{
			$twig->addExtension($twigExtension);
		}

		// Iterate over all the templates
		if ($recursive)
		{
			/** @var \DirectoryIterator $file */
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($twigDir)) as $file)
			{
				// Force compilation
				if ($file->isFile())
				{
					$twig->loadTemplate(str_replace($twigDir . '/', '', $file));
				}
			}
		}
		else
		{
			/** @var \DirectoryIterator $file */
			foreach (new \DirectoryIterator($twigDir) as $file)
			{
				// Force compilation
				if ($file->isFile())
				{
					$twig->loadTemplate(str_replace($twigDir . '/', '', $file));
				}
			}
		}

		return $this;
	}

	/**
	 * Replace a compiled twig template path with the real path.
	 *
	 * @param   string $sourcePath   Path to the twig sources.
	 * @param   string $twigPath     Path to the compiled twig files.
	 * @param   string $templateFile Path to the template file.
	 * @param   string $replacePath  Path to replace
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function replaceTwigPaths(string $sourcePath, string $twigPath, string $templateFile, string $replacePath): self
	{
		$pathMap = [];

		/** @var \DirectoryIterator $fileInfo */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($twigPath)) as $fileInfo)
		{
			if ('php' == $fileInfo->getExtension())
			{
				$f = new \stdClass;

				$f->twigPhpPath = str_replace($replacePath, '', $fileInfo->getPathname());
				$f->lines = file($fileInfo->getPathname());

				if (false === isset($f->lines[2]) || false === preg_match('| ([A-z0-9\.\-\/]+)|', $f->lines[2], $matches))
				{
					throw new \RuntimeException('Can not parse the twig template at: ' . $fileInfo->getPathname());
				}

				$f->twigTwigPath = str_replace($replacePath, '', $sourcePath) . '/' . $matches[1];

				$pathMap[$f->twigPhpPath] = $f;
			}
		}

		$lines = file($templateFile);

		foreach ($lines as $cnt => $line)
		{
			if (preg_match('/#: ([A-z0-9\/\.]+):([0-9]+)/', $line, $matches))
			{
				$path = $matches[1];
				$lineNo = $matches[2];

				if (false === array_key_exists($path, $pathMap))
				{
					// Not a twig template
					continue;
				}

				$twigPhp = $pathMap[$path];

				$matches = null;

				for ($i = $lineNo - 2; $i >= 0; $i --)
				{
					$pLine = $twigPhp->lines[$i];

					if (preg_match('#// line ([0-9]+)#', $pLine, $matches))
					{
						break;
					}
				}

				if (!$matches)
				{
					throw new \RuntimeException('Can not fetch the line number in: ' . $line);
				}

				$lines[$cnt] = '#: ' . $twigPhp->twigTwigPath . ':' . $matches[1] . "\n";
			}
		}

		file_put_contents($templateFile, implode('', $lines));

		return $this;
	}
}
