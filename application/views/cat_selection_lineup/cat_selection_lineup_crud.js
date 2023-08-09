var addVacancyToLineupWindow;
var lineupHeaderID, lineupVacancyID, applicantID;
var depcode = null;

function selectionLineupCRUD(type) {
	if(type == "Add") {
		var vacancies_grid_ids = Ext.getCmp("vacancies_grid").getSelectionModel();
		var vacancies_ids = [], item_codes = [], item_descs = [], item_desc_details = [];

		for(var i = 0; i < vacancies_grid_ids.store.data.length; i++) {
			var vacancy = vacancies_grid_ids.store.data.items[i].data;
			if(vacancy.is_selected == true) {
				vacancies_ids.push(vacancy.id);
				item_codes.push(vacancy.item_code);
				item_descs.push(vacancy.item_desc);
				item_desc_details.push(vacancy.item_desc_detail);
			}
		}
		
		params = new Object();
		params.type 				= type;
		params.vacancies_ids 	 	= vacancies_ids.toString();
		params.item_codes 		 	= item_codes.toString();
		params.item_descs 		 	= item_descs.toString();
		params.item_desc_details 	= item_desc_details.toString();
		addVacancyToLineupWindow.close();		
	}
	else if(type == "Delete") {
		params = new Object();
		params.type 				= type;
		params.lineup_header_id	 	= lineupHeaderID;
		params.lineup_vacancy_id 	= lineupVacancyID;
		params.lineup_applicant_id  = lineupApplicantID;
		params.applicant_id 		= applicantID;

	}
	deleteFunction('cat_selection_lineup/crud', params, 'list_grid', null);
}

