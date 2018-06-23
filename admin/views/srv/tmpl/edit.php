<?php defined('_JEXEC') or die;
/*
 * @package     com_updsrv
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'srv.cancel' || document.formvalidator.isValid(document.id('item-form')))
	{
		Joomla.submitform(task, document.getElementById('item-form'));
	}
	else
	{
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
	}
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_updsrv&view=srv&layout=edit&update_site_id=' . $this->form->getValue('update_site_id')); ?>" method="post" name="item-form" id="item-form" class="form-validate" enctype="multipart/form-data">
	
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span9">
				<div class="form-horizontal">

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
					</div>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('location'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('location'); ?></div>
					</div>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
					</div>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('extlist'); ?></div>
						<div class="controls">
						<?php
						$this->form->setFieldAttribute('extlist', 'readonly', $this->item->core);
						echo $this->form->getInput('extlist');
						?>
						</div>
					</div>

				</div>
			</div>
			<div class="span3">
				<div class="form-vertical">

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('enabled'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('enabled'); ?></div>
					</div>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('update_site_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('update_site_id'); ?></div>
					</div>

				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->getCmd('return'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
