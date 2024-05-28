<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace ETHAAE\Plugin\Console\ethaae\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

class GetDataApiCommand extends AbstractCommand {

    protected static $defaultName = 'api:getdata';

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
        $db = Factory::getContainer()->get('DatabaseDriver');
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Get Data From Api');

        $plugin = PluginHelper::getPlugin('console', 'ethaae_cli');
        $plg_params = new Registry($plugin->params);
        $api_url = $plg_params->get('api_url','');

        $unit_types = $this->getUnitTypes($db);

        $items = $this->DoGetFunction($api_url);
        $insts = 0;
        $t = 0;
        $start = microtime(true);
        foreach ($items as $item) {
            $insts++;
            $symfonyStyle->info($item->id." - ".$item->title." - ".$item->unitTypeCode." - ".$item->unitCode);
            $url = $api_url.'/'.$item->id.'/Structure';
            $entries = $this->DoGetFunction($url);

            foreach ($entries->units as $entry) {
                $t++;
                $symfonyStyle->info($item->id." - ".$entry->title." - ".$entry->unitTypeCode." - ".$entry->unitCode);
                $entry->unit_type_id = ArrayHelper::getValue($unit_types,$entry->unitTypeCode,0);
                if (!$this->registerItem($db,$entry,$url)) {
                    $symfonyStyle->error('Error Importing Institute');
                    print_r($entry);die;
                }
            }

        }
        $time_elapsed_secs = microtime(true) - $start;
        $symfonyStyle->success($insts.' Institutes has successfully imported!!');
        $symfonyStyle->success($t.' Items has successfully imported!!');
        $symfonyStyle->success('Time Consumed: '.$time_elapsed_secs.' seconds');

        return 0;
    }

    protected function registerItem($db,$institute,$url = "") {

        $item = new \stdClass();
        $item->id = $institute->id;
        $item->uuid = $institute->uuid;
        $item->title_el = $institute->title;
        $item->title_en = $institute->titleEn;
//        $item->fk_intitute_type_id = getTableIdByName($db,'#__adip_institutes_types',$institute->InstituteType);
//        $item->fk_region_id = getTableIdByName($db,'#__adip_regions',$institute->Region);
        $item->ministrycode = $institute->ministryCode;
        $item->unittypecode = $institute->unitTypeCode;
        $item->status = $institute->status;
        $item->versionid = $institute->versionId;
        $item->instituteid = $institute->instituteId;
        $item->unitcode = $institute->unitCode;
        $item->parentunitid = $institute->parentUnitId;
        $item->establishmentdate = $institute->establishmentDate;
        $item->startupdate = $institute->startupDate;
        $item->ceasedate = $institute->ceaseDate;
        $item->abolitiondate = $institute->abolitionDate;
        $item->greek = $institute->greek;
        $item->notestablished = $institute->notEstablished;
        $item->unit_type_id = $institute->unit_type_id;

            $act = "";
            try {
                if  ($this->itemAlreadyExists($db, '#__ethaae_institutes_structure','unitcode',$item->unitcode) > 0) {
                    $act = "Update";
                    $db->updateObject('#__ethaae_institutes_structure', $item, 'id', true);
                } else {
                    $act = "Insert";
                    $db->insertObject('#__ethaae_institutes_structure', $item, 'id');
                }

                return true;
            } catch (\Exception $e) {
                file_put_contents(JPATH_SITE.'/logs/api_error.log', print_r($url,true).PHP_EOL , FILE_APPEND | LOCK_EX);
                file_put_contents(JPATH_SITE.'/logs/api_error.log', print_r($item,true).PHP_EOL , FILE_APPEND | LOCK_EX);
                file_put_contents(JPATH_SITE.'/logs/api_error.log', $e->getCode()." ".$e->getMessage().PHP_EOL , FILE_APPEND | LOCK_EX);
                //echo $act;
                return false;
            }
    }

    protected function DoGetFunction($url) {
    $headers = array("Content-Type: application/json; charset=UTF-8",
        "Accept: application/json");
    $req = curl_init();
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($req, CURLOPT_URL, $url);
    curl_setopt($req, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($req, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($req, CURLOPT_SSL_VERIFYHOST, FALSE);



    $resp = curl_exec($req);
    curl_close($req);
    $respObj = json_decode($resp);
    return $respObj;
}

    protected function itemAlreadyExists($db,$table,$field,$value) {
        $query = $db->getQuery(true);

        $query->select('id');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName($field)." = ".$db->quote($value));

// Reset the query using our newly populated query object.
        $db->setQuery($query);
        //echo $db->replacePrefix((string) $query)."\r\n";
        $id = $db->loadResult();

        return ($id > 0) ? $id : 0;
    }

    protected function getTableIdByName($db,$table, $name) {
        $id = itemAlreadyExists($db,$table,'name',$name);
        if ($id > 0) {
            return $id;
        } else {
            $obj = new stdClass();
            $db->id = 0;
            $obj->name = $name;
            $obj->state = 1;
            $db->insertObject($table, $obj, 'id');
            return $obj->id;
        }
        return 0;
    }

    protected function getUnitTypes($db) {
        $query = $db->getQuery(true);
        $query->select('id,code');
        $query->from('#__ethaae_unit_type');
        $db->setQuery($query);
        return $db->loadAssocList('code','id');

    }
    protected function getTableIdByCode($db,$table, $field,$code) {
        $id = itemAlreadyExists($db,$table,$field,$code);
        if ($id > 0) {
            return $id;
        }
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
        $this->setDescription('This command get all data from ETHAAE\'s API');
        $this->setHelp(
            <<<EOF
The <info>%command.name%</info> command prints hello world to whoever calls it
<info>php %command.full_name%</info>
EOF
        );
    }
}


?>