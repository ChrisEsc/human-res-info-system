var psbWindow, psbForm;
var lineup_header_id = null, lineup_vacancy_id = null;
var selected_lineup_applicant_id;

function psbCRUD() {
	params = new Object();
	params.lineup_header_id = lineup_header_id;
	params.lineup_vacancy_id = lineup_vacancy_id;
	params.selected_lineup_applicant_id = selected_lineup_applicant_id;

	addeditFunction('cat_psb/crud', params, 'list_grid', null, psbForm, psbWindow);
}

function UpdatePSB() {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';
	var sm = Ext.getCmp("list_grid").getSelectionModel();
	
	if(!sm.hasSelection()) {
		warningFunction("Warning!","Please select a vacancy.");
		return;
	}

	if(sm.selected.items[0].data.is_locked == 1) {
		infoFunction("Locked!","Cannot update because selection process of this item is completed.");
		return;
	}

	lineup_header_id = sm.selected.items[0].data.lineup_header_id;
	lineup_vacancy_id = sm.selected.items[0].data.lineup_vacancy_id;

	psbForm = Ext.create('Ext.form.Panel', {
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
        	xtype       	: 'combo',
            id          	: 'selected_lineup_applicant',
            name          	: 'selected_lineup_applicant',
            fieldLabel  	: 'Selected Applic.',
            valueField  	: 'id',
            displayField	: 'description',
            // emptyText		: 'Select Applicant',
            triggerAction 	: 'all',
            value 			: 1,	//defaults to 'letter'
            minChars    	: 3,
            enableKeyEvents	: true,
            matchFieldWidth : true,
            forceSelection  : true,
            editable 		: false,
            store: new Ext.data.JsonStore({
                proxy: {
                    type 	: 'ajax',
                    url 	: 'cat_psb/lineupapplicants_list',
                    timeout : 1800000,
                    extraParams: {lineup_header_id: lineup_header_id},
                    reader 	: {
                        type 	: 'json',
                        root 	: 'data',
                        idProperty: 'id'
                    }
                },
                params: {},
                fields: [{name: 'id', type: 'int'}, 'description', 'is_selected']
            }),
            listeners: {
                select: function(combo, record, index) {        
                    selected_lineup_applicant_id = record[0].data.id;
                }
            }
        },{
        	xtype		: 'datefield',	
			id			: 'date_psb',
			name		: 'date_psb',
			fieldLabel	: 'PSB Date',
			editable 	: false,
			emptyText 	: 'mm/dd/yyyy'
        },{
        	xtype		: 'textarea',
			id			: 'remarks',
			name		: 'remarks',
			fieldLabel	: 'Remarks',
			afterLabelTextTpl: null,
		    allowBlank 	: true
        }]
	});

	psbWindow = Ext.create('Ext.window.Window', {
		title		: 'Update PSB Details',
		closable	: true,
		modal		: true,
		width		: 400,
		autoHeight	: true,
		resizable	: false,
		buttonAlign	: 'center',
		header: {titleAlign: 'center'},
		items: [psbForm],
		buttons: [{
		    text	: 'Save',
		    id 		: 'btn_save',
		    icon	: './image/save.png',
		    handler: function() {
				if(!psbForm.form.isValid()){
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
							psbCRUD();
						}
					}
				});
		    }
		}, {
		    text	: 'Close',
		    icon	: './image/close.png',
		    handler: function() {
		    	psbWindow.close();
		    }
		}]
	});
	
	psbForm.getForm().load({
		url: 'cat_psb/psbview',
		timeout: 30000,
		waitMsg:'Loading data...',
		params: {id: lineup_vacancy_id},
		success: function(form, action) {
			var data = action.result.data;

			selected_lineup_applicant_id = data.selected_lineup_applicant_id;
			if(selected_lineup_applicant_id != null) {
				Ext.get('selected_lineup_applicant').dom.value = selected_lineup_applicant_id;
        		Ext.getCmp("selected_lineup_applicant").setRawValue(data.selected_applicant_name);
			}
			else {
				Ext.getCmp("selected_lineup_applicant").getStore().filter("is_selected", null);
			}
        	
		},
		failure: function(f,action) { errorFunction("Error!", 'Please contact system administrator.');}
	})
	psbWindow.show();
}