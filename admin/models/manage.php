<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class UpdsrvModelManage extends JModelList
{
	public function __construct($config = [])
	{
		parent::__construct($config);
	}

	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);
	}

	protected function getListQuery()
	{
		$query = $this->getDbo()->getQuery(true)
			->select('update_site_id, name, type, location, enabled')
			->from('#__update_sites')
			->order('name asc');
		return $query;
	}

	public function getTable($type = 'Updsrv_Updatesites', $prefix = 'Table', $config = [])
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function translate(&$items)
	{
		$lang = JFactory::getLanguage();

		foreach ($items as &$item)
		{
			$path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;

			switch ($item->type)
			{
				case 'component':
					$extension = $item->element;
					$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
					$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true) ||
						$lang->load("$extension.sys", $source, null, false, true);
					break;
				case 'file':
					$extension = 'files_' . $item->element;
					$lang->load("$extension.sys", JPATH_SITE, null, false, true);
					break;
				case 'library':
					$extension = 'lib_' . $item->element;
					$lang->load("$extension.sys", JPATH_SITE, null, false, true);
					break;
				case 'module':
					$extension = $item->element;
					$source = $path . '/modules/' . $extension;
					$lang->load("$extension.sys", $path, null, false, true) ||
						$lang->load("$extension.sys", $source, null, false, true);
					break;
				case 'plugin':
					$extension = 'plg_' . $item->folder . '_' . $item->element;
					$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
					$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true) ||
						$lang->load("$extension.sys", $source, null, false, true);
					break;
				case 'template':
					$extension = 'tpl_' . $item->element;
					$source = $path . '/templates/' . $item->element;
					$lang->load("$extension.sys", $path, null, false, true) ||
						$lang->load("$extension.sys", $source, null, false, true);
					break;
				case 'package':
				default:
					$extension = $item->element;
					$lang->load("$extension.sys", JPATH_SITE, null, false, true);
					break;
			}

			$item->name = JText::_($item->name);
		}
	}

	public function getItems()
	{
		$items = parent::getItems();
		foreach ($items as &$item)
		{
			$query = $this->getDbo()->getQuery(true)
				->select('e.name, e.element, e.client_id, e.type, e.folder')
				->from('#__extensions as e')
				->leftJoin('#__update_sites_extensions as u on u.extension_id=e.extension_id')
				->where('u.update_site_id=' . $item->update_site_id)
				->order('e.name asc');
			$item->extlist = $this->getDbo()->setQuery($query)->loadObjectList();
			$this->translate($item->extlist);
			
			$item->core = false;
			foreach ($item->extlist as $li)
			{
				if ($li->element == 'joomla' && $li->type == 'file')
				{
					$item->core = true;
					break;
				}
			}
		}
		return $items;
	}

	public function publish(&$ids = [], $value = 0)
	{
		if (!JFactory::getUser()->authorise('core.manage', 'com_updsrv'))
		{
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			return false;
		}

		$result = true;

		if (!is_array($ids))
		{
			$ids = [$ids];
		}

		$query = $this->getDbo()->getQuery(true)
			->update('#__update_sites')
			->set('enabled=' . $value)
			->where('update_site_id in (' . implode(',', $ids) . ')');
		try
		{
			$this->getDbo()->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}

	public function copy($id)
	{
		if (!JFactory::getUser()->authorise('core.manage', 'com_updsrv'))
		{
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			return false;
		}

		$table = $this->getTable();
		$table->load($id);
		$table->update_site_id = 0;
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}
		
		$query = $this->getDbo()->getQuery(true)
			->select('extension_id')
			->from('#__update_sites_extensions')
			->where('update_site_id=' . $id);
		$list = $this->getDbo()->setQuery($query)->loadObjectList();
		foreach ($list as $li)
		{
			$query = $this->getDbo()->getQuery(true)
				->insert('#__update_sites_extensions')
				->columns('update_site_id, extension_id')
				->values($table->update_site_id . ', ' . $li->extension_id);
			$this->getDbo()->setQuery($query)->execute();
		}
		
		return $table->update_site_id;
	}
}
