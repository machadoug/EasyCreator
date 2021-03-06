<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') || die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * Extended  to provide g11n translations.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldList extends EcrFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'List';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     * @since   11.1
     */
    protected function getInput()
    {
        //-- Initialize variables.
        $html = array();
        $attr = '';

        //-- Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="'.(string)$this->element['class'].'"' : '';

        //-- To avoid user's confusion, readonly="true" should imply disabled="true".
        if((string)$this->element['readonly'] == 'true'
        || (string)$this->element['disabled'] == 'true')
        {
            $attr .= ' disabled="disabled"';
        }

        $attr .= $this->element['size'] ? ' size="'.(int)$this->element['size'].'"' : '';
        $attr .= $this->multiple ? ' multiple="multiple"' : '';

        //-- Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="'.(string)$this->element['onchange'].'"' : '';

        //-- Get the field options.
        $options = (array)$this->getOptions();

        //-- Create a read-only list (no name) with a hidden input to store the value.
        if((string)$this->element['readonly'] == 'true')
        {
            $html[] = JHtml::_('select.genericlist', $options, ''
            , trim($attr), 'value', 'text', $this->value, $this->id);

            $html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
        }
        else
        {
            //-- Create a regular list.
            $html[] = JHtml::_('select.genericlist', $options, $this->name
            , trim($attr), 'value', 'text', $this->value, $this->id);
        }

        return implode("\n", $html);
    }//function

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     * @since   11.1
     */
    protected function getOptions()
    {
        //-- Initialize variables.
        $options = array();

        foreach($this->element->children() as $option)
        {
            //-- Only add <option /> elements.
            if($option->getName() != 'option')
            {
                continue;
            }

            //-- Create a new option object based on the <option /> element.
            $tmp = JHtml::_('select.option', (string)$option['value']
            , jgettext((string)$option), 'value', 'text'
            , ((string)$option['disabled'] == 'true'));

            //-- Set some option attributes.
            $tmp->class = (string)$option['class'];

            //-- Set some JavaScript option attributes.
            $tmp->onclick = (string)$option['onclick'];

            //-- Add the option object to the result set.
            $options[] = $tmp;
        }//foreach

        reset($options);

        return $options;
    }//function
}//class
