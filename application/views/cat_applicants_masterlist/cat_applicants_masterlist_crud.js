var applicantID, applicantWindow, applicantForm;

function applicantCRUD(type) {
	params 				= new Object();
	params.id			= applicantID;
	params.type 		= type;

	if(type == "Add" || type == "Edit") {
		params.fname  	 	= Ext.getCmp("fname").getValue();
		params.mname 	 	= Ext.getCmp("mname").getValue();
		params.lname 	 	= Ext.getCmp("lname").getValue();
		params.suffix  	 	= Ext.getCmp("suffix").getValue();
		params.phone_no  	= Ext.getCmp("phone_no").getValue();
		params.email_add  	= Ext.getCmp("email_add").getValue();
	}
	
	deleteFunction('cat_applicants_masterlist/crud', params, 'list_grid', null);
	Ext.getCmp("pageToolbar").moveFirst();
}

function AddEditDeleteApplicant(type) {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';

	if(type == 'Edit' || type == 'Delete') {
		var sm = Ext.getCmp("list_grid").getSelectionModel();
		if(!sm.hasSelection()) {
			warningFunction("Warning!","Please select record.");
			return;
		}
		applicantID = sm.selected.items[0].data.id;
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
					applicantCRUD(type);
			}
		});
	}
	else {
		if(type == "Add") applicantID = 0;
		
		Ext.Ajax.request({
		    url     :"cat_applicants_masterlist/initialcrud",
		    method  : 'POST',
		    params	: {id: applicantID, type: type},
		    success: function(f,a) {
		        var response = Ext.decode(f.responseText);
		        applicantID = response.id;
		        AddEditDeleteApplicantDetails(type);
		    }
		});
	}
}