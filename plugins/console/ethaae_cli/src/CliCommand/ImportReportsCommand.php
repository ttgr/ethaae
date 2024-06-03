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

class ImportReportsCommand extends AbstractCommand
{

    protected static $defaultName = 'import:reports';

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

        $symfonyStyle->title('Import Old Reports');

        $query = $db->getQuery(true);
        $query->select('id,unitcode');
        $query->from('#__ethaae_all_units');
        $db->setQuery($query);
        $institutes  = $db->loadAssocList('unitcode','id');

        $query = $db->getQuery(true);
        $query->select('id,name');
        $query->from('#__ethaae_report_types');
        $db->setQuery($query);
        $reportTypes  = $db->loadAssocList('id','name');

        $this->truncateTable('tmp_reports_not_found',$db);
        $this->truncateTable('#__ethaae_reports',$db);
        $this->truncateTable('#__ethaae_reports_files',$db);


        $query = $db->getQuery(true) ;
        $query->select('*');
        $query->from('vw_all_reports');
        $query->order('id ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        file_put_contents(JPATH_SITE.'/logs/import_reports.log', print_r("",true));
        foreach ($items as $item) {
            $report = new \StdClass;
            switch ($item->fk_reporttype_id) {
                case 3:
                case 1:
                    $report->fk_unit_id = ArrayHelper::getValue($institutes,$item->i_hqacode,0);
                    $report->title = $item->i_title;
                    break;
                case 2:
                    $report->fk_unit_id = ArrayHelper::getValue($institutes,$item->d_hqacode,0);
                    $report->title = $item->p_title;
                    break;
                case 4:
                    $report->fk_unit_id = ArrayHelper::getValue($institutes,$item->p_hqacode,0);
                    $report->title = $item->d_title;
                    break;
                default:
                    $symfonyStyle->error('Error Report Type ID');
                    $symfonyStyle->info(json_encode($item,JSON_UNESCAPED_UNICODE));
                    break;
            }
            $item->reportType = ArrayHelper::getValue($reportTypes,$item->fk_reporttype_id,'');
            if ($report->fk_unit_id == 0) {
                $symfonyStyle->warning('OLD Unit ID not found in the new DB');
                //file_put_contents(JPATH_SITE.'/logs/import_reports.log', print_r(json_encode($item,JSON_UNESCAPED_UNICODE),true).PHP_EOL , FILE_APPEND | LOCK_EX);
                $db->insertObject('tmp_reports_not_found', $item, 'id');
            } else {
                $report->fk_institute_id = ArrayHelper::getValue($institutes,$item->i_hqacode,0);
                $report->id = $item->id;
                $report->ordering = $item->ordering;
                $report->state = $item->state;
                $report->created_by = $item->created_by;
                $report->modified_by = $item->modified_by;
                $report->created = $item->created;
                $report->modified = $item->modified;
                $report->fk_reporttype_id = $item->fk_reporttype_id;
                $report->academic_greek = $item->academic_greek;
                $report->session_num = $item->session_num;
                $report->report_year = $item->report_year;
                $report->session_date = $item->session_date;
                $report->valid_from = $item->valid_from;
                $report->valid_to = $item->valid_to;
                $report->params = [
                    "fk_reporttype_id" => $item->fk_reporttype_id,
                    "fk_institute_id" => ArrayHelper::getValue($institutes,$item->i_hqacode,0),
                    "fk_deprtement_id" => ArrayHelper::getValue($institutes,$item->d_hqacode,0),
                    "fk_programme_id" => ArrayHelper::getValue($institutes,$item->p_hqacode,0),
                    "fk_other_unit_id" => 0,
                ];
                $report->params = json_encode($report->params,JSON_UNESCAPED_UNICODE);
                try {
                    $db->insertObject('#__ethaae_reports', $report, 'id');
                    $this->importFiles($symfonyStyle,$db,$report);
                } catch (\Exception $e) {
                    $symfonyStyle->error('Error Importing Report');
                    file_put_contents(JPATH_SITE.'/logs/import_reports.log', print_r(json_encode($report,JSON_UNESCAPED_UNICODE),true).PHP_EOL , FILE_APPEND | LOCK_EX);
                    file_put_contents(JPATH_SITE.'/logs/import_reports.log', $e->getCode()." ".$e->getMessage().PHP_EOL , FILE_APPEND | LOCK_EX);
                }

            }

        }

        return 0;
    }


    protected function importFiles(SymfonyStyle $symfonyStyle,$db,$report) {

        $query = "INSERT INTO #__ethaae_reports_files (fk_report_id,rid,type,name,path,size,ordering,caption,fk_file_id,language,hits)
                SELECT item_id as fk_report_id,rid,type,name,path,size,ordering,caption,IF(image = '' OR image = 'null',1,image) as fk_file_id,language,hits FROM `#__adip_attachments_files` where item_id = ".$db->quote($report->id);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (\Exception $e) {
            $symfonyStyle->error('Error Importing Reports Attachments');
            file_put_contents(JPATH_SITE.'/logs/import_reports.log', print_r(json_encode($report,JSON_UNESCAPED_UNICODE),true).PHP_EOL , FILE_APPEND | LOCK_EX);
            file_put_contents(JPATH_SITE.'/logs/import_reports.log', print_r($db->replacePrefix((string) $query),true).PHP_EOL , FILE_APPEND | LOCK_EX);
            file_put_contents(JPATH_SITE.'/logs/import_reports.log', $e->getCode()." ".$e->getMessage().PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }

    protected function truncateTable($table,$db) {
        $query = "TRUNCATE TABLE ".$table;
        $db->setQuery($query);
        $db->execute();
    }


}
