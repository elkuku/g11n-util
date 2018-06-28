<?php
/**
 * Date: 27/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeTemplatesCommand
 * @since 1.0
 */
class MakeTemplatesCommand extends Command
{
	/**
	 * Configures the current command.
	 * @return void
	 */
	protected function configure(): void
	{
		$this
			->setName('make-templates')
			->setDescription('Creates a new user.')
			->setHelp('This command allows you to create a user...');
	}

	/**
	 * @param   InputInterface  $input  The input
	 * @param   OutputInterface $output The output
	 *
	 * @return null|integer null or 0 if everything went fine, or an error code
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln(
			[
				'User Creator',
				'============',
				'',
			]
		);

		$output->writeln('Whoa!');

		$output->write('You are about to ');
		$output->write('create a user.');

		return 0;
	}
}
