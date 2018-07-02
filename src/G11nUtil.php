<?php
/**
 * Date: 28/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil;

use ElKuKu\G11n\G11n;
use ElKuKu\G11n\Language\Storage;
use ElKuKu\G11n\Support\ExtensionHelper;
use ElKuKu\G11n\Support\FileInfo;
use ElKuKu\G11n\Support\TransInfo;
use ElKuKu\G11nUtil\Exception\G11nUtilityException;
use ElKuKu\G11nUtil\Type\LanguageFileType;
use ElKuKu\G11nUtil\Type\LanguageTemplateType;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class G11nUtil
 * @since 1.0
 */
class G11nUtil
{
	public const VERBOSITY_VERBOSE = 64;

	public const VERBOSITY_VERY_VERBOSE = 128;

	/**
	 * @var integer
	 */
	private $verbosity = 0;

	/**
	 * @var array
	 */
	private $executables = [
		'xgettext' => '',
		'msginit'  => '',
		'msgmerge' => '',
	];

	/**
	 * Generate or update language file templates for an extension.
	 *
	 * @param LanguageTemplateType $template Various template infos
	 *
	 * @return  G11nUtil
	 */
	public function processTemplates(LanguageTemplateType $template): G11nUtil
	{
		$this->checkRequirements();

		if (!$template->packageName)
		{
			throw new G11nUtilityException('Please provide a package name');
		}

		$packageName = $template->packageName;

		$headerData = '';
		$headerData .= ' --copyright-holder="' . ($template->copyrightHolder ?: $packageName) . '"';
		$headerData .= ' --package-name="' . $packageName . '"';
		$headerData .= ' --package-version="' . $template->packageVersion . '"';

		// @$headerData .= ' --msgid-bugs-address="info@example.com"';

		$comments = ' --add-comments=TRANSLATORS:';
		$keywords = ' -k --keyword=g11n3t --keyword=g11n4t:1,2';
		$noWrap   = ' --no-wrap';

		// Always write an output file even if no message is defined.
		$forcePo = ' --force-po';

		// Sort output by file location.
		$sortByFile = ' --sort-by-file';

		$dirName    = \dirname($template->templatePath);
		$cleanFiles = [[]];
		$buildOpts  = '';

		if ('js' === $template->type)
		{
			$buildOpts .= ' -L python';
		}

		foreach ($template->paths as $base)
		{
			if (!is_dir($base . '/' . $template->extensionDir))
			{
				throw new G11nUtilityException('Invalid extension');
			}

			$cleanFiles[] = $this->getCleanFiles($base . '/' . $template->extensionDir, $template->type, $template->excludes);
		}

		$cleanFiles = array_merge(...$cleanFiles);

		if (!is_dir($dirName))
		{
			if (!mkdir($dirName, 0755, true) || !is_dir($dirName))
			{
				throw new G11nUtilityException('Can not create the language template folder');
			}
		}

		$subType = '';

		if (strpos($template->extension, '.'))
		{
			$subType = substr($template->extension, strpos($template->extension, '.') + 1);
		}

		if ($this->isVerbose())
		{
			echo sprintf('Found %d files', \count($cleanFiles)) . PHP_EOL;
		}

		if ('config' === $subType)
		{
			$this->processConfigFiles($cleanFiles, $template->templatePath);
		}
		else
		{
			$fileList = implode("\n", $cleanFiles);

			$command = $keywords
				. $buildOpts
				. ' -o ' . $template->templatePath
				. $forcePo
				. $noWrap
				. $sortByFile
				. $comments
				. $headerData;

			if ($this->isVeryVerbose())
			{
				echo $command . PHP_EOL;
			}

			ob_start();

			system('echo "' . $fileList . '" | ' . $this->executables['xgettext'] . ' ' . $command . ' -f - 2>&1');

			$result = ob_get_clean();

			if ($this->isVerbose())
			{
				echo $result . PHP_EOL;
			}
		}

		if (!file_exists($template->templatePath))
		{
			throw new G11nUtilityException('Could not create the template');
		}

		return $this;
	}