function AddEditDeleteSelectionLineup(type) {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';

	if(type == "Delete") {
		var sm = Ext.getCmp("list_grid").getSelectionModel();
		if(!sm.hasSelection()) {
			warningFunction("Warning!","Please select a vacancy.");
			return;
		}

		lineupHeaderID  	= sm.selected.items[0].data.lineup_header_id;
		lineupVacancyID  	= sm.selected.items[0].data.lineup_vacancy_id;
		lineupApplicantID 	= sm.selected.items[0].data.lineup_applicant_id;
		applicantID  		= sm.selected.items[0].data.applicant_id;
	}

	if(type == "Delete") {
		Ext.Msg.show({
			title	: 'Confirmation',
			msg		: 'Are you sure you want to ' + type + ' vacancy?',
			width	: '100%',
			icon	: Ext.Msg.QUESTION,
			buttons	: Ext.Msg.YESNO,
			fn: function(btn) {
				if(btn == "yes")
					selectionLineupCRUD(type);
			}
		});
	}
	else {
		var departmentsStore = new Ext.data.JsonStore({
	        storeId: 'departmentsStore',
	        proxy: {
	            pageSize: 10,
	            type: 'ajax',
	            url: 'cat_selection_lineup/departments_list',
	            timeout : 1800000,
	            extraParams: {query: query},
	            remoteSort: false,
	            params: {start: 0, limit: 10},
	            reader: {
	                type: 'json',
	                root: 'data',
	                idProperty: 'id',
	                totalProperty: 'totalCount'
	            }
	        },
	        fields: [{name: 'id', type: 'int'}, 'depcode', 'description']
	    }); 

	    var vacanciesStore = new Ext.data.JsonStore({
	        storeId: 'vacanciesStore',
	        proxy: {
	            pageSize: 10,
	            type: 'ajax',
	            url: 'cat_selection_lineup/vacancies_list',
	            timeout : 1800000,
	            extraParams: {query: query, depcode: depcode},
	            remoteSort: false,
	            params: {start: 0, limit: 10},
	            reader: {
	                type: 'json',
	                root: 'data',
	                idProperty: 'id',
	                totalProperty: 'totalCount'
	            }
	        },
	        fields: [{name: 'id', type: 'int'}, 'plantilla_item_no', 'item_code', 'item_desc', 'item_desc_detail', 'posgrade', 'item_details', 'latest_posting', 'is_selected']
	    });

		var RefreshDepartmentsStore = function() {Ext.getCmp("departments_grid").getStore().reload({params:{start:0}, timeout: 300000});};
	    var RefreshVacanciesStore = function() {Ext.getCmp("vacancies_grid").getStore().reload({params:{start:0 }, timeout: 300000});};
	    var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
	        clicksToEdit: 1
	    });

	    var departmentsGrid = Ext.create('Ext.grid.Panel', {
	    	id 		: 'departments_grid',
	    	store 	: departmentsStore,
	    	split 	: true,
	    	region 	: 'west',
	    	columns: [
	    		{dataIndex: 'id', hidden: true},
	            {text: 'Dept. Code', dataIndex: 'depcode', width: '25%'},
	            {text: 'Department', dataIndex: 'description', width: '74%', renderer: addTooltip}
	    	],
	    	columnLines: true,
	    	width 	: '35%',
	    	height 	: 500,
	    	// margin 	: '0 0 10 0',
	    	tbar: [{
	    		xtype 	: 'textfield',
	    		id 		: 'departmentSearchId',
	    		emptyText: 'Search here...',
	    		width 	: '60%',
	    		listeners: {
	    			specialKey: function(field, e) {
	    				if(e.getKey() == e.ENTER) {
	    					Ext.getCmp('departments_grid').getStore().proxy.extraParams["query"] = Ext.getCmp("departmentSearchId").getValue();
	    						query = Ext.getCmp("departmentSearchId").getValue();
	    					RefreshDepartmentsStore();
	    				}
	    			}
	    		}
	    	}],
	    	viewConfig: {
	    		listeners: {
	    			itemclick: function(view, rec, item, index, eventOBj) {
	    				Ext.getCmp("vacancies_grid").getStore().proxy.extraParams["depcode"] = rec.get('depcode');
	    				RefreshVacanciesStore();
	    			}
	    		}
	    	}
	    });
	    RefreshDepartmentsStore();

	    var vacanciesGrid = Ext.create('Ext.grid.Panel', {
	    	id 		: 'vacancies_grid',
	    	store 	: vacanciesStore,
	    	region 	: 'center',
	    	plugins: [cellEditing],
	    	columns: [
	    		{dataIndex: 'id', hidden: true},
	    		{xtype: 'checkcolumn', text: 'Select', dataIndex: 'is_selected', width: '10%', align: 'center', stopSelection: false},
	    		{text: 'Item Code', dataIndex: 'item_code', width: '15%'},
	            {text: 'Item Details', dataIndex: 'item_details', width: '45%', renderer: addTooltip},
	            {text: 'Item<br>No.', dataIndex: 'plantilla_item_no', width: '12%', align: 'center'},
	            {text: 'Latest<br>Posting', dataIndex: 'latest_posting', width: '17%', align: 'center', renderer: dateRenderer}
	    	],
	    	columnLines: true,
	    	width 	: '65%',
	    	height 	: 500,
	    	// margin 	: '0 0 10 0',
	    	tbar: [{
	    		xtype 	: 'textfield',
	    		id 		: 'vacanciesSearchId',
	    		emptyText: 'Search here...',
	    		width 	: '60%',
	    		listeners: {
	    			specialKey: function(field, e) {
	    				if(e.getKey() == e.ENTER) {
	    					Ext.getCmp('vacancies_grid').getStore().proxy.extraParams["query"] = Ext.getCmp("vacanciesSearchId").getValue();
	    					RefreshVacanciesStore();
	    				}
	    			}
	    		}
	    	}]
	    });
	    RefreshVacanciesStore();

		addVacancyToLineupWindow = Ext.create('Ext.window.Window', {
			title		: type + ' Vacancy to Selection Lineup',
			closable	: true,
			modal		: true,
			width		: '50%',
			height 		: '70%',
			resizable	: false,
			buttonAlign	: 'center',
			header 		: {titleAlign: 'center'},
			items 		: [departmentsGrid, vacanciesGrid],
			layout 		: 'border',
			buttons: [{
			    text	: 'Save',
			    icon	: './image/save.png',
			    handler: function() {
					Ext.Msg.show({
						title	: 'Confirmation',
						msg		: 'Are you sure you want to save?',
						width	: '100%',
						icon	: Ext.Msg.QUESTION,
						buttons	: Ext.Msg.YESNO,
						fn: function(btn){
							if(btn == 'yes') {
								selectionLineupCRUD(type);
							}
						}
					});
			    }
			}, {
			    text	: 'Close',
			    icon	: './image/close.png',
			    handler: function() {
			    	addVacancyToLineupWindow.close();
			    }
			}]
		});
		addVacancyToLineupWindow.show();
	}
}

