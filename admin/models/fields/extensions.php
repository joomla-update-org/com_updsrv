<?php defined('JPATH_PLATFORM') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

JFormHelper::loadFieldClass('list');

class JFormFieldExtensions extends JFormFieldList
{

	public $type = 'extensions';

	protected static $options = [];
	
	protected $list = [];

	protected function getOptions()
	{
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();
			
			$options = [];
			
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true)
				->select('extension_id as value, name as text, element, client_id, type, folder')
				->from('#__extensions')
				->order('type asc, folder asc, name asc');
			$options = $db->setQuery($query)->loadObjectList();
			if ($options)
			{
				$this->translate($options);
				usort($options, function ($a, $b) { return $a->text > $b->text; } );
				static::$options[$hash] = array_merge(static::$options[$hash], array_values($options));
			}
		}
		return static::$options[$hash]; 
	}

	protected function translate(&$items)
	{
		$lang = JFactory::getLanguage();

		foreach ($items as &$item)
		{
			$path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;
			
			$lang->load('com_installer', JPATH_ADMINISTRATOR, null, false, true);
			$type_translated = JText::_('COM_INSTALLER_TYPE_' . strtoupper($item->type)) . ' – ';
			$folder_translated = @$item->folder ? $item->folder . ' – ' : '';

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

			$item->text = $type_translated . $folder_translated . JText::_($item->text);
		}
	}

}
