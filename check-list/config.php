<?php
 
require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');
 
class CheckListConfig extends PluginConfig{
	function getOptions() {
		return array(
			'checklist_backend_enable' => new BooleanField(array(
				'id' => 'checklist_backend_enable',
				'label' => 'Enable Backend',
				'configuration' => array(
					'desc' => 'Admin backend interface')
			)),
			'checklist_frontend_enable' => new BooleanField(array(
				'id' => 'checklist_frontend_enable',
				'label' => 'Enable Frontend',
				'configuration' => array(
					'desc' => 'Staff facing interface')
			))
		);
	}
 
	function pre_save(&$config, &$errors) {
		global $msg;
 
		if (!$errors)
		$msg = 'Configuration updated successfully';
 
		return true;
	}
}
?>