<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class UpdsrvViewSrv extends JViewLegacy
{
	public $form;
	public $item;
	public $state;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('\n', $errors));
			return false;
		}
		
		$isNew = $this->item->update_site_id == 0;

		$canDo = JHelperContent::getActions('com_updsrv');
		if ($canDo->get('core.manage'))
		{
			JToolBarHelper::apply('srv.apply');
			JToolBarHelper::save('srv.save');
			JToolBarHelper::save2new('srv.save2copy');
		}
		if ($isNew)
		{
			JToolBarHelper::cancel('srv.cancel');
		}
		else
		{
			JToolBarHelper::cancel('srv.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::title(JText::_('COM_UPDSRV_SRV_TITLE_' . ($isNew ? 'ADD' : 'MOD')), 'puzzle');
		
		if ($this->item->core)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_UPDSRV_NOTICE_CORE'), 'notice');
		}

		parent::display($tpl);
	}
}