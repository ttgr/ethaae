<?php
namespace ETHAAE\Plugin\Console\Ethaae\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * GetQDataCommand class to fetch and process "QData".
 *
 * @since  4.0.0
 */
class GetQDataCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'api:getqdata';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  int  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Retrieve and Process QData');
        file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', "");

        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');

        $totalQData = 0;
        $processed = 0;
        $start = microtime(true);


        $plugin = PluginHelper::getPlugin('console', 'ethaae_cli');
        $plg_params = new Registry($plugin->params);

        //Import Metrics
        $api_metrics = $plg_params->get('api_qdata_base_url','').'/Metrics';
        try {
            $items = $this->fetchApiResponse($api_metrics, $symfonyStyle);
            foreach ($items as $item) {
                $this->registerMetrics($db,$item);
            }
        } catch (\Exception $e) {
            // Handle exceptions if any unexpected errors occur
            $symfonyStyle->error('Unexpected Error: ' . $e->getMessage());
        }

//        //Import Years Collections
        $api_metrics = $plg_params->get('api_qdata_base_url','').'/Collections';
        try {
            $items = $this->fetchApiResponse($api_metrics, $symfonyStyle);
            foreach ($items as $item) {
                $symfonyStyle->info('Importing Data for Collection Year : '.$item->collectionYear);
                $this->registerCollections($db,$item);
                $this->importCollectionQData($item->collectionYear,$db, $plg_params->get('api_qdata_base_url',''),$symfonyStyle);
            }
        } catch (\Exception $e) {
            // Handle exceptions if any unexpected errors occur
            $symfonyStyle->error('Unexpected Error: ' . $e->getMessage());
        }



        // Output summary
        $time_elapsed_secs = microtime(true) - $start;
        $symfonyStyle->success('Time Consumed: '.$time_elapsed_secs.' seconds');

        return 0;
    }

    /**
     * Fetch the QData items to be processed.
     *
     * @return  array  An array of QData items.
     */
    protected function getQDataItems(): array
    {
        // Example stub for fetching QData. Replace with real logic as needed.
        return [
            (object) ['id' => 1, 'title' => 'Data 1', 'alias' => 'data-1'],
            (object) ['id' => 2, 'title' => 'Data 2', 'alias' => 'data-2'],
        ];
    }


    protected function importCollectionQData($year, $db,string $api_url,SymfonyStyle $symfonyStyle) {
        //Import QData Values
        $apidata = $api_url.'/Metrics/CollectionYear/'.$year;
        try {
            $items = $this->fetchApiResponse($apidata, $symfonyStyle);
            $qItems = $items->data ?? [];
            $totalQData = count($qItems);
            $symfonyStyle->progressStart($totalQData);
            foreach ($qItems as $item) {
                $symfonyStyle->progressAdvance();
                if (!empty($item->collectionYear) && !empty($item->unitCode) && !empty($item->metricCode)) {
                    if (!$this->saveQData($item,$db)) {
                        $symfonyStyle->error('Error Saving QData: Check :' . JPATH_SITE.'/logs/api_qdata_error.log');
                    }
                    $processed++;
                }
            }
            $symfonyStyle->progressFinish();
        } catch (\Exception $e) {
            // Handle exceptions if any unexpected errors occur
            $symfonyStyle->error('Unexpected Error: ' . $e->getMessage());
        }

    }


    /**
     * Fetch JSON response from a given API URL with SymfonyStyle for error management.
     *
     * @param   string        $url         The API URL to fetch data from.
     * @param   SymfonyStyle  $symfonyStyle  SymfonyStyle instance for displaying errors.
     *
     * @return  mixed|null                  The decoded JSON response or null in case of an error.
     */
    function fetchApiResponse(string $url, SymfonyStyle $symfonyStyle)
    {
        // Initialize cURL session
        $ch = curl_init();
        $headers = array("Content-Type: application/json; charset=UTF-8",
            "Accept: application/json");

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout in seconds
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);


        // Execute the cURL session and fetch the response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $errorMessage = 'cURL Error: ' . curl_error($ch);
            $symfonyStyle->error($errorMessage);
            curl_close($ch);
            return null;
        }

        // Check if the HTTP response code indicates failure
        if ($httpCode >= 400) {
            $errorMessage = "HTTP Error: Received status code $httpCode from API.";
            $symfonyStyle->error($errorMessage);
            curl_close($ch);
            return null;
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $decodedResponse = json_decode($response);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = 'JSON Decoding Error: ' . json_last_error_msg();
            $symfonyStyle->error($errorMessage);
            return null;
        }

        // Return the decoded response
        return $decodedResponse;
    }

    /**
     * Process a single QData item.
     *
     * @param   object                                $qDataItem     The item to process.
     * @param   \Joomla\Database\DatabaseDriver       $db            The database driver.
     * @param   \Joomla\CMS\Application\ConsoleApplication  $app   The console application.
     * @param   SymfonyStyle                          $symfonyStyle  Symfony style output for logging.
     *
     * @return  bool  True if processed successfully, false otherwise.
     */
    protected function processQDataItem($qDataItem, $db, $app, SymfonyStyle $symfonyStyle): bool
    {
        try {
            $symfonyStyle->info("Processing QData item: ID {$qDataItem->id}, Title: {$qDataItem->title}");

            // Add custom processing logic here.
            if ($this->qDataExists($qDataItem, $db)) {
                $symfonyStyle->warning("Skipping QData item (already exists): ID {$qDataItem->id}");
                return false;
            }

            // Example logic: Save or update the QData item (stubbed method)
            $this->saveQData($qDataItem, $db);
            $symfonyStyle->success("Successfully processed QData item: {$qDataItem->title}");

            return true;
        } catch (\Exception $e) {
            $symfonyStyle->error("Error processing QData item ID {$qDataItem->id}: " . $e->getMessage());
            return false;
        }
    }



    protected function registerMetrics($db,object $obj) {
        $item = new \stdClass();
        $item->id = $obj->id;
        $item->code = $obj->code;
        $item->title_el = $obj->title;
        $item->description_el = $obj->description;
        $item->title_en = $obj->titleEn;
        $item->description_en = $obj->descriptionEn;
        $item->datatype = $obj->dataType;
        $item->scope  = $obj->scope;

        try {
            if  ($this->itemAlreadyExists($db, '#__ethaae_qdata_metrics','id',$item->id) > 0) {
                $act = "Update";
                //$db->updateObject('#__ethaae_qdata_metrics', $item, 'id', true);
            } else {
                $act = "Insert";
                $db->insertObject('#__ethaae_qdata_metrics', $item, 'id');
            }
            return true;
        } catch (\Exception $e) {
            file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', print_r($item,true).PHP_EOL , FILE_APPEND | LOCK_EX);
            file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', $e->getCode()." ".$e->getMessage().PHP_EOL , FILE_APPEND | LOCK_EX);
            //echo $act;
            return false;
        }
    }
    protected function registerCollections($db,object $obj) {
        $item = new \stdClass();
        $item->collection_year = $obj->collectionYear;
        $item->academic_reference_year  = $obj->academicReferenceYear;
        $item->fiscal_reference_year  = $obj->fiscalReferenceYear;
        $item->public = 1;
        try {
            if  ($this->itemAlreadyExists($db, '#__ethaae_qdata_collections','collection_year',$item->collection_year) > 0) {
                $act = "Update";
                $db->updateObject('#__ethaae_qdata_collections', $item, 'collection_year', true);
            } else {
                $act = "Insert";
                $db->insertObject('#__ethaae_qdata_collections', $item, 'collection_year');
            }
            return true;
        } catch (\Exception $e) {
            file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', print_r($item,true).PHP_EOL , FILE_APPEND | LOCK_EX);
            file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', $e->getCode()." ".$e->getMessage().PHP_EOL , FILE_APPEND | LOCK_EX);
            //echo $act;
            return false;
        }
    }



    protected function itemAlreadyExists($db,$table,$field,$value) {
        $query = $db->getQuery(true);
        $query->select('count(*)');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName($field)." = ".$db->quote($value));

        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        //echo $db->replacePrefix((string) $query)."\r\n";
        $total = $db->loadResult();

        return ($total > 0);
    }



    /**
     * Save the QData item to the database.
     *
     * @param   object                                $qDataItem  The QData item.
     * @param   \Joomla\Database\DatabaseDriver       $db         The database driver.
     *
     * @return  bool
     */
    protected function saveQData(object $qDataItem, $db): bool
    {
        $item = new \stdClass();
        $item->collectionyear = $qDataItem->collectionYear;
        $item->unitcode     = $qDataItem->unitCode;
        $item->unitid       = $this->getTablePK($db,'#__ethaae_institutes_structure','id','unitcode',$qDataItem->unitCode);
        $item->institutecode = $qDataItem->instituteCode;
        $item->instituteid  = $this->getTablePK($db,'#__ethaae_institutes_structure','id','unitcode',$qDataItem->instituteCode);
        $item->metricid     = $this->getTablePK($db,'#__ethaae_qdata_metrics','id','code',$qDataItem->metricCode);
        $item->metrictype   = $this->getTablePK($db,'#__ethaae_qdata_metrics','datatype','code',$qDataItem->metricCode);
        $item->metricscope  = $this->getTablePK($db,'#__ethaae_qdata_metrics','scope','code',$qDataItem->metricCode);
        $item->value        = (empty($qDataItem->value)) ? 0 : $qDataItem->value;
        $item->metriccode   = $qDataItem->metricCode;
        $item->id = $this->qDataAlreadyExists($db, $item);
        try {

            if  ( $item->id > 0) {
                $act = "Update";
                $db->updateObject('#__ethaae_qdata_data', $item, 'id', true);
            } else {
                $act = "Insert";
                $db->insertObject('#__ethaae_qdata_data', $item, 'id');
            }
            return true;
        } catch (\Exception $e) {
            file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', print_r($item,true).PHP_EOL , FILE_APPEND | LOCK_EX);
            file_put_contents(JPATH_SITE.'/logs/api_qdata_error.log', $e->getCode()." ".$e->getMessage().PHP_EOL , FILE_APPEND | LOCK_EX);
            //echo $act;
            return false;
        }


    }

    protected function qDataAlreadyExists($db,object $item) {
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from($db->quoteName('#__ethaae_qdata_data'));
        $query->where($db->quoteName('collectionyear')." = ".$db->quote($item->collectionyear));
        $query->where($db->quoteName('unitid')." = ".$db->quote($item->unitid));
        $query->where($db->quoteName('metricid')." = ".$db->quote($item->metricid));

        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        //echo $db->replacePrefix((string) $query)."\r\n";
        $id = $db->loadResult();
        return ($id > 0) ? $id : 0;
    }

    protected function getTablePK($db,string $table,string $pk,string $field, string $value) {
        $query = $db->getQuery(true);
        $query->select($pk);
        $query->from($db->quoteName($table));
        $query->where($db->quoteName($field)." = ".$db->quote($value));

        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        //echo $db->replacePrefix((string) $query)."\r\n";
        $id = $db->loadResult();

        return ($id > 0) ? $id : 0;

    }
}