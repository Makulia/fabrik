<?php
/**
 * Renders widget for (de)selecting available groups when editing a from
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders widget for (de)selecting available groups when editing a from
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldSwapList extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'SwapList';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		$from = $this->id . '-from';
		$add = $this->id . '-add';
		$remove = $this->id . '-remove';
		$up = $this->id . '-up';
		$down = $this->id . '-down';
		$script = "swaplist = new SwapList('$from', '$this->id','$add', '$remove', '$up', '$down');";

		FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/swaplist.js', $script);

		list($this->currentGroups, $this->currentGroupList) = $this->getCurrentGroupList();
		list($this->groups, $this->groupList) = $this->getGroupList();
		$str = '';

		$checked = empty($this->current_groups) ? 'checked="checked"' : '';

		if (empty($this->groups) && empty($this->currentGroups))
		{
			return JText::_('COM_FABRIK_NO_GROUPS_AVAILABLE');
		}
		else
		{
			$str .= '<input type="text" readonly="readonly" class="readonly" style="clear:left" size="44" value="'
				. JText::_('COM_FABRIK_AVAILABLE_GROUPS') . ':" />';
			$str .= $this->groupList;
			$str .= '<input class="button" type="button" id="' . $this->id . '-add" value="' . JText::_('COM_FABRIK_ADD') . '" />';
			$str .= '<input type="text" readonly="readonly" class="readonly" style="clear:left" size="44" value="'
				. JText::_('COM_FABRIK_CURRENT_GROUPS') . ':" />';
			$str .= $this->currentGroupList;
			$str .= '<input class="button" type="button" value="' . JText::_('COM_FABRIK_UP') . '" id="' . $this->id . '-up" />';
			$str .= '<input class="button" type="button" value="' . JText::_('COM_FABRIK_DOWN') . '" id="' . $this->id . '-down" />';
			$str .= '<input class="button" type="button" value="' . JText::_('COM_FABRIK_REMOVE') . '" id="' . $this->id . '-remove"/>';
			return $str;
		}
	}

	/**
	* Method to get the field label markup.
	*
	* @return  string  The field label markup.
	*/

	protected function getLabel()
	{
		return '';
	}

	/**
	 * get a list of unused groups
	 *
	 * @return  array	list of groups, html list of groups
	 */

	public function getGroupList()
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('DISTINCT(group_id)')->from('#__{package}_formgroup');
		$db->setQuery($query);
		$usedgroups = $db->loadColumn();
		JArrayHelper::toInteger($usedgroups);
		$query = $db->getQuery(true);
		$query->select('id AS value, name AS text')->from('#__{package}_groups');
		if (!empty($usedgroups))
		{
			$query->where('id NOT IN(' . implode(',', $usedgroups) . ')');
		}
		$query->where('published <> -2');
		$query->order(FabrikString::safeColName('text'));
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$list = JHTML::_('select.genericlist', $groups, 'jform[groups]', 'class="inputbox" size="10" style="width:100%;" ', 'value', 'text', null,
			$this->id . '-from');
		return array($groups, $list);
	}

	/**
	 * Get a list of groups currenly used by the form
	 *
	 * @return  array  list of groups, html list of groups
	 */

	public function getCurrentGroupList()
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('fg.group_id AS value, g.name AS text');
		$query->from('#__{package}_formgroup AS fg');
		$query->join('LEFT', ' #__{package}_groups AS g ON fg.group_id = g.id');
		$query->where('fg.form_id = ' . (int) $this->form->getValue('id'));
		$query->where('g.name <> ""');
		$query->order('fg.ordering');
		$db->setQuery($query);
		$currentGroups = $db->loadObjectList();
		$list = JHTML::_('select.genericlist', $currentGroups, $this->name, 'class="inputbox" multiple="multiple" style="width:100%;" size="10" ',
			'value', 'text', '/', $this->id);
		return array($currentGroups, $list);
	}
}
