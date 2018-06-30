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
use ElKuKu\G11nUtil\Type\LanguageTemplateType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MakeTemplatesCommand
 * @since 1.0
 */
class MakeTemplatesCommand extends Command
{
	/**
	 * @var LanguageTemplateType
	 */
	private $template;

	/**
	 * Configures the current command.
	 * @return void
	 */
	protected function configure(): void
	{
		$this
			->setName('make-templates')
			->setDescription('Creates or updates language template files.')
			->addArgument(
				'domainPath',
				InputArgument::REQUIRED
			)
			->addArgument(
				'extension',
				InputArgument::REQUIRED
			)
			->addOption(
				'package-name',
				null,
				InputOption::VALUE_REQUIRED,
				'The name of the package',
				'Package-Name'
			)
			->addOption(
				'package-version',
				null,
				InputOption::VALUE_REQUIRED,
				'The version number.',
				'1.0'
			)
			->addOption(
				'type',
				null,
				InputOption::VALUE_REQUIRED,
				'The file type (e.g. PHP).',
				'php'
			)
			->addOption(
				'excludes',
				null,
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'Directory and file excludes.',
				[]
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

		if (false == is_dir($domainPath))
		{
			throw new \UnexpectedValueException('Invalid domain path');
		}

		ExtensionHelper::addDomainPath($domain, $domainPath);

		$extension     = $input->getArgument('extension');
		$type          = $input->getOption('type');
		$packageName   = $input->getOption('package-name');
		$packageVrsion = $input->getOption('package-version');
		$excludes      = $input->getOption('excludes');

		$templatePath = Storage::getTemplatePath($extension, $domain);

		$paths = [ExtensionHelper::getDomainPath($domain)];

		$this->template = (new LanguageTemplateType)
			->setExtension($extension)
			->setExtensionDir(ExtensionHelper::getExtensionPath($extension))
			->setDomain($domain)
			->setType($type)
			->setPaths($paths)
			->setTemplatePath($templatePath)
			->setPackageName($packageName)
			->setPackageVersion($packageVrsion)
			->setExcludes($excludes);
	}

	/**
	 * @param   InputInterface  $input  The input
	 * @param   OutputInterface $output The output
	 *
	 * @return null|integer null or 0 if everything went fine, or an error code
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$io->title('Create language templates');

		$output->writeln('Processing ' . $this->template->packageName);

		(new G11nUtil)
			->setVerbosity($output->getVerbosity())
			->processTemplates($this->template);

		$io->success('Language templates have been created!');

		return 0;
	}
}
