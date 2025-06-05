<?php
namespace ETHAAE\Plugin\Console\Ethaae\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\Exception\ExecutionFailureException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;

class GetUsersCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'import:users';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since 4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '256M');
        $start = microtime(true);
        clearstatcache();


        $symfonyStyle = new SymfonyStyle($input, $output);

        $db = Factory::getContainer()->get('DatabaseDriver');
        // You might want to do some stuff here in Joomla

        $items = array();
        $symfonyStyle->title('Getting Users from old Site');
        $items = $this->getItems();
        $symfonyStyle->title('Inserting Users to New Site');
        $cnt=0;
        $totals  = 0;
        foreach ($items as $item)
        {
            $totals++;
            $rslt = $this->userExists($db, $item);
            //$symfonyStyle->info($item->username . ' ' . $item->email);
            if ($rslt == 0)
            {
                $symfonyStyle->caution('New User: ' . $item->username . ' ' . $item->email);
                $this->createJoomlaUser($item, $db, $symfonyStyle);
                $cnt++;
            } else {
                //$symfonyStyle->error('User Already Exists: ' .$rslt.' '. $item->username . ' ' . $item->email);
            }

        }


        $symfonyStyle->info('Execution Time: ' . (microtime(true) - $start));
        $symfonyStyle->info('Memory consumed: ' . round(memory_get_usage() / 1024) . 'KB');
        $symfonyStyle->info('Peak usage: ' . round(memory_get_peak_usage() / 1024) . 'KB of memory');
        $symfonyStyle->success($cnt .' out of '. $totals . ' Successfully Created');
        return 0;
    }

    protected function getItems()
    {

        $curl = curl_init();

        $posts            = array();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://www.ethaae.gr/tasks/initUsers.php",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $posts,
            CURLOPT_HTTPHEADER     => array(
                "au: "
            ),
        ));

        $resultat = curl_exec($curl);
        $err      = curl_error($curl);

        if ($err)
        {
            echo 'Curl Error: ' . $err;
        }
        else
        {
            $response = json_decode($resultat);
        }
        curl_close($curl);

        return $response;
    }


    protected function userExists($db, $item)
    {

        $query = $db
            ->getQuery(true)
            ->select('count(id)')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('email') . " = " . $db->quote($item->email));
        $db->setQuery($query);
        //echo $db->replacePrefix((string) $query);
        return $db->loadResult();

    }

    protected function createJoomlaUser($data, $db, SymfonyStyle $symfonyStyle)
    {
        $query   = $db->getQuery(true);
        $columns = [
            'name',
            'username',
            'email',
            'block',
            'sendEmail',
            'password',
            'registerDate',
            'requireReset',
            'params',
        ];
        $values  = [
            $db->quote($data->name),
            $db->quote($data->username),
            $db->quote($data->email),
            $db->quote($data->block),
            $db->quote(1),
            $db->quote($data->password),
            $db->quote($data->registerDate),
            $db->quote($data->requireReset),
            $db->quote('{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":"","colorScheme":"","a11y_mono":"0","a11y_contrast":"0","a11y_highlight":"0","a11y_font":"0"}'),
        ];

        $query->insert($db->quoteName('#__users'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        try
        {
            $db->execute();
            $user_id = $db->insertid();
            foreach ($data->groups as $group) {
                $this->assignGroups($db, $user_id, 1,$symfonyStyle);
            }
        }
        catch (ExecutionFailureException $e)
        {
            $symfonyStyle->error('Unable to Import: '. $data->name.' '. $data->username . ' ' . $data->email);
            $symfonyStyle->error('Errors: ' . $e->getMessage());
        }
    }
    protected function assignGroups($db, int $userID, int $groupID, SymfonyStyle $symfonyStyle)
    {
        $query   = $db->getQuery(true);
        $columns = array('user_id', 'group_id');
        $values  = array($db->quote($userID), $db->quote($groupID));

        $query->insert($db->quoteName('#__user_usergroup_map'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        try
        {
            $db->execute();
        }
        catch (ExecutionFailureException $e)
        {
            $symfonyStyle->error('Unable to Assign Group ' . $groupID);
            $symfonyStyle->error('Errors: ' . $e->getMessage());
        }

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
        $this->setDescription('Importing All Users from Old Site');
        $this->setHelp(
            <<<EOF
The <info>%command.name%</info> command Imports All Users from Old Site
<info>php %command.full_name%</info>
EOF
        );
    }
}
