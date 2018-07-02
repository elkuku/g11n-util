<?php
/**
 * Date: 27/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil\Command;

use ElKuKu\G11n\Language\Storage;
use ElKuKu\G11n\Support\ExtensionHelper;
use ElKuKu\G11nUtil\G11nUtil;
use ElKuKu\G11nUtil\Type\LanguageFileType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MakeLangfilesCommand
 * @since 1.0
 */
class MakeLangfilesCommand extends Command
{
	/**
	 * @var LanguageFileType
	 */
	private $languageFile;

	/**
	 * Configures the current command.
	 * @return void`
	 */
	protected function configure(): void
	{
		$this
			->setName('make-langfiles')
			->setDescription('Creates or updates language files.')
			->addArgument(
				'domainPath',
				InputArgument::REQUIRED
			)
			->addArgument(
				'extension',
				InputArgument::REQUIRED
			)
			->addArgument(
				'lang',
				InputArgument::REQUIRED
			)
			->addOption(
				'template-path',
				null,
				InputOption::VALUE_REQUIRED,
				'Full path and name of the template file.',
				''
			);
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
		$domain     = 'domain';
		$domainPath = realpath($input->getArgument('domainPath'));

		if (false === is_dir($domainPath))
		{
			throw new \UnexpectedValueException('Invalid domain path');
		}

		ExtensionHelper::addDomainPath($domain, $domainPath);

		$extension = $input->getArgument('extension');
		$lang      = $input->getArgument('lang');

		$templatePath = $input->getOption('template-path') ?: Storage::getTemplatePath($extension, $domain);

		$this->languageFile = (new LanguageFileType)
			->setExtension($extension)
			->setDomain($domain)
			->setLang($lang)
			->setTemplatePath($templatePath);
	}

	/**
	 * @param   InputInterface  $input  The input
	 * @param   OutputInterface $output The output
	 *
	 * @return null|integer null or 0 if everything went fine, or an error code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): ?int
	{
		$io = new SymfonyStyle($input, $output);

		$io->title('Create language files');

		$output->writeln('Processing ' . $this->languageFile->extension);

		(new G11nUtil)
			->setVerbosity($output->getVerbosity())
			->processFiles($this->languageFile);

		$io->success('Language files have been created!');

		return 0;
	}
}
