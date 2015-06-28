<?php

//	Include required modules from osTicket
require_once(INCLUDE_DIR . 'class.plugin.php');
 

// Include required modules from Check List Plugin
require_once('config.php');


// Define required variables for Plugin
define('CHECKLIST_TABLE_CHECKLIST',TABLE_PREFIX.'checklist');
define('CHECKLIST_TABLE_DAGDELEN',TABLE_PREFIX.'dagdelen');
define('CHECKLIST_TABLE_ENTRIES',TABLE_PREFIX.'entries');
define('PLUGINS_ROOT',INCLUDE_DIR.'plugins/');
define('CHECKLIST_PLUGIN_ROOT',PLUGINS_ROOT.'check-list/');
define('CHECKLIST_INCLUDE_DIR',CHECKLIST_PLUGIN_ROOT.'include/');


// Installer Class
require_once(CHECKLIST_INCLUDE_DIR . 'class.checklist-installer.php');


// Check List Plugin Class
class CheckListPlugin extends Plugin {
 
    var $config_class = 'CheckListConfig';
 
 
 
// bootstrap function that is run on every page call 
    function bootstrap() {
        if ($this->firstRun()) {
            $this->configureFirstRun();
        }
 
        $config = $this->getConfig();
		
		
		if ($config->get ( 'checklist_backend_enable' )) {
			$this->createAdminMenu ();
		}
		if ($config->get ( 'checklist_frontend_enable' )) {
			$this->createStaffMenu ();
		}
    }
 
 
	/**
	 * Creates menu links in the staff frontend.
	 */
	function createStaffMenu() {
		Application::registerStaffApp ( 'Check List', 'checklist.php', array (
				iconclass => 'faq-categories' 
		) );
	}
	
	/**
	 * Creates menu links in the Admin backend.
	 */
	function createAdminMenu() {
		/*Application::registerStaffApp ( 'Equipment', 'dispatcher.php/equipment/dashboard/', array (
				iconclass => 'faq-categories' 
		) );*/
	}
 
    /**
     * Checks if this is the first run of our plugin.
     * @return boolean
     */
    function firstRun() {
        $sql='SHOW TABLES LIKE \''.CHECKLIST_TABLE_CHECKLIST.'\'';
        $res=db_query($sql);
        return  (db_num_rows($res)==0);
    }
 
    /**
     * Necessary functionality to configure first run of the application
     */
    function configureFirstRun() {
       if(!$this->createDBTables())
       {
           echo "First run configuration error.  " . "Unable to create database tables!";
       }
    }
 
    /**
     * Kicks off database installation scripts
     * @return boolean
     */
    function createDBTables() {
       $installer = new CheckListInstaller();
       return $installer->install();
 
    }
 
    /**
     * Uninstall hook.
     * @param type $errors
     * @return boolean
     */
    function pre_uninstall(&$errors) {
       $installer = new CheckListInstaller();
       return $installer->remove();
    }


}

?>