<?php
namespace ETHAAE\Plugin\Console\ethaae\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;

class RunHelloCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'hello:world';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Hello World Command Title');

        // You might want to do some stuff here in Joomla

        $symfonyStyle->success('Hello World!');

        return 0;
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $this->setDescription('This command prints hello world to whoever calls it');
        $this->setHelp(
            <<<EOF
The <info>%command.name%</info> command prints hello world to whoever calls it
<info>php %command.full_name%</info>
EOF
        );
    }
}