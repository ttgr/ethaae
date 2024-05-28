<?php
namespace ETHAAE\Plugin\Console\Ethaae\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Factory;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\RunHelloCommand;
use ETHAAE\Plugin\Console\Ethaae\CliCommand\GetDataApiCommand;

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
        $app->addCommand(new RunHelloCommand());
        $app->addCommand(new GetDataApiCommand());
    }

    public function getApiUrl() {
        return $this->params->get('api_url', '');
    }
}