/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
//SG1411

Vtiger_Popup_Js("MGChauffeurs_Popup_Js",{
},
{
	setTextColorForColorTag : function(){
		var popupPageContainer = jQuery('#popupPageContainer');
		
		popupPageContainer.find('tr.listViewEntries div.colortag').each(function(index,element) {
			var colordiv = jQuery(element);
			if ((colordiv.data('color'))) {
				var thiscolor = colordiv.data('color');
				var colorContrast = app.getColorContrast(thiscolor.slice(1));
				if(colorContrast == 'light') {
					var textColor = 'black'
					}
				else {
					var textColor = 'white'
				}
				colordiv.css({'background-color':thiscolor,'color':textColor});
			}
		}
		);
		
		
	},
	setTextColorForBusyChauffeur : function(){
		var popupPageContainer = jQuery('#popupPageContainer');
		popupPageContainer.find('tr.listViewEntries.alreadyBusy td.busyState').each(function(index,element) {
			var busytd = jQuery(element);
			
				busytd.css({'color': 'red'});
				busytd.find('a').css({'color': 'red'});
			}
		);		
	},
	
	disableBusyChauffeurs : function(){
		var srcrcrd = (this.sourceRecord == false) ? this.getSourceRecord() : this.sourceRecord;
		var popupPageContainer = jQuery('#popupPageContainer');
		popupPageContainer.find('tr.listViewEntries').each(function(index,rowelement) {
			var row = jQuery(rowelement);
			if (row.hasClass('alreadySelected')) {
				row.find('td input.entryCheckBox').hide();				
				}
			else row.addClass('selectableChauffeur');
			});
		
	},
	selectAllHandler : function(e){
		var currentElement = jQuery(e.currentTarget);
		var isMainCheckBoxChecked = currentElement.is(':checked');
		var tableElement = currentElement.closest('table');
		if(isMainCheckBoxChecked) {
			jQuery('tr.selectableChauffeur input.entryCheckBox', tableElement).attr('checked','checked').closest('tr').addClass('highlightBackgroundColor');
		}else {
			jQuery('tr.selectableChauffeur input.entryCheckBox', tableElement).removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');
		}
	},
	/**
	 * Function to get Page Records. Appelee apres un search. SG1411 : Ajout des traitemnts sp�cifiques voir tag SG1411
	 */
	getPageRecords : function(params){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		Vtiger_BaseList_Js.getPageRecords(params).then(
				function(data){
					jQuery('#popupContents').html(data);
					
					thisInstance.disableBusyChauffeurs();
					thisInstance.setTextColorForColorTag();
					thisInstance.registerEventForListEntryValueLink();
					thisInstance.setTextColorForBusyChauffeur();
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					})
					thisInstance.calculatePages().then(function(data){
						aDeferred.resolve(data);
					});
				},

				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			);
		return aDeferred.promise();
	},
	
	registerEventForListEntryValueLink : function(){		
		var thisInstance = this;
		var srcmod = thisInstance.getSourceModule();
		if (srcmod && srcmod == 'MGTransports') {	
			var popupPageContentsContainer = this.getPopupPageContainer();
			popupPageContentsContainer.on('click','td.listViewEntryValue a',function(e){
				thisInstance.clickLinkForParentWindow(e);
				});

		}	
	},
	clickLinkForParentWindow : function(e){
		
		if(typeof window == 'undefined'){
					window = self;
					};
		var thisanc  = jQuery(e.currentTarget);
		var newhref = thisanc.attr('href');
		if (newhref) {
				window.opener.location = newhref;				
		}
		window.close();
		//jQuery.progressIndicator();
		e.stopPropagation();
	},
	
	//SG1411 zapper de getListViewEntries � clickListViewEntries pour le popup de construction d'un mgtransport
	registerEventForListViewEntries : function(){
		
		var thisInstance = this;
		var srcmod = thisInstance.getSourceModule();
		if (srcmod && srcmod == 'MGTransports') {
			var popupPageContentsContainer = this.getPopupPageContainer();
			popupPageContentsContainer.on('click','.listViewEntries',function(e){
				thisInstance.clickListViewEntries(e);
				});
		}
		else this._super();		
	},
	//SG1411 jouer seulement sur la chexkbox si le vehicule est dispo; ignorer si vehicule busy
	clickListViewEntries: function(e){
		var thisInstance = this;
		var row  = jQuery(e.currentTarget);
		var dataIsBusyElsewhere = row.hasClass('alreadyBusy');
		var dataIsAlreadySelected = row.hasClass('alreadySelected');
		var dataIsBusy = (dataIsBusyElsewhere || dataIsAlreadySelected);
		
		if (!dataIsAlreadySelected) {
			var thisCheckBox = row.find('td input.entryCheckBox');
			var isChecked = thisCheckBox.is(':checked');
			if (dataIsBusyElsewhere ) {				
				if(!isChecked) {
					if (confirm(app.vtranslate('JS_VEHICULE_IS_BUSY_ELSEWHERE'))) {	
						thisCheckBox.attr('checked','checked').closest('tr').addClass('highlightBackgroundColor');
						}
				}
				else{					
					thisCheckBox.removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');
					}
				}
			else {
				if(!isChecked) {
					thisCheckBox.attr('checked','checked').closest('tr').addClass('highlightBackgroundColor');
					}
					else {
					thisCheckBox.removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');				
					}
			}
		}
		else {
				if (dataIsBusyElsewhere) {
				alert (app.vtranslate('JS_VEHICULE_IS_ALREADY_SELECTED')+ '\n' +
						    app.vtranslate('JS_VEHICULE_IS_BUSY_ELSEWHERE'));
				e.preventDefault();
				}
				else {
				alert(app.vtranslate('JS_VEHICULE_IS_ALREADY_SELECTED'));
				e.preventDefault();
				}
		}
	},
	registerEvents: function(){
		this._super();
		this.disableBusyChauffeurs();
		this.setTextColorForColorTag();
		this.setTextColorForBusyChauffeur();
		this.registerEventForListEntryValueLink();
	}
	

});