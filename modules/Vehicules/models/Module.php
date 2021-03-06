<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Vehicules_Module_Model extends Vtiger_Module_Model {
	
	
	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		
		$relatedModuleName = $relatedModule->getName();
		// this gets only activity, no tasks
		if ($functionName === 'get_activities' && ($relatedModuleName == 'Events' || $relatedModuleName == '')) {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_vehiculeactivityrel.vehiculeid, vtiger_contactdetails.lastname,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_cntactivityrel.contactid,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS eventstatus
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						
						LEFT JOIN vtiger_vehiculeactivityrel ON vtiger_vehiculeactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						
							WHERE vtiger_vehiculeactivityrel.vehiculeid = ".$recordId." AND vtiger_crmentity.deleted = 0
								AND vtiger_activity.activitytype <> 'Emails'
								AND vtiger_activity.activitytype <> 'Tasks'";

			$relatedModuleName = $relatedModule->getName();
			
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		}
		else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}		
		return $query;
	}
	
	// Function returns the Busylist on the date of the mgevent
	// @param <int> $mgeventId : id of the event or transport considered, needed to get the date
	// @return <Array>
	public function getBusylist($mgevent) {		
		$busyontransportslist = $this->getBusylistOnTransports($mgevent);
		$busyoneventslist = $this->getBusylistOnEvents($mgevent);
		$busylist = array();
		
		if (empty($busyoneventslist)) {
			$busylist = $busyontransportslist;
		}
		else {
		foreach($busyontransportslist as $vehicid => $transportsarray) {
			 if (array_key_exists($vehicid,$busyoneventslist)) {				
				$busylist[$vehicid] = $transportsarray + $busyoneventslist[$vehicid];
			 }
			 else{
				$busylist[$vehicid] = $transportsarray;
			 }
		}
		
		foreach($busyoneventslist as $vehicid => $eventsarray) {			
			 if (!array_key_exists($vehicid,$busylist)) {
				$busylist[$vehicid] = $eventsarray;
			 }
		}
		}
		return $busylist;
	}	

	//
	// Function returns the Busylist on the date of the mgtransport
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// @return <Array> (vehiculeid1 => array (eventidx=>arrayofinfo,eventidy=>arrayofinfo,...), vehiculeid2 => array (eventidx=>arrayofinfo,eventidz=>arrayofinfo,...))
	public function getBusylistOnEvents($mgtransportId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $currentUser->getId();
		
		$busyList = array();
		$db = PearDatabase::getInstance();
		
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_activity.subject,vtiger_activity.activitytype, vtiger_vehiculeactivityrel.vehiculeid as vehiculeid FROM vtiger_activity"
					." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid"
					." INNER JOIN vtiger_vehiculeactivityrel ON vtiger_vehiculeactivityrel.activityid = vtiger_activity.activityid"
					;

		$query .= " WHERE vtiger_crmentity.deleted=0"
			." AND (vtiger_activity.date_start <= (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			." AND (vtiger_activity.due_date >= (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			;	

		$params = array($mgtransportId,$mgtransportId);

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$eventhref = "index.php?module=Calendar".
					"&view=Detail&record=".$row['crmid'] ;
					
			$temparray = array('modulename'=>'Calendar',
					'label'=>$row['subject'],
					'type'=>$row['activitytype'],
					'href'=>$eventhref
					);
			
			if (!$busyList[$row['vehiculeid']]) {	
			$busyList[$row['vehiculeid']]=array($row['crmid']=> $temparray);
			}
			
			else {				
			$busyList[$row['vehiculeid']][$row['crmid']] = $temparray ;
			}		
					
		}
		
		return $busyList;	
	}
	
	// Function returns the Busylist on the date of the mgtransport
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// @return <Array>
	public function getBusylistOnTransports($mgtransportId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $currentUser->getId();
		$busyList = array();
		$db = PearDatabase::getInstance();
		
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_mgtransports.subject, vtiger_mgtransports.mgtypetransport, vtiger_crmentityrel.relcrmid as vehiculeid, vtiger_crmentityrel.relmodule  FROM vtiger_mgtransports"
					." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_mgtransports.mgtransportsid"
					." LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = vtiger_mgtransports.mgtransportsid"
					;

		$query .= " WHERE vtiger_crmentity.deleted=0"
			." AND (vtiger_mgtransports.datetransport = (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			." AND vtiger_crmentityrel.relmodule = ?";	

		$params = array($mgtransportId,$this->getName());


		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$transporthref = "index.php?module=MGTransports".
					"&view=Detail&record=".$row['crmid'] ;
					
			$temparray = array('modulename'=>'MGTransports',
					'label'=>$row['subject'],
					'type'=>$row['mgtypetransport'],
					'href'=>$transporthref
					);
			
			if (!$busyList[$row['vehiculeid']]) {	
			$busyList[$row['vehiculeid']]=array($row['crmid']=> $temparray);
			}
			
			else {				
			$busyList[$row['vehiculeid']][$row['crmid']] = $temparray ;
			}
			
			
		}
		
		return $busyList;	
	}
	
	
     //Function that returns related list header fields that will be showed in the Related List View
     // @return <Array> returns related fields list.
    //
	public function getRelatedListFields() {
		
	$relatedListFields = parent::getRelatedListFields();

		$temp = array();

		$temp['full_vehicule_name'] = 'full_vehicule_name';
		
		$relatedListFields = array_merge($temp,$relatedListFields);
		
		
        return $relatedListFields;
	}
/**SGNOW copie de vtiger
	 * Function to get list of field for summary view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 */
	public function getSummaryViewFieldsList() {
		
		if (!$this->summaryFields) {
			$summaryFields = array();
			$fields = $this->getFields();
			
			//SG1504 Ajout de calcolor et des parametres de full_vehivule_name dans les summary view fields
			$fullnamefields = array('calcolor' /*,'vehicule_owner','isrented'*/);
			
			foreach ($fields as $fieldName => $fieldModel) {
				if (($fieldModel->isSummaryField() && $fieldModel->isActiveField()) ||  in_array($fieldName,$fullnamefields) ) {
					$summaryFields[$fieldName] = $fieldModel;
				}
			}
			$this->summaryFields = $summaryFields;
			
		}
		return $this->summaryFields;
	}

	public function getField($fieldName) {
	if ($fieldName == 'full_vehicule_name') {
		$field = new Vtiger_Field_Model();
		
		$field->set('name', 'full_vehicule_name');
		$field->set('column', 'full_vehicule_name');
		$field->set('label', 'LBL_FULL_VEHICULE_NAME');
		
		return $field;
	}
	else
	return Vtiger_Field_Model::getInstance($fieldName,$this);
	}

}