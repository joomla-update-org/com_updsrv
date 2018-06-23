<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class UpdsrvControllerManage extends JControllerAdmin
{
	function __construct($config = [])
	{
		parent::__construct($config);
	}

	public function getModel($name = 'Srv', $prefix = 'UpdsrvModel', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function publish()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('cid', [], 'array');
		$values = ['publish' => 1, 'unpublish' => 0];
		$task = $this->getTask();
		$value = JArrayHelper::getValue($values, $task, 0, 'int');

		if (!empty($ids))
		{
			$model = $this->getModel('srv');
			$core = $model->checkCore($ids);
			$key = array_search($core, $ids);
			if ($key !== false)
			{
				unset($ids[$key]);
				$ids = array_values($ids);
			}
			if ($core)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_UPDSRV_NO_STATE_CORE', $core), 'error');
			}
		}
		
		if (empty($ids))
		{
			if (!$core)
			{
				JError::raiseWarning(500, JText::_('COM_UPDSRV_ERROR_NO_SELECTED'));
			}
		}
		else
		{
			$model = $this->getModel('manage');
			if (!$model->publish($ids, $value))
			{
				JError::raiseWarning(500, implode('<br>', $model->getErrors()));
			}
			else
			{
				$ntext = $value ? 'COM_UPDSRV_N_ITEMS_PUBLISHED' : 'COM_UPDSRV_N_ITEMS_UNPUBLISHED';
				$this->setMessage(JText::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_updsrv&view=manage', false));
	}

	public function copy()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('cid', [], 'array');

		if (empty($ids) || (int)$ids[0] <= 0)
		{
			JError::raiseWarning(500, JText::_('COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'));
		}
		else
		{
			$model = $this->getModel('srv');
			$core = $model->checkCore([(int)$ids[0]]);
			if ($core)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_UPDSRV_NO_COPY_CORE', $core), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_updsrv&view=manage', false));
			}
			else
			{
				$model = $this->getModel('manage');
				$result = $model->copy((int)$ids[0]);
				if (!$result)
				{
					JError::raiseWarning(500, implode('<br />', $model->getErrors()));
					$this->setRedirect(JRoute::_('index.php?option=com_updsrv&view=manage', false));
				}
				else
				{
					$this->setRedirect(JRoute::_('index.php?option=com_updsrv&task=srv.edit&update_site_id=' . (int)$result, false));
				}
			}
		}
	}
}