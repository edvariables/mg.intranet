<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class Vehicules extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_vehicules';
	var $table_index= 'vehiculesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_vehiculescf', 'vehiculesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_vehicules', 'vtiger_vehiculescf','vtiger_vendorvehiculerel');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_vehicules' => 'vehiculesid',
		'vtiger_vehiculescf'=>'vehiculesid',
		'vtiger_vendorvehiculerel'=>'vehiculeid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_VEHICULE_NAME' => array('vehicules', 'vehicule_name'),
		'LBL_VEHICULE_REGISTRATION' => array('vehicules', 'vehicule_registration'),
		
		'LBL_VEHICULE_ISRENTED' => array('vehicules','isrented'),
		'LBL_VEHICULE_OWNER' => array('vehicules', 'vehicule_owner'),
		'LBL_VEHICULE_TYPE' => array('vehicules', 'vehicule_type'),
		'LBL_VEHICULE_TARE' => array('vehicules', 'tare'),
		'LBL_VEHICULE_TYPESTRANSPORT' => array('vehicules', 'typestransport'),
		'LBL_VEHICULE_COLOR' => array('vehicules', 'calcolor'),

	);
	var $list_fields_name = Array (
		'LBL_VEHICULE_NAME' => 'vehicule_name',
		'LBL_VEHICULE_REGISTRATION' => 'vehicule_registration',
		
		'LBL_VEHICULE_ISRENTED' =>'isrented',
		'LBL_VEHICULE_OWNER' => 'vehicule_owner',
		'LBL_VEHICULE_TYPE' => 'vehicule_type',
		'LBL_VEHICULE_TARE' => 'tare',
		'LBL_VEHICULE_TYPESTRANSPORT' => 'mgtypetransport',
		'LBL_VEHICULE_COLOR' => 'calcolor',
		
	);

	// Make the field link to detail view
	var $list_link_field = 'vehicule_name';

	// For Popup listview and UI type support
	var $search_fields = Array(
		
		'LBL_VEHICULE_OWNER' => array('vehicules', 'vehicule_owner'),
		'LBL_VEHICULE_NAME' => array('vehicules', 'vehicule_name'),
		'LBL_VEHICULE_COLOR' => array('vehicules', 'calcolor'),
		'LBL_VEHICULE_REGISTRATION' => array('vehicules', 'vehicule_registration'),
		'LBL_ISRENTED' => array('vehicules', 'isrented'),
		'LBL_VEHICULE_TYPE' => array('vehicules', 'vehicule_type'),		
		'LBL_VEHICULE_TARE' => array('vehicules', 'tare'),
		'LBL_VEHICULE_TYPESTRANSPORT' => array('vehicules', 'typestransport'),

	);
	var $search_fields_name = Array (	
		'LBL_VEHICULE_OWNER' => 'vehicule_owner',
		'LBL_VEHICULE_NAME' => 'vehicule_name',
		'LBL_VEHICULE_COLOR' => 'calcolor',
		'LBL_VEHICULE_REGISTRATION' => 'vehicule_registration',
		'LBL_ISRENTED' => 'isrented',
		'LBL_VEHICULE_TYPE' => 'vehicule_type',	
		'LBL_VEHICULE_TARE' => 'tare',
		'LBL_VEHICULE_TYPESTRANSPORT' => 'mgtypetransport',
	);

	// For Popup window record selection
	var $popup_fields = Array ('vehicule_registration');

	// For Alphabetical search
	var $def_basicsearch_col = 'vehicule_registration';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'vehicule_name';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('vehicule_registration','assigned_user_id');

	var $default_order_by = 'vehicule_registration';
	var $default_sort_order='ASC';

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
 		if($eventType == 'module.postinstall') {
			//Delete duplicates from all picklist
			static::deleteDuplicatesFromAllPickLists($moduleName);
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			//Delete duplicates from all picklist
			static::deleteDuplicatesFromAllPickLists($moduleName);
		}
 	}
	
	/**
	 * Delete doubloons from all pick list from module
	 */
	public static function deleteDuplicatesFromAllPickLists($moduleName)
	{
		global $adb,$log;

		$log->debug("Invoking deleteDuplicatesFromAllPickList(".$moduleName.") method ...START");

		//Deleting doubloons
		$query = "SELECT columnname FROM `vtiger_field` WHERE uitype in (15,16,33) "
				. "and tabid in (select tabid from vtiger_tab where name = '$moduleName')";
		$result = $adb->pquery($query, array());

		$a_picklists = array();
		while($row = $adb->fetchByAssoc($result))
		{
			$a_picklists[] = $row["columnname"];
		}
		
		foreach ($a_picklists as $picklist)
		{
			static::deleteDuplicatesFromPickList($picklist);
		}
		
		$log->debug("Invoking deleteDuplicatesFromAllPickList(".$moduleName.") method ...DONE");
	}
	
	public static function deleteDuplicatesFromPickList($pickListName)
	{
		global $adb,$log;
		
		$log->debug("Invoking deleteDuplicatesFromPickList(".$pickListName.") method ...START");
	
		//Deleting doubloons
		$query = "SELECT {$pickListName}id FROM vtiger_{$pickListName} GROUP BY {$pickListName}";
		$result = $adb->pquery($query, array());
	
		$a_uniqueIds = array();
		while($row = $adb->fetchByAssoc($result))
		{
			$a_uniqueIds[] = $row[$pickListName.'id'];
		}
	
		if(!empty($a_uniqueIds))
		{
			$query = "DELETE FROM vtiger_{$pickListName} WHERE {$pickListName}id NOT IN (".implode(",", $a_uniqueIds).")";
			$adb->pquery($query, array());
		}
		
		$log->debug("Invoking deleteDuplicatesFromPickList(".$pickListName.") method ...DONE");
	}
}