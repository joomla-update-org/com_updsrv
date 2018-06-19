<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class TableUpdsrv_Updatesites extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__update_sites', 'update_site_id', $db);
	}
}