var detailsWindow, detailsForm;
var educ_level_id, employment_status_id, training_type_id;

function detailsCRUD(crudType, detailType, grid) {
	params = new Object();
	params.id	 		= detailID;
	params.applicantID 	= applicantID;
	params.crudType	 	= crudType;
	params.detailType 	= detailType;
	
	if (crudType == "Delete")
		deleteFunction('cat_applicants_masterlist/detailscrud', params, grid, null);	
	else {
		if(detailType == "Education") {
        	params.educ_level_id = educ_level_id;
        }
        else if(detailType == "Eligibility") {

        }
        else if(detailType == "Experience") {
        	params.employment_status_id = employment_status_id;
        }
        else if(detailType == "Training") {
        	params.training_type_id = training_type_id;
        }
		
		addeditFunction('cat_applicants_masterlist/detailscrud', params, grid, null, detailsForm, detailsWindow);
	}
}

function AddEditDeleteDetails(crudType, detailType, grid) {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';

	if(crudType == "Edit" || crudType == "Delete") {
		var sm = Ext.getCmp(grid).getSelectionModel();
		if(!sm.hasSelection()) {
			warningFunction("Warning!","Please select record.");
			return;
		}

		detailID = sm.selected.items[0].data.id;
	}
	else if(crudType == "Add") 	detailID = 0;

	if(crudType == "Delete") {
		Ext.Msg.show({
			title	: 'Confirmation',
			msg		: 'Are you sure you want to ' + crudType + ' record?',
			width	: '100%',
			icon	: Ext.Msg.QUESTION,
			buttons	: Ext.Msg.YESNO,
			fn: function(btn){
				if(btn == 'yes')
					detailsCRUD(crudType, detailType, grid);
			}
		});
	}
	else {
		if(detailType == "Education") {
			detailsForm = Ext.create('Ext.form.Panel', {
				border 		: false,
				bodyStyle 	: 'padding:15px;',
				fieldDefaults: {
					labelAlign: 'right',
					labelWidth: 110,
					msgTarget: 'side',
					anchor	: '100%',
		        },
		        items: [{
		        	xtype 		: 'fieldset',
		        	title 		: 'Basic',
		        	fieldDefaults: {
		        		afterLabelTextTpl: required,
		        		allowBlank: false
		        	},
		        	items: [{
			        	xtype		: 'combo',
						id			: 'educ_level',
						name		: 'educ_level',
						fieldLabel 	: 'Level',
						mode		: 'local',
						triggerAction: 'all',
						editable	: false,
						store	: new Ext.data.ArrayStore({
							// bind to database table soon
							fields: ['id', 'description'],
							data: [[1, 'Elementary'], [2, 'Secondary'], [3, 'Vocational/Trade Course'], [4, 'College'], [5, 'Graduate Studies']]
						}),
						listeners: {
							beforerender: function(combo, eOpts) {
								educ_level_id = 4;
			                	combo.setValue(educ_level_id);
            					combo.setRawValue("College");
			                },
			                select: function(combo, record, index) {
			                	educ_level_id = record[0].data.id;
			                }
			            },
			            // value 		: 'College',
						valueField 	: 'id',
						displayField: 'description'
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'school',
						name	 	: 'school',
						fieldLabel 	: 'School',
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'course',
						name	 	: 'course',
						fieldLabel 	: 'Degree/Course',
						listeners: {
			                specialKey : function(field, e) {
			                    if(e.getKey() == e.ENTER) {
			                        Ext.getCmp("btn_save").focus();
			                    }
			                }
			            }	
			        }]
		        },{
		        	xtype 		: 'fieldset',
		        	title 		: 'Additional',
		        	fieldDefaults: {
		        		afterLabelTextTpl: null,
		        		allowBlank: true
		        	},
		        	items: [{
			        	xtype		: 'numberfield',	
						id			: 'from_year',
						name		: 'from_year',
						fieldLabel	: 'From'
			        },{
			        	xtype		: 'numberfield',	
						id			: 'to_year',
						name		: 'to_year',
						fieldLabel	: 'To'
			        },{
			        	xtype	 	: 'numberfield',	
						id		 	: 'units_earned',
						name	 	: 'units_earned',
						fieldLabel 	: 'Units Earned',
						emptyText 	: 'If not graduated'
			        },{
			        	xtype		: 'numberfield',	
						id			: 'year_grad',
						name		: 'year_grad',
						fieldLabel	: 'Year Grad.'
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'acad_honor',
						name	 	: 'acad_honor',
						fieldLabel 	: 'Acad. Honors Received'
			        }]
		        }]
			});
		}
		else if(detailType == "Eligibility") {
			detailsForm = Ext.create('Ext.form.Panel', {
				border 		: false,
				bodyStyle 	: 'padding:15px;',
				fieldDefaults: {
					labelAlign: 'right',
					labelWidth: 90,
					msgTarget: 'side',
					anchor	: '100%',
		        },
		        items: [{
		        	xtype 		: 'fieldset',
		        	title 		: 'Basic',
		        	fieldDefaults: {
		        		afterLabelTextTpl: required,
		        		allowBlank: false
		        	},
		        	items:[{
			        	xtype	 	: 'textfield',	
						id		 	: 'title',
						name	 	: 'title',
						fieldLabel 	: 'Title',
						listeners: {
			                specialKey : function(field, e) {
			                    if(e.getKey() == e.ENTER) {
			                        Ext.getCmp("btn_save").focus();
			                    }
			                }
			            }	
			        }]
		        },{
		        	xtype 		: 'fieldset',
		        	title 		: 'Additional',
		        	fieldDefaults: {
		        		afterLabelTextTpl: null,
		        		allowBlank: true
		        	},
		        	items:[{
			        	xtype		: 'numberfield',	
						id			: 'rating',
						name		: 'rating',
						fieldLabel	: 'Rating'
			        },{
			        	xtype		: 'datefield',	
						id			: 'exam_date',
						name		: 'exam_date',
						fieldLabel	: 'Exam Date',
						editable 	: false,
						format 		: "M d, Y",
						emptyText 	: 'Date of Examination/Conferement'
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'exam_place',
						name	 	: 'exam_place',
						fieldLabel 	: 'Exam Place',
						emptyText 	: 'Place of Examination/Conferement'
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'license_no',
						name	 	: 'license_no',
						fieldLabel 	: 'License No.',
						emptyText 	: 'If applicable'
			        },{
			        	xtype		: 'datefield',	
						id			: 'date_validity',
						name		: 'date_validity',
						fieldLabel	: 'Validity',
						editable 	: false,
						format 		: "M d, Y",
						emptyText 	: 'Date of Validity'
			        }]
		        }]
			});
		}
		else if(detailType == "Experience") {
			detailsForm = Ext.create('Ext.form.Panel', {
				border 		: false,
				bodyStyle 	: 'padding:15px;',
				fieldDefaults: {
					labelAlign: 'right',
					labelWidth: 90,
					msgTarget: 'side',
					anchor	: '100%'
		        },
		        items: [{
		        	xtype 		: 'fieldset',
		        	title 		: 'Basic',
		        	fieldDefaults: {
		        		afterLabelTextTpl: required,
		        		allowBlank: false
		        	},
		        	items:[{
			        	xtype		: 'datefield',	
						id			: 'from_date',
						name		: 'from_date',
						fieldLabel	: 'From',
						editable 	: false,
						format 		: "M d, Y",
						emptyText 	: 'mm/dd/yyyy',
						afterLabelTextTpl: null, // remove after initial import
		        		allowBlank: true // remove after initial import
			        },{
			        	xtype		: 'datefield',	
						id			: 'to_date',
						name		: 'to_date',
						fieldLabel	: 'To',
						editable 	: false,
						format 		: "M d, Y",
						emptyText 	: 'Leave blank if until present date.',
						afterLabelTextTpl: null,
		        		allowBlank  : true
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'position',
						name	 	: 'position',
						fieldLabel 	: 'Position Title',
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'agency_company',
						name	 	: 'agency_company',
						fieldLabel 	: 'Agency',
						emptyText 	: 'Department/Agency/Office/Company',
						listeners: {
			                specialKey : function(field, e) {
			                    if(e.getKey() == e.ENTER) {
			                        Ext.getCmp("btn_save").focus();
			                    }
			                }
			            }
			        }]
		        },{
		        	xtype 		: 'fieldset',
		        	title 		: 'Additional',
		        	fieldDefaults: {
		        		afterLabelTextTpl: null,
		        		allowBlank: true
		        	},
		        	items:[{
		        		xtype		: 'numberfield',	
						id			: 'monthly_salary',
						name		: 'monthly_salary',
						fieldLabel	: 'Monthly Salary'
		        	},{
		        		xtype		: 'numberfield',	
						id			: 'salary_grade',
						name		: 'salary_grade',
						fieldLabel	: 'Salary Grade',
						emptyText 	: 'Salary/Job/Pay Grade (if applicable, “00-0” format)'
		        	},{
		        		xtype   		: 'combo',
			            id				: 'employment_status',
			            name 			: 'employment_status',
			            displayField	: 'description',
			            valueField		: 'id',
			            fieldLabel		: 'Appointment Status',
			            triggerAction	: 'all',
			            enableKeyEvents	: true,
			            matchFieldWidth	: true,
			            editable		: false,
			            store: new Ext.data.JsonStore({
					        proxy: {
					            type: 'ajax',
					            url: 'commonquery/combolist',
					            timeout : 1800000,
					            extraParams: {query:null, type: 'employment_statuses'},
					            reader: {
					                type: 'json',
					                root: 'data',
					                idProperty: 'id'
					            }
					        },
					        params: {start: 0, limit: 10},
					        fields: [{name: 'id', type: 'int'}, 'description']
			            }),
						listeners: {
			                select: function(combo, record, index) {
			                	employment_status_id = record[0].data.id;
			                }
			            }
		        	},{
		        		xtype: 'fieldcontainer',
		        		fieldLabel: 'Gov. Service?',
		        		defaultType: 'checkboxfield',
		        		items: [{
		        			boxLabel: 'Yes',
		        			id: 'government_service_yes',
		        			name: 'government_service',
		        			inputValue: 1,
		        			flex: 1
		        		}]
		        	}]
		        }]
			});
		}
		else if(detailType == "Training") {
			detailsForm = Ext.create('Ext.form.Panel', {
				border 		: false,
				bodyStyle 	: 'padding:15px;',
				fieldDefaults: {
					labelAlign: 'right',
					labelWidth: 90,
					msgTarget: 'side',
					anchor	: '100%'
		        },
		        items: [{
		        	xtype 		: 'fieldset',
		        	title 		: 'Basic',
		        	fieldDefaults: {
		        		afterLabelTextTpl: required,
		        		allowBlank: false
		        	},
		        	items:[{
			        	xtype	 	: 'textfield',	
						id		 	: 'title',
						name	 	: 'title',
						fieldLabel 	: 'Title',
			        },{
			        	xtype	 	: 'numberfield',	
						id		 	: 'duration',
						name	 	: 'duration',
						fieldLabel 	: 'No. of Hrs.',
						emptyText 	: '',
						listeners: {
			                specialKey : function(field, e) {
			                    if(e.getKey() == e.ENTER) {
			                        Ext.getCmp("btn_save").focus();
			                    }
			                }
			            }
			        }]
		        },{
		        	xtype 		: 'fieldset',
		        	title 		: 'Additional',
		        	fieldDefaults: {
		        		afterLabelTextTpl: null,
		        		allowBlank: true
		        	},
		        	items:[{
			        	xtype		: 'datefield',	
						id			: 'from_date',
						name		: 'from_date',
						fieldLabel	: 'From',
						editable 	: false,
						format 		: "M d, Y",
						emptyText 	: 'mm/dd/yyyy'
			        },{
			        	xtype		: 'datefield',	
						id			: 'to_date',
						name		: 'to_date',
						fieldLabel	: 'To',
						editable 	: false,
						format 		: "M d, Y",
						emptyText 	: 'mm/dd/yyyy',
						afterLabelTextTpl: null,
		        		allowBlank  : true
			        },{
			        	xtype		:'combo',
						id			: 'training_type',
						name		: 'training_type',
						fieldLabel 	: 'Type',
						mode		: 'local',
						triggerAction: 'all',
						editable	: false,
						store	: new Ext.data.ArrayStore({
							// bind to database table soon
							fields: ['id', 'description'],
							data: [[1, 'Managerial'], [2, 'Supervisory'], [3, 'Technical']]
						}),
						listeners: {
							// beforerender: function(combo, eOpts) {
							// },
							select: function(combo, record, index) {
								training_type_id = record[0].data.id;
							}
			            },
			            // value 		: 'College',
						valueField 	: 'id',
						displayField: 'description'
			        },{
			        	xtype	 	: 'textfield',	
						id		 	: 'conducted_by',
						name	 	: 'conducted_by',
						fieldLabel 	: 'Conducted by',
			        }]
		        }]
			});
		}

		detailsWindow = Ext.create('Ext.window.Window', {
 	 		title		: crudType + ' ' + detailType,
			closable	: true,
			modal		: true,
			width		: 400,
			autoHeight	: true,
			resizable	: false,
			buttonAlign	: 'center',
			header: {titleAlign: 'center'},
			items: [detailsForm],
			buttons: [{
			    text	: 'Save',
			    id 		: 'btn_save',
			    icon	: './image/save.png',
			    handler: function() {
					if(!detailsForm.form.isValid()){
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
								detailsCRUD(crudType, detailType, grid);
							}
						}
					});
			    }
			}, {
			    text	: 'Close',
			    icon	: './image/close.png',
			    handler: function() {
			    	detailsWindow.close();
			    }
			}]
		});

		if(crudType == "Edit") {
			detailsForm.getForm().load({
		        url: 'cat_applicants_masterlist/detailview',
		        timeout: 30000,
		        waitMsg:'Loading data...',
		        params: {id: detailID, detailType: detailType},
		        success: function(form, action) {
		            var data = action.result.data;

		            if(detailType == "Education") {
		            	educ_level_id = data.educ_level;
		            	Ext.get('educ_level').dom.value = educ_level_id;
		            	Ext.getCmp("educ_level").setRawValue(data.educ_level_desc);
		            }
		            else if(detailType == "Eligibility") {

		            }
		            else if(detailType == "Experience") {
		            	employment_status_id = data.employment_status_id;
		            	Ext.get('employment_status').dom.value = employment_status_id;
		            	Ext.getCmp("employment_status").setRawValue(data.employment_status_desc);
		            }
		            else if(detailType == "Training") {
		            	training_type_id = data.training_type_id;
		            	Ext.get('training_type').dom.value = training_type_id;
		            	Ext.getCmp("training_type").setRawValue(data.training_type_desc);
		            }
		        },      
		        failure: function(f,action) { errorFunction("Error!",'Please contact system administrator.');}
		    });
		    detailsWindow.show();
		}
		else {
			detailsWindow.show();
			employment_status_id = null;
			if(detailType == "Education") Ext.getCmp("school").focus();
			else if(detailType == "Eligibility") Ext.getCmp("title").focus();
			else if(detailType == "Experience") Ext.getCmp("from_date").focus();
			else if(detailType == "Training") Ext.getCmp("title").focus();
		}
	}
}