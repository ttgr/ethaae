<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace ETHAAE\Plugin\Console\Ethaae\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

class UpdateFilesCommand extends AbstractCommand
{
    protected static $defaultName = 'update:files';

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $symfonyStyle = new SymfonyStyle($input, $output);
        $params = ComponentHelper::getParams('com_ethaae_reports');
        //$upload_path = $params->get('upload_dir','').'/';
        $upload_path = '/Volumes/Data/wwwroot/ethaae-reports/files/';

        $symfonyStyle->title('Update Files');
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('vw_reports_count_files');
        $query->where('total_files > 0');
        $db->setQuery($query);
        $reports  = $db->loadObjectList();

        foreach ($reports as $report) {
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__ethaae_reports_files');
            $query->where('fk_report_id = '. $report->report_id);
            $db->setQuery($query);
            $files  = $db->loadObjectList();
            foreach ($files as $file) {
                $lang = explode('-',$file->language);
                $filename = $report->u_unitcode.'_'.$report->report_year.'_'.$file->fk_file_id.'_'.$lang[0].'_'.$this->sanitizeFilename($file->caption);
                if (!$this->createFolder($upload_path, $report->i_unitcode))  {
                    $symfonyStyle->error('Error Creating Folder:'. $upload_path.$report->i_unitcode);
                    $symfonyStyle->info(json_encode($file,JSON_UNESCAPED_UNICODE));
                }
                $file->new_path = $report->i_unitcode.'/'.$filename;
                $symfonyStyle->info($file->new_path);
                $this->updateNewPath($file->id,$file->new_path,$db,$symfonyStyle);
            }
        }
        return 0;

    }

    function updateNewPath(int $id, string $newPath, $db, SymfonyStyle $symfonyStyle): bool
    {
        try {
            // Create a new query
            $query = $db->getQuery(true);

            // Update statement
            $query->update($db->quoteName('#__ethaae_reports_files'))
                ->set($db->quoteName('new_path') . ' = ' . $db->quote($newPath))
                ->where($db->quoteName('id') . ' = ' . (int) $id);

            // Set the query and execute it
            $db->setQuery($query);
            $db->execute();

            return true; // Update succeeded

        } catch (Exception $e) {
            // Log the exception or handle it as needed
            $symfonyStyle->error('Failed to update the record: ' . $e->getMessage());
            return false; // Update failed
        }
    }


    function sanitizeFilename(string $filename): string
    {
        // Greek to Latin character mapping
        $greekToLatin = [
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => 'th',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'x', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'u', 'φ' => 'f', 'χ' => 'ch', 'ψ' => 'ps', 'ω' => 'o',
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => 'TH',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'X', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'U', 'Φ' => 'F', 'Χ' => 'CH', 'Ψ' => 'PS', 'Ω' => 'O',
        ];
        // Replace Greek characters with Latin
        $filename = strtr($filename, $greekToLatin);
        // Remove unsafe characters (keeping only alphanumeric, dash, underscore, dot, and space)
        $filename = preg_replace('/[^A-Za-z0-9\-\.\_\s]/', '', $filename);
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        // Reduce multiple underscores to a single one
        $filename = preg_replace('/_+/', '_', $filename);
        // Trim underscores and dots from beginning and end
        $filename = trim($filename, '._');
        return $filename;
    }

    /**
     * Creates a folder in the specified root path if it does not already exist.
     *
     * @param string $rootPath The root path where the folder should be created.
     * @param string $folderName The name of the folder to be created.
     * @param int $permissions The permissions to set on the folder (default: 0755).
     * @return bool Returns `true` if the folder exists or is successfully created, `false` on failure.
     */
    function createFolder(string $rootPath, string $folderName, int $permissions = 0755): bool
    {
        // Ensure the root path has a trailing slash
        if (substr($rootPath, -1) !== DIRECTORY_SEPARATOR) {
            $rootPath .= DIRECTORY_SEPARATOR;
        }

        // Full path for the folder
        $folderPath = $rootPath . $folderName;

        // Check if the folder already exists
        if (is_dir($folderPath)) {
            return true; // Folder already exists
        }

        // Try to create the folder
        if (mkdir($folderPath, $permissions, true)) {
            return true; // Folder created successfully
        }

        // Folder could not be created
        return false;
    }

    protected function configure(): void
    {
        $this->setDescription('This command modifies all files of the reports');
        $this->setHelp(
            <<<EOF
The <info>%command.name%</info> command prints hello world to whoever calls it
<info>php %command.full_name%</info>
EOF
        );
    }

}