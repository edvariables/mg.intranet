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
<input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
<input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
<input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTIRES_COUNT}">
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
<div class="popupEntriesDiv">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	{if $SOURCE_MODULE eq "Emails"}
		<input type="hidden" value="Vtiger_EmailsRelatedModule_Popup_Js" id="popUpClassName"/>
	{/if}
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	<table class="table table-bordered listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				{if $MULTI_SELECT}
				<td class="{$WIDTHTYPE}">
					<input type="checkbox"  class="selectAllInCurrentPage" />
				</td>
				{/if}
				{foreach item=LISTVIEW_HEADER key=LISTVIEW_HEADER_KEY from=$LISTVIEW_HEADERS}
				{if  $SOURCE_MODULE neq "MGTransports" or
					($LISTVIEW_HEADER_KEY neq 'uicolor' && $LISTVIEW_HEADER_KEY neq 'name')
					}
				<th class="{$WIDTHTYPE}">
					
					<a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('column')}">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}
						{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}<img class="sortImage" src="{vimage_path( $SORT_IMAGE, $MODULE)}">{else}<img class="hide sortingImage" src="{vimage_path( 'downArrowSmall.png', $MODULE)}">{/if}</a>
					
				</th>
				{/if}
				{/foreach}
			</tr>
		</thead>
		
		{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
		{assign var=CHAUFFEURID value={$LISTVIEW_ENTRY->getId()}}
		<tr class="listViewEntries {if $BUSYLIST[$CHAUFFEURID]['alreadyselected']}alreadySelected highlightBackgroundColor{/if} {if $BUSYLIST[$CHAUFFEURID]['busyelsewhere']} alreadyBusy{/if}"
		 data-id="{$CHAUFFEURID}" data-name='{$LISTVIEW_ENTRY->getName()}' data-info='{ZEND_JSON::encode($LISTVIEW_ENTRY->getRawData())}'
			{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if}
			{if $BUSYLIST[$CHAUFFEURID]['busyelsewhere']} data-busyin ='{ZEND_JSON::encode($BUSYLIST[$CHAUFFEURID]['busyelsewhere'])}'{/if}
			 id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
			{if $MULTI_SELECT}
			<td class="{$WIDTHTYPE}">
				<input class="entryCheckBox" type="checkbox" />
			</td>
			{/if}
			{foreach item=LISTVIEW_HEADER key=LISTVIEW_HEADER_KEY from=$LISTVIEW_HEADERS}
			
			{if  $SOURCE_MODULE neq "MGTransports" or
			($LISTVIEW_HEADER_KEY neq 'uicolor' && $LISTVIEW_HEADER_KEY neq 'name')
			}
				{if $LISTVIEW_HEADER_KEY eq 'engagement'}				
					{if $BUSYLIST[$CHAUFFEURID]}
						
						{if $BUSYLIST[$CHAUFFEURID]['busyelsewhere']}
							<td class="listViewEntryValue {$WIDTHTYPE} busyState">
								{foreach key=EVENTID item=EVENTINFO from=$BUSYLIST[$CHAUFFEURID]['busyelsewhere'] name=eventinfolist}
								
									<a href='{$EVENTINFO['href']}' title='{vtranslate($EVENTINFO['type'], $EVENTINFO['modulename'])}'>{$EVENTINFO['label']}
									{if $smarty.foreach.eventinfolist.last}. {else}, {/if}
									</a>
								
								{/foreach}
							</td>
								
						{elseif $BUSYLIST[$CHAUFFEURID]['alreadyselected']}
								<td class="listViewEntryValue {$WIDTHTYPE} busyState">			
								{vtranslate('LBL_ALREADY_SELECTED_IN', $MODULE)}{*$BUSYLIST[$CHAUFFEURID]['alreadyselected']*}	
								</td>
						{/if}
						
					{else}
						<td class="listViewEntryValue {$WIDTHTYPE} busyState">			
						{vtranslate('LBL_NO_ENGAGEMENT', $MODULE)}		
						</td>
							
						
					{/if}
					
				{elseif $LISTVIEW_HEADER_KEY eq 'colored_name'}
					<td class="listViewEntryValue {$WIDTHTYPE}">						
						<div class="picklistvalue-uicolor" style="background-color:{$LISTVIEW_ENTRY->get('uicolor')}">&nbsp;</div>
						{$LISTVIEW_ENTRY->get('name')}
					</td>
									
				{else}
					{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
					<td class="listViewEntryValue {$WIDTHTYPE}">						
						{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}					
					</td>
				{/if}
			{/if}
			{/foreach}
		</tr>
		{/foreach}
	</table>

	<!--added this div for Temporarily -->
{if $LISTVIEW_ENTIRES_COUNT eq '0'}
	<div class="row-fluid">
		<div class="emptyRecordsDiv">{vtranslate('LBL_NO', $MODULE)} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND', $MODULE)}.</div>
	</div>
{/if}
</div>
{/strip}
