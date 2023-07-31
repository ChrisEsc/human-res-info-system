var existingApplicantID, applicantID, applicantWindow;

function applicantCRUD(type) {
	params = new Object();
	params.id 			= selectionLineupID;
	if(type == "Add") 	{
		params.applicant_id = applicantID;
	}
	params.type 		= type;

	deleteFunction('cat_selection_lineup/applicant_crud', params, 'list_grid', null);
	if(type == "Add") 	applicantWindow.close();
}

function AddEditDeleteApplicant(type) {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';
	var sm = Ext.getCmp("list_grid").getSelectionModel();
	
	selectionLineupID = sm.selected.items[0].data.lineup_applicant_id;
	// existingApplicantID = sm.selected.items[0].data.applicant_id;
	
	if(type == "Delete") {
		if(sm.selected.items[0].data.applicant_id == null) {
			warningFunction("Warning!","Please select an applicant.");
			return;
		}
		else if(sm.selected.items[0].data.applicant_id == "0") {
			warningFunction("Warning!","There is no applicant to be removed.");
			return;
		}

		Ext.Msg.show({
			title 	: 'Confirmation',
			msg 	: 'Are you sure you want to remove applicant?',
			width 	: '100%',
			icon 	: Ext.Msg.QUESTION,
			buttons : Ext.Msg.YESNO,
			fn: function(btn) {
				if(btn == "yes") {
					applicantCRUD(type);
				}
			}
		});
	}
	else {
		var applicantsStore = new Ext.data.JsonStore({
			storeId: 'applicantsStore',
			proxy: {
				pageSize: 10,
				type: 'ajax',
				url: 'cat_applicants_masterlist/applicants_list',
				timeout: 1800000,
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
			fields: [{name: 'id', type: 'int'}, 'applicant_name', 'phone_no', 'email_add', 'educ_highest', 'eligibility', 'experience']
		});

		var RefreshApplicantsStore = function() {Ext.getCmp("applicants_grid").getStore().reload({params:{start:0 }, timeout: 300000});};

		var applicantsGrid = Ext.create('Ext.grid.Panel', {
			id 		: 'applicants_grid',
			store 	: applicantsStore,
			split 	: true,
			region 	: 'center',
			columns : [
				Ext.create('Ext.grid.RowNumberer', {width: 25}),
	            {dataIndex: 'id', hidden: true},
	            {text: 'Name', dataIndex: 'applicant_name', align: 'left', width: '27%', renderer:columnWrap},
	            {text: 'Phone No.',dataIndex: 'phone_no', align: 'left', width: '10%', hidden: true, renderer:columnWrap},
	            {text: 'Email Add.', dataIndex: 'email_add', align: 'left', width: '15%',  hidden: true,renderer:addTooltip},
	            {text: 'Course', dataIndex: 'educ_highest', align: 'left', width: '25%', renderer:columnWrap},
	            {text: 'Eligibility', dataIndex: 'eligibility', align: 'left', width: '20%', renderer:columnWrap},
	            {text: 'Experience', dataIndex: 'experience', align: 'left', width: '25%', renderer:columnWrap}
			],
			columnLines: false,
			width 	: '100%',
			height 	: 500,
			tbar: [{
	    		xtype 	: 'textfield',
	    		id 		: 'applicantsSearchId',
	    		emptyText: 'Search here...',
	    		width 	: '40%',
	    		listeners: {
	    			specialKey: function(field, e) {
	    				if(e.getKey() == e.ENTER) {
	    					Ext.getCmp('applicants_grid').getStore().proxy.extraParams["query"] = Ext.getCmp("applicantsSearchId").getValue();
	    						query = Ext.getCmp("applicantsSearchId").getValue();
	    					RefreshApplicantsStore();
	    				}
	    			}
	    		}
	    	}],
	    	viewConfig: {
	    		listeners: {
	    			itemdblclick: function(view, record , item, index, e, eOpts) {
	    				// double click action not consistent
	    				// var sm_inner = Ext.getCmp("applicants_grid").getSelectionModel();
	    				
						// Ext.Msg.show({
						// 	title	: 'Confirmation',
						// 	msg		: 'Are you sure you want to save?',
						// 	width	: '100%',
						// 	icon	: Ext.Msg.QUESTION,
						// 	buttons	: Ext.Msg.YESNO,
						// 	fn: function(btn){
						// 		if(btn == 'yes') {
						// 			applicantID = sm_inner.selected.items[0].data.id;
						// 			applicantCRUD(type);
						// 		}
						// 	}
						// });
	    			}
	    		}
	    	}
		});
		RefreshApplicantsStore();

		applicantWindow = Ext.create('Ext.window.Window', {
			title 		: 'Place Applicant',
			closable	: true,
			modal		: true,
			width		: '50%',
			height 		: '70%',
			resizable	: false,
			buttonAlign	: 'center',
			header 		: {titleAlign: 'center'},
			items 		: [applicantsGrid],
			layout 		: 'border',
			buttons: [{
			    text	: 'Place',
			    icon	: './image/save.png',
			    handler: function() {
					var sm_inner = Ext.getCmp("applicants_grid").getSelectionModel();
					if(!sm_inner.hasSelection()) {
						warningFunction("Warning!","Please select an applicant.");
						return;
					}
					Ext.Msg.show({
						title	: 'Confirmation',
						msg		: 'Are you sure you want to save?',
						width	: '100%',
						icon	: Ext.Msg.QUESTION,
						buttons	: Ext.Msg.YESNO,
						fn: function(btn){
							if(btn == 'yes') {
								applicantID = sm_inner.selected.items[0].data.id;
								applicantCRUD(type);
							}
						}
					});
			    }
			}, {
			    text	: 'Close',
			    icon	: './image/close.png',
			    handler: function() {
			    	applicantWindow.close();
			    }
			}]
		});

		applicantWindow.show();
	}
}