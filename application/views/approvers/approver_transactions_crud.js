var transactionWindow, transactionID, form;

function transactionCRUD(type) {
	params = new Object();
	params.id	= transactionID;
	params.type	= type;

	if(type == "Delete")
		deleteFunction('approvers/transactioncrud', params, 'transactionsGrid', null);
	else
		addeditFunction('approvers/transactioncrud', params, 'transactionsGrid', null, form, transactionWindow);
}

function AddEditDeleteTransaction(type) {          
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';

	if(type == 'Edit' || type == 'Delete') {
		var sm = Ext.getCmp("transactionsGrid").getSelectionModel();
		if(!sm.hasSelection()) {
			warningFunction("Warning!","Please select a record.");
			return;
		}
		this.transactionID = sm.selected.items[0].data.id;
	}

	if(type == "Delete") {
		Ext.Msg.show({
			title	: 'Confirmation',
			msg		: 'Are you sure you want to ' + type + ' record?',
			width	: '100%',
			icon	: Ext.Msg.QUESTION,
			buttons	: Ext.Msg.YESNO,
			fn: function(btn){
				if(btn == 'yes')
					transactionCRUD(type);
			}
		});
	}
	else {
		form = Ext.create('Ext.form.Panel', {
			border		: false,
			bodyStyle	: 'padding:15px;',		
			fieldDefaults: {
				labelAlign	: 'right',
				labelWidth: 90,
				afterLabelTextTpl: required,
				msgTarget: 'side',
				anchor	: '100%',
				allowBlank: false
	        },
			items: [{
				xtype	: 'textfield',	
				id		: 'code',				
				name	: 'code',		
				maxLength: 10,		
				fieldLabel: 'Code'
			}, {
				xtype	: 'textfield',	
				name	: 'transaction',				
				fieldLabel: 'Transaction'
            }]
		});

		transactionWindow = Ext.create('Ext.window.Window', {
			title		: type + ' Transaction',
			closable	: true,
			modal		: true,
			width		: 400,
			autoHeight	:true,
			resizable	: false,
			buttonAlign	: 'center',
			header: {titleAlign: 'center'},
			items: [form],
			buttons: [{
			    text	: 'Save',
			    icon	: './image/save.png',
			    handler: function() {
					if(!form.form.isValid()){
						errorFunction("Error!",'Please fill-in the required fields (Marked red).');
					    return;
			        }
					Ext.Msg.show({
						title	: 'Confirmation',
						msg		: 'Are you sure you want to Save?',
						width	: '100%',
						icon	: Ext.Msg.QUESTION,
						buttons	: Ext.Msg.YESNO,
						fn: function(btn){
							if(btn == 'yes')
								transactionCRUD(type);
						}
					});
			    }
			}, {
			    text	: 'Close',
			    icon	: './image/close.png',
			    handler: function() {
			    	transactionWindow.close();
			    }
			}],
		});

		if(type == 'Edit') {
			form.getForm().load({
				url: 'approvers/transactionview',
				timeout: 30000,
				waitMsg:'Loading data...',
				params: {
					id: this.transactionID
				},
				success: function(form, action) {
					transactionWindow.show();
					Ext.getCmp("code").setReadOnly(true);
				},
				failure: function(f,action) { errorFunction("Error!",'Please contact system administrator.'); }
			});
		}
		else
			transactionWindow.show();
		
		Ext.getCmp("code").focus();
	}
}