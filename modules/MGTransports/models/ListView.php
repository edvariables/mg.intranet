<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger ListView Model Class
 */
class MGTransports_ListView_Model extends Vtiger_ListView_Model {


	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
		$headerFieldModels = parent::getListViewHeaders();

		$field1 = new Vtiger_Field_Model();
		$field1->set('name', 'related_vehicules');
		$field1->set('label', 'Véhicules');

		$headerFieldModels[] = $field1;

		$field1 = new Vtiger_Field_Model();
		$field1->set('name', 'related_mgchauffeurs');
		$field1->set('label', 'Chauffeurs');

		$headerFieldModels[] = $field1;

		return $headerFieldModels;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		
		$listViewRecordModels = parent::getListViewEntries($pagingModel);
		
		$adb = PearDatabase::getInstance();
		$sql = '';
		$params = array();
		
		$ids = array_keys($listViewRecordModels);
		
		$relatedTabs = array(
			'MGChauffeurs'=> array(
				'table' => 'vtiger_mgchauffeurs',
				'id_field' => 'mgchauffeursid',
				'label_field' => 'name',
				'dest_field' => 'related_mgchauffeurs',
				),
			'Vehicules'=> array(
				'table' => 'vtiger_vehicules',
				'id_field' => 'vehiculesid',
				'label_field' => 'vehicule_name',
				'dest_field' => 'related_vehicules',
				),
		);
		foreach($relatedTabs as $module => $infos){
			if($sql) $sql .= "
			UNION
			";
			$sql .= "SELECT '$module' AS related_module, IFNULL(related_left.relcrmid, related_right.crmid) AS crmid
			, ". $infos['table'] . ".". $infos['id_field'] . " AS relcmrid , ". $infos['label_field'] . " AS label
			FROM ". $infos['table'] . "
			LEFT JOIN vtiger_crmentityrel related_left
				ON ". $infos['table'] . ".". $infos['id_field'] . " = related_left.crmid
				AND related_left.relcrmid IN (" . generateQuestionMarks($ids) . ")
			LEFT JOIN vtiger_crmentityrel related_right
				ON ". $infos['table'] . ".". $infos['id_field'] . " = related_right.relcrmid
				AND related_right.crmid IN (" . generateQuestionMarks($ids) . ")
			WHERE NOT related_left.relcrmid IS NULL
			   OR NOT related_right.crmid IS NULL";
			$params = array_merge($params, $ids, $ids);
		}
		$sql .= "
			ORDER BY crmid, related_module
		";
		/*echo('<br><br><br><br>');
		echo('<br><br><br><br>');
		echo('<br><br><br><br>');*/
		
		$result = $adb->pquery($sql, $params);
		$noofrows = $adb->num_rows($result);
		if($noofrows) {
			$relatedRecords = array();
			while($row = $adb->fetch_array($result)) {
				if(!array_key_exists($row['crmid'], $relatedRecords))
					$relatedRecords[$row['crmid']] = array($row);
				else
					$relatedRecords[$row['crmid']][] = $row;
			}
			foreach($listViewRecordModels as $recordId => $record) {
				if(array_key_exists($recordId, $relatedRecords)){
					foreach($relatedTabs as $module => $infos){
						$str = '';
						foreach($relatedRecords[$recordId] as $row){
							if($row['related_module'] == $module){
								if($str)
									$str .= '<br>';
								$str .= $row['label'];
							}
						}
						$record->set($infos['dest_field'], $str);
					}
				}
			}
		}
		return $listViewRecordModels;
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
	}
}