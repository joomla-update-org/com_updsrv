<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class UpdsrvViewManage extends JViewLegacy
{
	public $items;
	public $pagination;
	public $state;

	public function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('\n', $errors));
			return false;
		}
		
		$canDo = JHelperContent::getActions('com_updsrv');
		if ($canDo->get('core.manage'))
		{
			JToolbarHelper::addNew('srv.add');
			if (count($this->items) > 0)
			{
				JToolBarHelper::editList('srv.edit');
				JToolbarHelper::publish('manage.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('manage.unpublish', 'JTOOLBAR_UNPUBLISH', true);
				JToolbar::getInstance('toolbar')->appendButton('Standard', 'save-copy', 'JTOOLBAR_COPY', 'manage.copy', true);
				JToolBarHelper::deleteList('COM_UPDSRV_DELETE_QUERY_STRING', 'manage.delete', 'JTOOLBAR_DELETE');
			}
			if ($canDo->get('core.admin'))
			{
				JToolBarHelper::preferences('com_updsrv');
			}
		}

		$custom_button_html = '<span style="display:inline-block;padding:0 10px;font-size:12px;line-height:25.5px;border:1px solid #d6e9c6;border-radius:3px;background-color:#dff0d8;color:#3c763d;">' . JText::sprintf('J_COUNT_ITEMS_VIEW', count($this->items)) . '</span>';
		JToolBar::getInstance('toolbar')->appendButton('Custom', $custom_button_html, 'options');
		
		JToolBarHelper::title(JText::_('COM_UPDSRV'), 'puzzle');
		
		JFactory::getApplication()->enqueueMessage(JText::_('COM_UPDSRV_NOTICE_CORE'), 'notice');

		parent::display( $tpl );
	}
}