	/**
	 * Generate or update language files for an extension.
	 *
	 * @param LanguageFileType $languageFile
	 *
	 * @return  G11nUtil
	 */
	public function processFiles(LanguageFileType $languageFile): G11nUtil
	{
		$this->checkRequirements();

		$lang         = $languageFile->lang;
		$extension    = $languageFile->extension;
		$domain       = $languageFile->domain;
		$templateFile = $languageFile->templatePath ?: Storage::getTemplatePath($extension, $domain);

		$languageFile = ExtensionHelper::findLanguageFile($lang, $extension, $domain);

		if (false === $languageFile)
		{
			$scopePath     = ExtensionHelper::getDomainPath($domain);
			$extensionPath = ExtensionHelper::getExtensionLanguagePath($extension);

			$path = $scopePath . '/' . $extensionPath . '/' . $lang;

			if (!is_dir($path))
			{
				if (!mkdir($path, 0755, true) || !is_dir($path))
				{
					throw new G11nUtilityException('Can not create the language folder');
				}
			}

			$fileName = $lang . '.' . $extension . '.po';

			$options = [];

			$options[] = 'input=' . $templateFile;
			$options[] = 'output=' . $path . '/' . $fileName;
			$options[] = 'no-wrap';
			$options[] = 'locale=' . $lang;

			$cmd = 'msginit --' . implode(' --', $options) . ' 2>&1';

			if ($this->isVeryVerbose())
			{
				echo $cmd . PHP_EOL;
			}

			ob_start();

			system($cmd);

			$msg = ob_get_clean();

			if (!file_exists($templateFile))
			{
				throw new G11nUtilityException('Could not create the language file');
			}

			if ($this->isVerbose())
			{
				echo $msg . PHP_EOL;
			}
		}
		else
		{
			$options = [];

			$options[] = 'update';
			$options[] = 'backup=off';
			$options[] = 'no-fuzzy-matching';
			$options[] = 'verbose';
			$options[] = 'no-wrap';

			$paths   = [];
			$paths[] = $languageFile;
			$paths[] = $templateFile;

			$cmd = 'msgmerge --'
				. implode(' --', $options)
				. ' "' . implode('" "', $paths) . '"'
				. ' 2>&1';

			if ($this->isVeryVerbose())
			{
				echo $cmd . PHP_EOL;
			}

			ob_start();

			system($cmd);

			$msg = ob_get_clean();

			if ($msg && $this->isVerbose())
			{
				echo $msg . PHP_EOL;
			}
		}

		return $this;
	}

