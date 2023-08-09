var applicationWindow, applicationForm;
var id, applicant_id, applic_type_desc;

function applicationCRUD() {
	params = new Object();
	params.id 							= id;
	params.applicant_id  				= applicant_id;
	params.date_application_received  	= Ext.getCmp("date_application_received").getValue();
	params.position_applied  			= Ext.getCmp("position_applied").getValue();
	params.notes  						= Ext.getCmp("notes").getValue();
	params.applic_type_desc  			= applic_type_desc;
	addeditFunction('cat_applicants_masterlist/applicationcrud', params, 'list_grid', null, applicationForm, applicationWindow);
}

function UpdateApplication() {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';
	var sm = Ext.getCmp("list_grid").getSelectionModel();
	
	if(!sm.hasSelection()) {
		warningFunction("Warning!","Please select an applicant.");
		return;
	}

	applicant_id = sm.selected.items[0].data.id;
	applicationForm = Ext.create('Ext.form.Panel', {
		border 		: false,
		bodyStyle 	: 'padding:15px;',
		fieldDefaults: {
			labelAlign 	: 'right',
			labelWidth 	: 100,
			msgTarget 	: 'side',
			anchor	 	: '100%',
			afterLabelTextTpl: required,
		    allowBlank 	: false
        },
        items: [{
        	xtype		: 'datefield',	
			id			: 'date_application_received',
			name		: 'date_application_received',
			fieldLabel	: 'Date Received',
			editable 	: false,
			emptyText 	: 'mm/dd/yyyy'
        }, {
        	xtype		: 'textarea',
			id			: 'position_applied',
			name		: 'position_applied',
			fieldLabel	: 'Position/s Applied',
			afterLabelTextTpl: null,
		    allowBlank 	: true
        }, {
        	xtype		: 'textarea',
			id			: 'notes',
			name		: 'notes',
			fieldLabel	: 'Notes',
			afterLabelTextTpl: null,
		    allowBlank 	: true
        }, {
        	xtype		: 'combo',
			id			: 'applic_type',
			name		: 'applic_type',
			fieldLabel 	: 'Type',
			mode		: 'local',
			triggerAction: 'all',
			editable	: false,
			store	: new Ext.data.ArrayStore({
				fields: ['id', 'description'],
				data: [[1, 'Internal'], [2, 'External']]
			}),
			listeners: {
                select: function(combo, record, index) {
                	applic_type_desc = record[0].data.description;
                }
            },
			valueField 	: 'id',
			displayField: 'description'
        }]
	});

	applicationWindow = Ext.create('Ext.window.Window', {
		title		: 'Update Application Details',
		closable	: true,
		modal		: true,
		width		: 400,
		autoHeight	: true,
		resizable	: false,
		buttonAlign	: 'center',
		header: {titleAlign: 'center'},
		items: [applicationForm],
		buttons: [{
		    text	: 'Save',
		    id 		: 'btn_save',
		    icon	: './image/save.png',
		    handler: function() {
				if(!applicationForm.form.isValid()){
					errorFunction("Error!",'Please fill-in the required fields (Marked red).');
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
							applicationCRUD();
						}
					}
				});
		    }
		}, {
		    text	: 'Close',
		    icon	: './image/close.png',
		    handler: function() {
		    	applicationWindow.close();
		    }
		}]
	});
	
	applicationForm.getForm().load({
		url: 'cat_applicants_masterlist/applicationview',
		timeout: 30000,
		waitMsg:'Loading data...',
		params: {applicant_id: applicant_id},
		success: function(form, action) {
			var data = action.result.data;

			id = data.id;        	
		},
		failure: function(f,action) { errorFunction("Error!", 'Please contact system administrator.');}
	})
	applicationWindow.show();
}