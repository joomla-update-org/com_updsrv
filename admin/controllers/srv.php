<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

class UpdsrvControllerSrv extends JControllerForm
{
	function __construct($config = [])
	{
		$this->view_list = 'manage';
		parent::__construct($config);
	}

	protected function allowEdit($data = [], $key = 'id')
	{
		return JFactory::getUser()->authorise('core.manage', 'com_updsrv');
	}
}