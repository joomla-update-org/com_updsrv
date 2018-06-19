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
		$item->extlist = [];
		if ($item->update_site_id != 0)
		{
			$query = $this->getDbo()->getQuery(true)
				->select('extension_id')
				->from('#__update_sites_extensions')
				->where('update_site_id=' . $item->update_site_id);
			$list = $this->getDbo()->setQuery($query)->loadObjectList();
			foreach ($list as $li)
			{
				$item->extlist[] = $li->extension_id;
			}
		}
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
		}
		
		return $result;
	}
	
	public function delete(&$pks)
	{
		$result = parent::delete($pks);

		if ($result)
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
}
