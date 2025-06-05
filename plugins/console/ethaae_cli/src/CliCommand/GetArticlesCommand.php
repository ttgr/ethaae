<?php
namespace ETHAAE\Plugin\Console\Ethaae\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Database\Exception\ExecutionFailureException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;
use Joomla\CMS\Helper\TagsHelper;

class GetArticlesCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'import:getarticles';

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
        //old to new categories
        $catMaps = [
                     2  => 2,
                     8 => 8,
                     11 => 11,
                     12 => 12,
                     13 => 13,
                     14 => 14,
                     15 => 15,
                     16 => 16,
                     17 => 17,
        ];

        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Update Articles from Old Site');

        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $contentTags = $this->getArticleTags($db);

        $items = $this->getItems();
        $totals = 0;
        $new = 0;
        $upd = 0;

        foreach ($items as $item) {
            $totals++;
            $rslt = $this->importArticle($item,$catMaps,$db,$app,$symfonyStyle,$contentTags);
            switch ($rslt) {
                case 1:
                    $upd++;
                    break;
                case 2:
                    $new++;
                    break;
                default:
            }
        }
        $symfonyStyle->success($totals." Total Articles to be imported");
        $symfonyStyle->success($new." New Articles");
        $symfonyStyle->success($upd." Old Articles");
        $symfonyStyle->success(count(get_object_vars($items)).' Successfully Updated');

        return 0;
    }


    protected function importArticle($item,$catMaps,$db,\Joomla\CMS\Application\ConsoleApplication $app,SymfonyStyle $symfonyStyle,$contentTags = array()) {
        $symfonyStyle->info($item->id." - ".$item->title." - ".$item->alias." - ".$item->cat->title);
        $existingID = $this->aliasExists($item,$db,$catMaps);
        if (intval($existingID) >0) {
            $tag = (isset($item->tags[0])) ? $item->tags[0] : null;
            $item->id = $existingID;
            $newArticleID = $this->createArticle($item,$catMaps,$db,$app,$tag,$contentTags);
            if (is_numeric($newArticleID)) {
                $symfonyStyle->success('Article Successfully Updated');
            } else {
                //$symfonyStyle->info($item->id." - ".$item->title." - ".$item->alias." - ".$item->cat->title);
                $symfonyStyle->note('Article Exists: '.$existingID);
                $symfonyStyle->error($newArticleID);
            }
            return 1;
        } else {
            $tag = (isset($item->tags[0])) ? $item->tags[0] : null;
            $symfonyStyle->info($item->id." - ".$item->title." - ".$item->language." - ".$item->cat->title);
            $symfonyStyle->caution('New Article'.$existingID);
            if (is_object($tag) && isset($tag->title)) {
                $symfonyStyle->info('Article Tag:'.$tag->id.' - '.$tag->title.' - '.$tag->path);
            }

            if (!array_key_exists($item->catid, $catMaps)) {
                $symfonyStyle->warning('Non Existing Mapping of the CatID:'.$item->catid);
                $symfonyStyle->error('Unable to Create Article');
            } else {
                $item->id = 0;
                if (($newArticleID = $this->createArticle($item,$catMaps,$db,$app,$tag,$contentTags)) && is_numeric($newArticleID) ) {
                    $symfonyStyle->success('Article Successfully Created');
                } else {
                    $symfonyStyle->error('Unable to Create the EN Article');
                    $symfonyStyle->error($newArticleID);
                }
            }
            return 2;
        }
        return false;
    }


    protected function getArticleTags($db,$id = 0) {
    $query = $db
        ->getQuery(true)
        ->select(array('t.id','t.title','t.alias','t.path','t.language','t.created_user_id','t.parent_id','t.level'))
        ->from($db->quoteName('#__contentitem_tag_map','a'))
        ->join('LEFT',$db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('a.tag_id') . ' = ' . $db->quoteName('t.id'))
        ->where($db->quoteName('a.type_alias') . " = " . $db->quote('com_content.article'))
        ->where($db->quoteName('a.type_id') . " = " . $db->quote(1))
        ->where($db->quoteName('t.published') . " = " . $db->quote(1));
    if ($id > 0) {
        $query->where($db->quoteName('a.content_item_id') . " = ". $db->quote($id));
    }
    $db->setQuery($query);
    return $db->loadAssocList('id');
}

    protected function addAssociations($associations,$db) {

        $query = $db->getQuery(true)
            ->delete('#__associations')
            ->where('context =' . $db->quote('com_content.item'))
            ->where('id IN (' . implode(',', $associations) . ')');
        $db->setQuery($query);
        $db->execute();
//        echo $db->replacePrefix((string) $query);

        if (count($associations) > 1)
        {
            $query = $db->getQuery(true);
            // Adding new association for these items
            $key = md5(json_encode($associations));
            $query->clear()
                ->insert('#__associations');

            foreach ($associations as $id)
            {
                $query->values($id . ',' . $db->quote('com_content.item') . ',' . $db->quote($key));
            }
            //echo $db->replacePrefix((string) $query)." \r\n";
            $db->setQuery($query);
            $db->execute();
        }
        return true;
    }

    protected function createArticle($item,$catMaps,$db,$app,$tag,$contentTags) {
        $article = $app->bootComponent('com_content')->getMVCFactory()->createTable('Article', 'Administrator',  ['dbo' => $db]);
        $data = [
            'id'               => $item->id,
            'title'            => $item->title ,
            'alias'            => $item->alias,
            'introtext'        => $item->introtext,
            'fulltext'         => '',
            'images'           => $item->images,
            'urls'             => $item->urls,
            'created'          => $item->created,
            'created_by'       => $item->created_by,
            'created_by_alias' => '',
            'publish_up'       => $item->publish_up,
            'publish_down'     => null,
            'version'          => $item->version,
            'catid'            => $catMaps[$item->catid],
            'metadata'         => $item->metadata,
            'metakey'          => '',
            'metadesc'         => '',
            'language'         => $item->language,
            'state'            => 1,
            'featured'         => $item->featured,
            'attribs'          => $item->attribs,
            'rules'            => [],
            'hits'             => $item->hits,
            'access'           => $item->access,
            'note'             => $item->note,

        ];

        if($item->id > 0) {
            $data['ordering'] = $item->ordering;
        }

        // Bind the data to the table
        if (!$article->bind($data)) {
            return "Binding Data: ".$article->getError();
        }

        // Check to make sure our data is valid.
        if (!$article->check()) {
            return "Checking Data: ".$article->getError();
        }

        // Now store the category.
        if (!$article->store(true)) {
            //print_r($data);die;
            return "Storing Data: ".$article->getError();
        }
        $newId = $article->get('id');

        if ($item->id  == 0)
        {
            try {
                $this->createWorkflowAssociation($newId, $db);
            } catch (ExecutionFailureException $e) {
                return $e->getMessage();
            }
        }
        if (isset($tag->id) && array_key_exists($tag->id,$contentTags)) {

            $tagsHelper = new TagsHelper();
            $tagsHelper->typeAlias = "com_content.article";
            $ucmId = $this->getumcID($newId,$db);
            try {
                $tagsHelper->tagItem($ucmId, $article, [$tag->id], true);
            } catch (ExecutionFailureException $e) {
                return $e->getMessage();
            }
        }

        // Get the new item ID.
        return $newId;
    }

    protected function getumcID($newId,$db) {
        $query = $db
            ->getQuery(true)
            ->select(array('core_content_id'))
            ->from($db->quoteName('#__ucm_content'))
            ->where($db->quoteName('core_type_alias') . " = ". $db->quote('com_content.article'))
            ->where($db->quoteName('core_content_item_id') . " = ". $db->quote($newId));
        $db->setQuery($query);
        return $db->loadResult();
    }

    protected function createWorkflowAssociation($articleID,$db) {
        $columns = array('item_id', 'stage_id', 'extension');
        $values = array($articleID, 1, $db->quote('com_content.article'));

        $query = $db
            ->getQuery(true)
            ->insert($db->quoteName('#__workflow_associations'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();
    }

    protected function aliasExists($item,$db,$catMaps) {
        $query = $db
            ->getQuery(true)
            ->select(array('id'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('alias') . " = ". $db->quote($item->alias))
            ->where($db->quoteName('catid') . " = ". $db->quote($catMaps[$item->catid]));
        $db->setQuery($query);
        return $db->loadResult();
    }
    protected function getItems() {

        $curl = curl_init();

        $posts = array();
//        $posts['token'] = '867164f011b0f8c633584819071ccfa7';
//        $posts['service'] = 'newsletter';


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.ethaae.gr/tasks/getContent.php",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $posts,
            CURLOPT_HTTPHEADER => array(
                "au: "
            ),
        ));

        $resultat = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            echo 'Curl Error: ' . $err;
        } else {
            $response = json_decode($resultat);
        }
        curl_close($curl);
        return $response;
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