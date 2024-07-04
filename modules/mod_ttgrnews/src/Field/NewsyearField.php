<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


namespace Joomla\Module\TTGRNews\Site\Field;


defined('JPATH_BASE') or die;

use ExecutionFailureException;
use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Form\Field\ListField;
use Joomla\Database\ParameterType;


class NewsyearField extends ListField
{

    protected $type = 'newsyear';

//    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * The translate.
     *
     * @var    boolean
     * @since  2.3.0
     */
    protected $translate = true;

    private $catids;
    private $language;


    protected $value;


    /**
     * Method to get the field input for a foreignkey field.
     *
     * @return  string  The field input.
     *
     * @since   2.3.0
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();
        $this->value = (string) $this->getAttribute('value',"");

        if (!\is_array($this->value) && !empty($this->value)) {
            if (\is_object($this->value)) {
                $this->value = get_object_vars($this->value);
            }

            // String in format 2,5,4
            if (\is_string($this->value)) {
                $this->value = explode(',', $this->value);
            }

            // Integer is given
            if (\is_int($this->value)) {
                $this->value = array($this->value);
            }

            $data['value'] = $this->value;
        }
        $data['options'] = $this->getOptions();
        return $this->getRenderer($this->layout)->render($data);
    }


    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   2.3.0
     */
    protected function getOptions()
    {
        $options = array();

        $this->catids = (string) $this->getAttribute('catids',"");
        $this->language = (string) $this->getAttribute('language',"*");



        $db = Factory::getContainer()->get('DatabaseDriver');
        try {
            $query = $db->getQuery(true);
            $query
                ->select('DISTINCT(year(created)) as news_year')
                ->from($db->quoteName('#__content'));
            if (!empty($this->catids)) {
                $query->where($db->quoteName('catid') . ' in ('.$this->catids.')');
            } else {
                $query->where($db->quoteName('catid') . ' = 0');
            }
            $query->whereIn($db->quoteName('state'), [1,2]);
            $query->whereIn($db->quoteName('language'), [$this->language, '*'], ParameterType::STRING);
            $query->order('news_year DESC');
            $db->setQuery($query);
            $results = $db->loadObjectList();
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }


        // Build the field options.
        if (!empty($results)) {
            foreach ($results as $item) {
                $options[] = (object)[
                    "value" => $item->news_year,
                    "text" => $this->translate == true ? Text::_($item->news_year) : $item->news_year
                ];
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }


    /**
     * Wrapper method for getting attributes from the form element
     *
     * @param   string  $attr_name  Attribute name
     * @param   mixed   $default    Optional value to return if attribute not found
     *
     * @return  mixed The value of the attribute if it exists, null otherwise
     */
    public function getAttribute($attr_name, $default = null)
    {
        if (!empty($this->element[$attr_name]))
        {
            return $this->element[$attr_name];
        }
        else
        {
            return $default;
        }
    }


}