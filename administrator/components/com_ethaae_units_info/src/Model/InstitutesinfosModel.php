<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaunitsinfo\Component\Ethaae_units_info\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Ethaaunitsinfo\Component\Ethaae_units_info\Administrator\Helper\Ethaae_units_infoHelper;

/**
 * Methods supporting a list of Institutesinfos records.
 *
 * @since  1.1.0
 */
class InstitutesinfosModel extends ListModel
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
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created', 'a.created',
				'modified', 'a.modified',
				'fk_unit_id', 'a.fk_unit_id',
				'description_el', 'a.description_el',
				'url_en', 'a.url_en',
				'url_el', 'a.url_el',
				'description_en', 'a.description_en',
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
		parent::populateState('id', 'ASC');

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
	 * @since   1.1.0
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
	 * @since   1.1.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__ethaae_institutes_info` AS a');
		
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		// Join over the foreign key 'fk_unit_id'
		$query->select('`#__ethaae_institutes_structure_4100237`.`title_el` AS #__ethaae_institutes_structure_fk_value_4100237');
		$query->join('LEFT', '#__ethaae_institutes_structure AS #__ethaae_institutes_structure_4100237 ON #__ethaae_institutes_structure_4100237.`id` = a.`fk_unit_id`');
		

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif (empty($published))
		{
			$query->where('(a.state IN (0, 1))');
		}

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
				
			}
		}
		

		// Filtering fk_unit_id
		$filter_fk_unit_id = $this->state->get("filter.fk_unit_id");

		if ($filter_fk_unit_id !== null && !empty($filter_fk_unit_id))
		{
			$query->where("a.`fk_unit_id` = '".$db->escape($filter_fk_unit_id)."'");
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');

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
		foreach ($items as $oneItem)
		{

			if (isset($oneItem->fk_unit_id))
			{
					$db    = $this->getDatabase();
					$query = $db->getQuery(true);
					$query
						->select('`u`.`name_el`')
						->from($db->quoteName('#__ethaae_all_units', 'u'))
						->where($db->quoteName('u.id') . ' = '. $db->quote($oneItem->fk_unit_id));

					$db->setQuery($query);
                    $oneItem->fk_unit_id = $db->loadResult();
			}
            $oneItem->description_el = $this->truncate($oneItem->description_el,200);
            $oneItem->description_en = $this->truncate($oneItem->description_en,200);
		}

		return $items;
	}

    public function truncate($html, $maxLength = 0)
    {
        $baseLength = \strlen($html);

        // First get the plain text string. This is the rendered text we want to end up with.
        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

        for ($maxLength; $maxLength < $baseLength;) {
            // Now get the string if we allow html.
            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

            // Now get the plain text from the html string.
            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

            // If the new plain text string matches the original plain text string we are done.
            if ($ptString === $htmlStringToPtString) {
                return $htmlString;
            }

            // Get the number of html tag characters in the first $maxlength characters
            $diffLength = \strlen($ptString) - \strlen($htmlStringToPtString);

            // Set new $maxlength that adjusts for the html tags
            $maxLength += $diffLength;

            if ($baseLength <= $maxLength || $diffLength <= 0) {
                return $htmlString;
            }
        }

        return $html;
    }

}