function SaveSelectionLineup() {
	var lineup_applicant_ids = [], lineup_vacancy_ids = [], applicant_ids = [], dates_lineup = [], statuses_hr_test = [], dates_hr_test = [], remarks_hr_test = [], statuses_interview = [], dates_interview = [], remarks_interview = [], are_done_bi = [], are_done_paf = [], are_done_nir = [], remarks = [], statuses_psb = [], are_selected = [], prev_values = [];
	var modified_records = Ext.getCmp("list_grid").getStore().getUpdatedRecords();

	for(var i = 0; i < modified_records.length; i++) {
		var modified_record = modified_records[i].data;
		var modified_columns = modified_records[i].modified;

		lineup_applicant_ids.push(modified_record.lineup_applicant_id);
		lineup_vacancy_ids.push(modified_record.lineup_vacancy_id);
		applicant_ids.push(modified_record.applicant_id);
		dates_lineup.push(modified_record.date_lineup);
		statuses_hr_test.push(modified_record.status_hr_test);
		// reformat date back to 'm/d/y' if date is modified
		if(modified_columns.hasOwnProperty('date_hr_test')) {dates_hr_test.push(Ext.Date.format(modified_record.date_hr_test, 'm/d/y'));}
		else {dates_hr_test.push(modified_record.date_hr_test);}
		remarks_hr_test.push(modified_record.remarks_hr_test);
		statuses_interview.push(modified_record.status_interview);
		// reformat date back to 'm/d/y' if date is modified
		if(modified_columns.hasOwnProperty('date_interview')) {dates_interview.push(Ext.Date.format(modified_record.date_interview, 'm/d/y'));}
		else {dates_interview.push(modified_record.date_interview);}
		remarks_interview.push(modified_record.remarks_interview);
		are_done_bi.push(modified_record.is_done_bi);
		are_done_paf.push(modified_record.is_done_paf);
		are_done_nir.push(modified_record.is_done_nir);
		remarks.push(modified_record.remarks);
		statuses_psb.push(modified_record.status_psb);
		are_selected.push(modified_record.is_selected);

		var old_values_string = JSON.stringify(modified_columns);
		console.log(old_values_string.replaceAll(",", "-"));
		prev_values.push(JSON.stringify(modified_columns).replaceAll(",", "-"));
	}

	params = new Object();
	params.type 				= "Edit";
	params.lineup_applicant_ids = lineup_applicant_ids.toString();
	params.lineup_vacancy_ids 	= lineup_vacancy_ids.toString();
	params.applicant_ids 		= applicant_ids.toString();
	params.dates_lineup 		= dates_lineup.toString();
	params.statuses_hr_test 	= statuses_hr_test.toString();
	params.dates_hr_test 		= dates_hr_test.toString();
	params.remarks_hr_test 	 	= remarks_hr_test.toString();
	params.statuses_interview 	= statuses_interview.toString();
	params.dates_interview 		= dates_interview.toString();
	params.remarks_interview 	= remarks_interview.toString();
	params.are_done_bi 			= are_done_bi.toString();
	params.are_done_paf 	 	= are_done_paf.toString();
	params.are_done_nir 		= are_done_nir.toString();
	params.remarks 				= remarks.toString();
	params.statuses_psb 		= statuses_psb.toString();
	params.are_selected 		= are_selected.toString();
	params.prev_values 			= prev_values.toString();

	deleteFunction('cat_selection_lineup/crud', params, 'list_grid', null);
	// Ext.getCmp("pageToolbar").moveFirst();
	Ext.getCmp("save").setDisabled(true);
}