<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

?>

<form action="<?php echo JRoute::_('index.php?option=com_updsrv&view=manage'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		
		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="hidden-phone center"><?php echo JHtml::_('grid.checkall'); ?></th>
					<th width="5%" class="hidden-phone center" style="min-width:55px;"><?php echo JText::_('JSTATUS'); ?></th>
					<th><?php echo JText::_('COM_UPDSRV_COLUMN_NAME'); ?></th>
					<th class="hidden-phone"><?php echo JText::_('COM_UPDSRV_COLUMN_LIST'); ?></th>
					<th width="5%" class="hidden-phone"><?php echo JText::_('COM_UPDSRV_COLUMN_TYPE'); ?></th>
					<th width="1%" class="hidden-phone center nowrap"><?php echo JText::_('JGRID_HEADING_ID'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="hidden-phone center"><?php echo JHtml::_('grid.id', $i, $item->update_site_id); ?></td>
					<td class="hidden-phone center">
						<?php if (!$item->enabled) { ?>
						<a class="btn btn-mini hasTooltip" title="<?php echo JText::_('JTOOLBAR_PUBLISH'); ?>" onclick="return listItemTask('cb<?php echo $i; ?>','manage.publish');" href="javascript:void(0);"><span class="icon-unpublish"></span></a>
						<?php } else { ?>
						<a class="btn btn-mini hasTooltip active" title="<?php echo JText::_('JTOOLBAR_UNPUBLISH'); ?>" onclick="return listItemTask('cb<?php echo $i; ?>','manage.unpublish');" href="javascript:void(0);"><span class="icon-publish"></span></a>
						<?php } ?>
					</td>
					<td class="has-context">
						<a href="<?php echo JRoute::_('index.php?option=com_updsrv&task=srv.edit&update_site_id=' . $item->update_site_id); ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $this->escape($item->name); ?></a>
						<br><small><a href="<?php echo $this->escape($item->location); ?>" target="_blank" class="hasTooltip" data-placement="bottom" title="<?php echo JText::_('COM_UPDSRV_COLUMN_LOCATION_HINT'); ?>"><?php echo $this->escape($item->location); ?></a></small>
					</td>
					<td class="hidden-phone"><?php foreach ($item->extlist as $li) { echo $li->name . '<br>'; } ?></td>
					<td class="hidden-phone"><?php echo JText::_('COM_UPDSRV_TYPE_' . $item->type); ?></td>
					<td class="hidden-phone center"><?php echo (int)$item->update_site_id; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		
		<?php echo $this->pagination->getListFooter(); ?>
		
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
		
	</div>
</form>
