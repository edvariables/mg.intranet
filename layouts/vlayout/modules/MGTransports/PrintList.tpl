{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
<div id="toggleButton" class="toggleButton" title="Left Panel Show/Hide"> 
	<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-right {else} icon-chevron-left{/if}"></i>
</div>&nbsp
    <div style="padding-left: 15px;">
        <form id="exportForm" class="form-horizontal row-fluid" method="post" action="index.php">
            <input type="hidden" name="module" value="{$SOURCE_MODULE}" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
	    <input type="hidden" name="orderby" value="{$ORDER_BY}" />
            <input type="hidden" name="sortorder" value="{$SORT_ORDER}" />
	    
            <input type="hidden" name="view" value="PrintData" />
            <input type="hidden" name="viewname" value="{$VIEWID}" />
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" id="page" name="page" value="{$PAGE}" />
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
	    <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
            <div class="row-fluid">
                <div class="span8">
                <h4>{vtranslate('LBL_PRINT_RECORDS',$MODULE)} {vtranslate($SOURCE_MODULE,$MODULE)} </h4>
                    
		<div class="well exportContents marginLeftZero">
			<h3>{vtranslate('LBL_SELECT_PRINT_CONTENTS',$MODULE)}</h3>	
				<div class="row-fluid">
					<div class="row-fluid" style="height:30px">
						<div class="span6 textAlignRight row-fluid">
							<div class="span8">{vtranslate('LBL_PRINT_SELECTED_RECORDS',$MODULE)}&nbsp;</div>
							<div class="span3"><input type="radio" name="mode" value="ExportSelectedRecords" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if}/></div>
						</div>
					</div>
					{if empty($SELECTED_IDS)}&nbsp; <span class="redColor">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
				</div>
				<div class="row-fluid" style="height:30px">
					<div class="span6 textAlignRight row-fluid">
						<div class="span8">{vtranslate('LBL_PRINT_DATA_IN_CURRENT_PAGE',$MODULE)}&nbsp;</div>
						<div class="span3"><input type="radio" name="mode" value="ExportCurrentPage" /></div>
					</div>
				</div>
				<div class="row-fluid" style="height:30px">
					 <div class="span6 textAlignRight row-fluid">
						<div class="span8">{vtranslate('LBL_PRINT_ALL_DATA',$MODULE)}&nbsp;</div>
						<div class="span3"><input type="radio"  name="mode" value="ExportAllData"  {if empty($SELECTED_IDS)} checked="checked" {/if} /></div>
					</div>
				</div>
                </div>
				
		<div class="well printMode marginLeftZero">
			<h3>{vtranslate('LBL_SELECT_PRINT_MODE',$MODULE)}</h3>	
				<div class="row-fluid">
					<div class="row-fluid" style="height:30px">
						<div class="span6 textAlignRight row-fluid">
							<div class="span8">{vtranslate('LBL_PRINT_MODE_0',$MODULE)}&nbsp;</div>
							<div class="span3"><input type="radio" name="printmode" value="MGOldSchool" checked="checked"/></div>
						</div>
					</div>
					<div class="row-fluid" style="height:30px">
						<div class="span6 textAlignRight row-fluid">
							<div class="span8">{vtranslate('LBL_PRINT_MODE_1',$MODULE)}&nbsp;</div>
							<div class="span3"><input type="radio" name="printmode" value="Basic"/></div>
						</div>
					</div>
				</div>
		</div>	
                <br> 
                <div class="textAlignCenter">
                        <button class="btn btn-success" type="submit"><strong>{vtranslate(LBL_PRINT, $MODULE)}&nbsp;{vtranslate($SOURCE_MODULE, $MODULE)}</strong></button>
                        <a class="cancelLink" type="reset" onclick='window.history.back()'>{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
                </div>
            </div>
        
	</form>
	</div>

{/strip}
