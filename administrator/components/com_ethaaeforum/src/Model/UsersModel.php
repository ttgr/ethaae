<?php
/**
 * @version    CVS: 2.0.0
 * @package    Com_Ethaaeforum
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaeforum\Component\Ethaaeforum\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Ethaaeforum\Component\Ethaaeforum\Administrator\Helper\EthaaeforumHelper;

/**
 * Methods supporting a list of Users records.
 *
 * @since  2.0.0
 */
class UsersModel extends ListModel
{
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'username', 'a.username',
				'email', 'a.email',
				'registerDate', 'a.registerDate',
		'registerDate.from', 'registerDate.to',
				'lastvisitDate', 'a.lastvisitDate',
		'lastvisitDate.from', 'lastvisitDate.to',
				'block', 'a.block',
				'sendEmail', 'a.sendEmail',
				'activation', 'a.activation',
				'params', 'a.params',
				'lastResetTime', 'a.lastResetTime',
				'resetCount', 'a.resetCount',
				'otpKey', 'a.otpKey',
				'otep', 'a.otep',
				'requireReset', 'a.requireReset',
			);
		}

		parent::__construct($config);
	}


	

	

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState("a.id", "ASC");

		$context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string A store id.
	 *
	 * @since   2.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		
		return parent::getStoreId($id);
		
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__users` AS a');

        $query->join('LEFT', '#__user_usergroup_map AS ug ON ug.user_id = a.id');
        $query->where('ug.group_id = 2 ' );


        // Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.id LIKE ' . $search . '  OR  a.name LIKE ' . $search . '  OR  a.username LIKE ' . $search . '  OR  a.email LIKE ' . $search . ' )');
			}
		}
		

		// Filtering registerDate
		$filter_registerDate_from = $this->state->get("filter.registerDate.from");

		if ($filter_registerDate_from !== null && !empty($filter_registerDate_from))
		{
			$query->where("a.`registerDate` >= '".$db->escape($filter_registerDate_from)."'");
		}
		$filter_registerDate_to = $this->state->get("filter.registerDate.to");

		if ($filter_registerDate_to !== null  && !empty($filter_registerDate_to))
		{
			$query->where("a.`registerDate` <= '".$db->escape($filter_registerDate_to)."'");
		}

		// Filtering lastvisitDate
		$filter_lastvisitDate_from = $this->state->get("filter.lastvisitDate.from");

		if ($filter_lastvisitDate_from !== null && !empty($filter_lastvisitDate_from))
		{
			$query->where("a.`lastvisitDate` >= '".$db->escape($filter_lastvisitDate_from)."'");
		}
		$filter_lastvisitDate_to = $this->state->get("filter.lastvisitDate.to");

		if ($filter_lastvisitDate_to !== null  && !empty($filter_lastvisitDate_to))
		{
			$query->where("a.`lastvisitDate` <= '".$db->escape($filter_lastvisitDate_to)."'");
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "a.id");
		$orderDirn = $this->state->get('list.direction', "ASC");

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		

		return $items;
	}

    public function addUserToForum($pks)  {
        if (count($pks) > 0) {
            foreach ($pks as $userid) {
                $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userid);
                $usergroups = $user->getAuthorisedGroups() ;
                if (!in_array(11,$usergroups)) {
                    $this->removeUSerFromGroup($user->id,2);
                    $this->addUSerToGroup($user->id,11);
                    $this->sendNewUSerMail($user->name,$user->email);
                    Factory::getApplication()->enqueueMessage('Ο Χρήστης: '.$user->name .' προστέθηκε στο γκρουπ Forum Users. Στάλθηκε ενημέρωση στο: '.$user->email, 'notice');
                }
            }
        }
    }


    public function removeUSerFromGroup($user_id): void
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('user_id') . ' = '. $db->quote($user_id),
        );
        $query->delete($db->quoteName('#__user_usergroup_map'));
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }


    public function addUSerToGroup($user_id,$usergroupID): void
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $columns = array('user_id', 'group_id');
        $values = array($db->quote($user_id), $db->quote($usergroupID));
        $query->insert($db->quoteName('#__user_usergroup_map'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();
    }

    public static function sendNewUSerMail ($name,$email) : bool {

        $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
        $sender = [Factory::getApplication()->get('mailfrom'), Factory::getApplication()->get('fromname')];
        $mailer->setSender($sender);
        $mailer->addRecipient($email);

        $subject = 'ΕΘΑΑΕ - Forum: Ενεργοποίηση Λογαριασμού ';

        $mailer->setSubject($subject);

        $body  = "<p>Αγαπητέ Χρήστη: ".$name." </p>";
        $body  .= "<p>Ο Λογαριασμός σας στο Forum της ΕΘΑΑΕ Ενεργοποιήθηκε και πλέον έχετε πλήρη πρόσβαση</p>";
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);
        $send = $mailer->Send();

        if ($send !== true) {
            Factory::getApplication()->enqueueMessage("Error Sending mail to: ".$name." ".$email." :: Description: ", 'error');
            return false;
        }
        return true;
    }


}
