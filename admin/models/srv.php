<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class UpdsrvModelSrv extends JModelAdmin
{
	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_updsrv.srv', 'srv', ['control' => 'jform', 'load_data' => $loadData]);
		if (empty($form))
		{
			return false;
		}
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_updsrv.srv.' . $this->getState('extdataedit.id')))
		{
			$form->setFieldAttribute('enabled', 'disabled', 'true');
			$form->setFieldAttribute('enabled', 'filter', 'unset');
		}
		return $form;
	}
	
	public function getItem($update_site_id = null)
	{
		$item = parent::getItem($update_site_id);
		$this->setState('core', false);
		$item->extlist = [];
		if ($item->update_site_id != 0)
		{
			$query = $this->getDbo()->getQuery(true)
				->select('u.extension_id, e.element, e.type')
				->from('#__update_sites_extensions as u')
				->innerJoin('#__extensions as e on e.extension_id=u.extension_id')
				->where('u.update_site_id=' . $item->update_site_id);
			$list = $this->getDbo()->setQuery($query)->loadObjectList();
			foreach ($list as $li)
			{
				$item->extlist[] = $li->extension_id;
				if ($li->element == 'joomla' && $li->type == 'file')
				{
					$item->core = true;
					$this->setState('core', true);
				}
			}
		}
		$item->core = $this->getState('core');
		return $item;
	}
	
	public function getTable($type = 'Updsrv_Updatesites', $prefix = 'Table', $config = [])
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_updsrv.edit.srv.data', []);
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	protected function canDelete($record)
	{
		return JFactory::getUser()->authorise('core.manage', 'com_updsrv');
	}

	protected function canEditState($record)
	{
		return JFactory::getUser()->authorise('core.manage', 'com_updsrv');
	}
	
	public function save($data)
	{
		if ($data['update_site_id'] == 0)
		{
			$data['last_check_timestamp'] = 0;
			$core = false;
		}
		else
		{
			$core = $this->checkCore( [$data['update_site_id']] );
		}
		
		$result = parent::save($data);
		
		if ($result != false)
		{
			if ($data['update_site_id'] !== 0)
			{
				$query = $this->getDbo()->getQuery(true)
					->delete('#__update_sites_extensions')
					->where('update_site_id = ' . $data['update_site_id']);
				$this->getDbo()->setQuery($query)->execute();
			}
			foreach ($data['extlist'] as $li)
			{
				$query = $this->getDbo()->getQuery(true)
					->insert('#__update_sites_extensions')
					->columns('update_site_id, extension_id')
					->values($data['update_site_id'] . ', ' . $li);
				$this->getDbo()->setQuery($query)->execute();
			}
			
			if ($core)
			{
				$params = JComponentHelper::getParams('com_joomlaupdate');
				$params->set('updatesource', 'custom');
				$params->set('customurl', $data['location']);
				
				$query = $this->getDbo()->getQuery(true)
					->update('#__extensions')
					->set('params=' . $this->getDbo()->quote($params->toString()))
					->where('element=' . $this->getDbo()->quote('com_joomlaupdate') );
				try
				{
					$this->getDbo()->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}
		}
		
		return $result;
	}
	
	public function delete(&$pks)
	{
		if (!is_array($pks))
		{
			$pks = [$pks];
		}

		$core = $this->checkCore($pks);
		$key = array_search($core, $pks);
		if ($key !== false)
		{
			unset($pks[$key]);
			$pks = array_values($pks);
		}
		
		if ($core)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_UPDSRV_NO_DELETE_CORE', $core), 'error');
		}
		
		$result = parent::delete($pks);

		if (count($pks) > 0 && $result)
		{
			$query = $this->getDbo()->getQuery(true)
				->delete('#__update_sites_extensions')
				->where('update_site_id in (' . implode(',', $pks) . ')');
			$this->getDbo()->setQuery($query)->execute();
		}
		return $result != false;
	}

	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_updsrv');
	}

	public function checkCore($ids)
	{
		$core = 0;
		$query = $this->getDbo()->getQuery(true)
			->select('u.update_site_id, e.element, e.type')
			->from('#__update_sites_extensions as u')
			->innerJoin('#__extensions as e on e.extension_id=u.extension_id')
			->where('u.update_site_id in (' . implode(',', $ids) . ')');
		$list = $this->getDbo()->setQuery($query)->loadObjectList();
		
		foreach ($list as $li)
		{
			if ($li->element == 'joomla' && $li->type == 'file')
			{
				$core = $li->update_site_id;
				break;
			}
		}
		return $core;
	}
}
