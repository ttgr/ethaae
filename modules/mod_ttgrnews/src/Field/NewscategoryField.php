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
use Joomla\CMS\Log\Log;


class NewscategoryField extends ListField
{

    protected $type = 'newscategory';

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
    private $extension;
    private $published;
    private $action;
    protected $value;


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

    protected function getOptions()
    {
        $options   = [];
        $this->extension = (string) $this->getAttribute('extension',"");
        $this->language = (string) $this->getAttribute('language',"*");
        $this->published = (int) $this->getAttribute('published',"1");
        $this->action = (string) $this->getAttribute('action',"");
        $this->catids = (string) $this->getAttribute('catids',"");
        $catids = explode(',',$this->catids);

        // Load the category options for a given extension.
        if (!empty($this->extension)) {
            // Filter over published state or not depending upon if it is present.

            if ($this->published) {
                $filters['filter.published'] = explode(',', $this->published);
            }

            // Filter over language depending upon if it is present.
            if ($this->language) {
                $filters['filter.language'] = explode(',', $this->language);
            }

            if ($filters === []) {
                $options = HTMLHelper::_('category.options', $this->extension);
            } else {
                $options = HTMLHelper::_('category.options', $this->extension, $filters);
            }


                foreach ($options as $i => $option) {
                    if (in_array($options[$i]->value,$catids)) {
                        $options[$i]->text = str_replace($this->language, "", $options[$i]->text);
                        $pattern = array('/\(/', '/\)/', '/-/i');
                        $options[$i]->text = trim(preg_replace($pattern, '', $options[$i]->text));
                    } else {
                        unset($options[$i]);
                    }
                }


        } else {
            Log::add(Text::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'), Log::WARNING, 'jerror');
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