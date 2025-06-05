<?php
namespace ETHAAE\Plugin\Console\Ethaae\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Factory;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\GetDataApiCommand;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\ImportReportsCommand;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\GetArticlesCommand;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\GetUsersCommand;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\UpdateFilesCommand;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\GetQDataCommand;

class EthaaeConsolePlugin extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            \Joomla\Application\ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    public function registerCommands(): void
    {
        $app = Factory::getApplication();
        $app->addCommand(new GetDataApiCommand());
        $app->addCommand(new ImportReportsCommand());
        $app->addCommand(new GetArticlesCommand());
        $app->addCommand(new GetUsersCommand());
        $app->addCommand(new UpdateFilesCommand());
        $app->addCommand(new GetQDataCommand());
    }

    public function getApiUrl() {
        return $this->params->get('api_url', '');
    }
}