	/**
	 * Compile twig templates to PHP.
	 *
	 * @param   string  $rootDir        The root diectory.
	 * @param   string  $twigDir        Path to twig templates.
	 * @param   string  $cacheDir       Path to cache dir.
	 * @param   array   $twigExtensions Array with twig extensions to add.
	 * @param   boolean $recursive      Scan the directory recursively.
	 *
	 * @return  G11nUtil
	 */
	public function makePhpFromTwig(string $rootDir, string $twigDir, string $cacheDir, array $twigExtensions, bool $recursive = false): self
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
	 * @return  G11nUtil
	 */
	public function replaceTwigPaths(string $sourcePath, string $twigPath, string $templateFile, string $replacePath): self
	{
		$pathMap = [];

		/** @var \DirectoryIterator $fileInfo */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($twigPath)) as $fileInfo)
		{
			if ('php' === $fileInfo->getExtension())
			{
				$f = new \stdClass;

				$f->twigPhpPath = str_replace($replacePath, '', $fileInfo->getPathname());
				$f->lines       = file($fileInfo->getPathname());

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
				$path   = $matches[1];
				$lineNo = $matches[2];

				if (false === array_key_exists($path, $pathMap))
				{
					// Not a twig template
					continue;
				}

				$twigPhp = $pathMap[$path];

				$matches = null;

				for ($i = $lineNo - 2; $i >= 0; $i--)
				{
					$pLine = $twigPhp->lines[$i];

					if (preg_match('#// line ([0-9]+)#', $pLine, $matches))
					{
						break;
					}
				}

				if (!$matches)
				{
					throw new G11nUtilityException('Can not fetch the line number in: ' . $line);
				}

				$lines[$cnt] = '#: ' . $twigPhp->twigTwigPath . ':' . $matches[1] . "\n";
			}
		}

		if (false === file_put_contents($templateFile, implode('', $lines)))
		{
			throw new G11nUtilityException('Could not write the template file.');
		}

		return $this;
	}

	/**
	 * Check system requirements.
	 *
	 * @return array
	 * @throws G11nUtilityException
	 */
	public function checkRequirements(): array
	{
		foreach ($this->executables as $executable => $path)
		{
			if ($path)
			{
				continue;
			}

			$path = trim(shell_exec('which ' . $executable));

			if (!$path)
			{
				throw new G11nUtilityException("The '$executable' command has not been found on your system. Please install gettext.");
			}

			$this->executables[$executable] = $path;

			if ($this->isVerbose())
			{
				echo 'Requirements OK' . PHP_EOL;
			}

			if ($this->isVeryVerbose())
			{
				$output = [];

				exec($executable . ' --version', $output);

				if (isset($output[0]))
				{
					echo 'Found executable: ' . $output[0] . PHP_EOL;
				}
			}
		}

		return $this->executables;
	}

	/**
	 * @param int $verbosity
	 *
	 * @return G11nUtil
	 */
	public function setVerbosity(int $verbosity): G11nUtil
	{
		$this->verbosity = $verbosity;

		return $this;
	}

	/**
	 * Get the source files to process.
	 *
	 * @param   string $path     The base path.
	 * @param   string $search   The file extension to search for.
	 * @param   array  $excludes Files to exclude.
	 *
	 * @return  array
	 */
	private function getCleanFiles(string $path, string $search, array $excludes): array
	{
		$cleanFiles = [];

		/** @var \SplFileInfo $fileInfo */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $fileInfo)
		{
			if ($fileInfo->getExtension() !== $search)
			{
				continue;
			}

			$excluded = false;

			foreach ($excludes as $exclude)
			{
				if (false !== strpos($fileInfo->getPathname(), $exclude))
				{
					$excluded = true;
				}
			}

			if (!$excluded)
			{
				$cleanFiles[] = $fileInfo->getRealPath();
			}
		}

		return $cleanFiles;
	}

	/**
	 * Process config files in XML format.
	 *
	 * @param   array  $cleanFiles   Source files to process.
	 * @param   string $templatePath The path to store the template.
	 *
	 * @return  G11nUtil
	 */
	private function processConfigFiles(array $cleanFiles, string $templatePath): self
	{
		\defined('NL') || \define('NL', "\n");
		$parser    = G11n::getCodeParser('xml');
		$potParser = G11n::getLanguageParser('pot');

		$options = new \stdClass;

		$outFile = new FileInfo;

		foreach ($cleanFiles as $fileName)
		{
			$fileInfo = $parser->parse($fileName);

			if (!\count($fileInfo->strings))
			{
				continue;
			}

			$relPath = $fileName;

			foreach ($fileInfo->strings as $key => $strings)
			{
				foreach ($strings as $string)
				{
					if (array_key_exists($string, $outFile->strings))
					{
						if (strpos($outFile->strings[$string]->info, $relPath . ':' . $key) !== false)
						{
							continue;
						}

						$outFile->strings[$string]->info .= '#: ' . $relPath . ':' . $key . NL;

						continue;
					}

					$t       = new TransInfo;
					$t->info .= '#: ' . $relPath . ':' . $key . NL;

					$outFile->strings[$string] = $t;
				}
			}
		}

		$buffer = $potParser->generate($outFile, $options);

		if (!file_put_contents($templatePath, $buffer))
		{
			throw new G11nUtilityException('Unable to write the output file');
		}

		return $this;
	}

	/**
	 * Check verbosity level
	 * @return boolean
	 */
	private function isVerbose(): bool
	{
		return self::VERBOSITY_VERBOSE <= $this->verbosity;
	}

	/**
	 * Check verbosity level
	 * @return boolean
	 */
	private function isVeryVerbose(): bool
	{
		return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
	}
}
