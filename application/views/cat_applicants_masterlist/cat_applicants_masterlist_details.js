function AddEditDeleteApplicantDetails(type) {
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';

	var educationStore = new Ext.data.JsonStore({
        storeId: 'educationStore',
        proxy: {
            pageSize: 10,
            type: 'ajax',
            url: 'cat_applicants_masterlist/details_list',
            timeout : 1800000,
            extraParams: {applicantID: applicantID, detailType: 'Education', query:query},
            remoteSort: false,
            params: {start: 0, limit: 10},
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'id',
                totalProperty: 'totalCount'
            }
        },
        fields: [{name: 'id', type: 'int'}, 'educ_level', 'educ_level_desc', 'school', 'course', 'from_year', 'to_year', 'units_earned', 'year_grad', 'acad_honor']
    }); 

    var eligibilityStore = new Ext.data.JsonStore({
        storeId: 'eligibilityStore',
        proxy: {
            pageSize: 10,
            type: 'ajax',
            url: 'cat_applicants_masterlist/details_list',
            timeout : 1800000,
            extraParams: {applicantID: applicantID, detailType: 'Eligibility', query:query},
            remoteSort: false,
            params: {start: 0, limit: 10},
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'id',
                totalProperty: 'totalCount'
            }
        },
        fields: [{name: 'id', type: 'int'}, 'title', 'rating', 'exam_date', 'exam_place', 'license_no', 'date_validity']
    });

    var experienceStore = new Ext.data.JsonStore({
        storeId: 'experienceStore',
        proxy: {
            pageSize: 10,
            type: 'ajax',
            url: 'cat_applicants_masterlist/details_list',
            timeout : 1800000,
            extraParams: {applicantID: applicantID, detailType: 'Experience', query:query},
            remoteSort: false,
            params: {start: 0, limit: 10},
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'id',
                totalProperty: 'totalCount'
            }
        },
        listeners: {
            load: function(store, records, successful, eOpts) {
            }
        },
        fields: [{name: 'id', type: 'int'}, {name: 'employment_status_id', type: 'int'}, 'employment_status_desc', 'from_date', 'to_date', 'position', 'agency_company', 'monthly_salary', 'salary_grade', 'employment_status', 'government_service']
    });

    var trainingStore = new Ext.data.JsonStore({
        storeId: 'trainingStore',
        proxy: {
            pageSize: 10,
            type: 'ajax',
            url: 'cat_applicants_masterlist/details_list',
            timeout : 1800000,
            extraParams: {applicantID: applicantID, detailType: 'Training', query:query},
            remoteSort: false,
            params: {start: 0, limit: 10},
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'id',
                totalProperty: 'totalCount'
            }
        },
        listeners: {
            load: function(store, records, successful, eOpts) {
            }
        },
        fields: [{name: 'id', type: 'int'}, {name: 'training_type_id', type: 'int'}, 'title', 'from_date', 'to_date', 'duration', 'training_type_desc', 'conducted_by']
    });

	var RefreshEducationStore = function() {Ext.getCmp("education_grid").getStore().reload({params:{start:0 }, timeout: 300000});};
    var RefreshEligibilityStore = function() {Ext.getCmp("eligibility_grid").getStore().reload({params:{start:0 }, timeout: 300000});};
    var RefreshExperienceStore = function() {Ext.getCmp("experience_grid").getStore().reload({params:{start:0 }, timeout: 300000});};
    var RefreshTrainingStore = function() {Ext.getCmp("training_grid").getStore().reload({params:{start:0 }, timeout: 300000});};

    var educationGrid = Ext.create('Ext.grid.Panel', {
        id      : 'education_grid',
        store   : educationStore,
        cls     : 'gridCss',
        columns: [
            {dataIndex: 'id', hidden: true},
            {text: 'Level', dataIndex: 'educ_level_desc', align: 'left', width: '15%', renderer:columnWrap},
            {text: 'School', dataIndex: 'school', align: 'left', width: '15%', renderer:columnWrap},
            {text: 'Degree/Course', dataIndex: 'course', align: 'left', width: '25%', renderer:columnWrap},
            {
                text: 'Period', 
                columns: [{
                    text: 'From', 
                    dataIndex: 'from_year', 
                    align: 'center', 
                    width: 60, 
                    renderer:columnWrap
                },{
                    text: 'To', 
                    dataIndex: 'to_year',
                    align: 'center', 
                    width: 60,  
                    renderer:columnWrap
                 }]
            },
            {text: 'Highest Level/<br>Units Earned', dataIndex: 'units_earned', align: 'left', width: '7%', renderer:columnWrap},
            {text: 'Year<br>Graduated', dataIndex: 'year_grad', align: 'left', width: '7%', renderer:columnWrap},
            {text: 'Scholarship/<br>Academic Honors', dataIndex: 'acad_honor', align: 'left', width: '15%', renderer:columnWrap}
        ],
        width   : '100%',
        height  : 500,
        margin  : '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {
                    // ViewRecord();
                },
                itemcontextmenu: function(view, record, item, index, e){
                    e.stopEvent();
                    educationRowMenu.showAt(e.getXY());
                }
            }
        },
        tbar: [{
                xtype   : 'textfield',
                id      : 'search_educ',
                emptyText: 'Search here...',
                width   : '25%',
                listeners: {
                    specialKey : function(field, e) {
                        if(e.getKey() == e.ENTER) {
                            Ext.getCmp("education_grid").getStore().proxy.extraParams["query"] = Ext.getCmp("search_educ").getValue();
                            query = Ext.getCmp("search_educ").getValue();
                            RefreshEducationStore();
                        }
                    }
                }
            },
            { xtype: 'tbfill'},
            { xtype: 'button', id: 'add_educ', text: 'ADD', icon: './image/add.png', tooltip: 'Add Education', 
                handler: function(){
                    if(!applicantForm.form.isValid()){errorFunction("Error!",'Please fill-in first the required fields (Marked red).');return;}
                    AddEditDeleteDetails("Add", "Education", "education_grid");
                }
            },
            { xtype: 'button', id: 'edit_educ', text: 'EDIT', icon: './image/edit.png', tooltip: 'Edit Education', handler: function(){ AddEditDeleteDetails("Edit", "Education", "education_grid");}},
            { xtype: 'button', id: 'delete_educ', text: 'DELETE', icon: './image/delete.png', tooltip: 'Delete Education', handler: function(){ AddEditDeleteDetails("Delete", "Education", "education_grid");}}
        ]
    });
    RefreshEducationStore();

    var eligibilityGrid = Ext.create('Ext.grid.Panel', {
        id      : 'eligibility_grid',
        store   : eligibilityStore,
        cls     : 'gridCss',
        columns: [
            {dataIndex: 'id', hidden: true},
            {text: 'Eligility', dataIndex: 'title', align: 'left', width: '35%', renderer:columnWrap},
            {text: 'Rating',dataIndex: 'rating', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Date of<br>Exam/Conferement', dataIndex: 'exam_date', align: 'left', width: '15%', renderer: Ext.util.Format.dateRenderer('M d, Y')},
            {text: 'Place',dataIndex: 'exam_place', align: 'left', width: '15%', renderer:columnWrap},
            {
                text: 'License (if applicable)', 
                columns: [{
                    text: 'Number', 
                    dataIndex: 'license_no', 
                    align: 'center', 
                    width: 80, 
                    renderer:columnWrap
                },{
                    text: 'Validity', 
                    dataIndex: 'date_validity',
                    align: 'center', 
                    width: 110,  
                    renderer: Ext.util.Format.dateRenderer('M d, Y')
                 }]
            },
        ],
        width   : '100%',
        height  : 500,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {
                    // ViewRecord();
                },
                itemcontextmenu: function(view, record, item, index, e){
                    e.stopEvent();
                    eligibilityRowMenu.showAt(e.getXY());
                }
            }
        },
        tbar: [{
                xtype   : 'textfield',
                id      : 'search_eligibility',
                emptyText: 'Search here...',
                width   : '25%',
                listeners: {
                    specialKey : function(field, e) {
                        if(e.getKey() == e.ENTER) {
                            Ext.getCmp("eligibility_grid").getStore().proxy.extraParams["query"] = Ext.getCmp("search_eligibility").getValue();
                            query = Ext.getCmp("search_eligibility").getValue();
                            RefreshEligibilityStore();
                        }
                    }
                }
            },
            { xtype: 'tbfill'},
            { xtype: 'button', id: 'add_eligibility', text: 'ADD', icon: './image/add.png', tooltip: 'Add Eligibility', 
                handler: function(){
                    if(!applicantForm.form.isValid()){errorFunction("Error!",'Please fill-in first the required fields (Marked red).');return;}
                    AddEditDeleteDetails("Add", "Eligibility", "eligibility_grid");
                }
            },
            { xtype: 'button', id: 'edit_eligibility', text: 'EDIT', icon: './image/edit.png', tooltip: 'Edit Eligibility', handler: function(){ AddEditDeleteDetails("Edit", "Eligibility", "eligibility_grid");}},
            { xtype: 'button', id: 'delete_eligibility', text: 'DELETE', icon: './image/delete.png', tooltip: 'Delete Eligibility', handler: function(){ AddEditDeleteDetails("Delete", "Eligibility", "eligibility_grid");}}
        ]
    });
    RefreshEligibilityStore();

    var experienceGrid = Ext.create('Ext.grid.Panel', {
        id      : 'experience_grid',
        store   : experienceStore,
        cls     : 'gridCss',
        columns: [
            {dataIndex: 'id', hidden: true},
            {
                text: 'Inclusive Dates',
                columns: [{
                    text: 'From', 
                    dataIndex: 'from_date', 
                    align: 'center', 
                    width: 100, 
                    renderer: Ext.util.Format.dateRenderer('M d, Y')
                },{
                    text: 'To', 
                    dataIndex: 'to_date',
                    align: 'center', 
                    width: 100,  
                    renderer: dateRenderer
                 }]
            },
            {text: 'Position', dataIndex: 'position', align: 'left', width: '21%', renderer:columnWrap},
            {text: 'Dept/Agency/<br>Office/Company',dataIndex: 'agency_company', align: 'left', width: '20%', renderer:columnWrap},
            {text: 'Monthly<br>Salary', dataIndex: 'monthly_salary', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'SG', dataIndex: 'salary_grade', align: 'left', width: '5%', renderer:columnWrap},
            {text: 'Appt.<br>Status', dataIndex: 'employment_status_desc', align: 'left', width: '13%', renderer:columnWrap},
            {xtype: 'checkcolumn', text: 'Gov\'t<br>Service', dataIndex: 'government_service', align: 'left', width: '5%', renderer:columnWrap, listeners:{beforecheckchange: function() {return false;}}}
        ],
        width   : '100%',
        height  : 500,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {
                    // ViewRecord();
                },
                itemcontextmenu: function(view, record, item, index, e){
                    e.stopEvent();
                    experienceRowMenu.showAt(e.getXY());
                }
            }
        },
        tbar: [{
                xtype   : 'textfield',
                id      : 'search_experience',
                emptyText: 'Search here...',
                width   : '25%',
                listeners: {
                    specialKey : function(field, e) {
                        if(e.getKey() == e.ENTER) {
                            Ext.getCmp("experience_grid").getStore().proxy.extraParams["query"] = Ext.getCmp("search_experience").getValue();
                            query = Ext.getCmp("search_experience").getValue();
                            RefreshExperienceStore();
                        }
                    }
                }
            },
            { xtype: 'tbfill'},
            { xtype: 'button', id: 'add_experience', text: 'ADD', icon: './image/add.png', tooltip: 'Add Experience', 
                handler: function() {
                    if(!applicantForm.form.isValid()){errorFunction("Error!",'Please fill-in first the required fields (Marked red).');return;}
                    AddEditDeleteDetails("Add", "Experience", "experience_grid");
                }
            },
            { xtype: 'button', id: 'edit_experience', text: 'EDIT', icon: './image/edit.png', tooltip: 'Edit Experience', handler: function(){ AddEditDeleteDetails("Edit", "Experience", "experience_grid");}},
            { xtype: 'button', id: 'delete_experience', text: 'DELETE', icon: './image/delete.png', tooltip: 'Delete Experience', handler: function(){ AddEditDeleteDetails("Delete", "Experience", "experience_grid");}}
        ]
    });
    RefreshExperienceStore();

    var trainingGrid = Ext.create('Ext.grid.Panel', {
        id      : 'training_grid',
        store   : trainingStore,
        cls     : 'gridCss',
        columns: [
            {dataIndex: 'id', hidden: true},
            {text: 'Title', dataIndex: 'title', align: 'left', width: '30%', renderer:columnWrap},
            {
                text: 'Inclusive Dates',
                columns: [{
                    text: 'From', 
                    dataIndex: 'from_date', 
                    align: 'center', 
                    width: 100, 
                    renderer: dateRenderer
                },{
                    text: 'To', 
                    dataIndex: 'to_date',
                    align: 'center', 
                    width: 100,  
                    renderer: dateRenderer
                 }]
            },
            {text: 'No. of<br>Hrs.',dataIndex: 'duration', align: 'center', width: '10%', renderer:columnWrap},
            {text: 'Type', dataIndex: 'training_type_desc', align: 'left', width: '19%', renderer:columnWrap},
            {text: 'Conducted/<br>Sponsored by', dataIndex: 'conducted_by', align: 'left', width: '15%', renderer:columnWrap}
        ],
        width   : '100%',
        height  : 500,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {
                    // ViewRecord();
                },
                itemcontextmenu: function(view, record, item, index, e){
                    e.stopEvent();
                    trainingRowMenu.showAt(e.getXY());
                }
            }
        },
        tbar: [{
                xtype   : 'textfield',
                id      : 'search_training',
                emptyText: 'Search here...',
                width   : '25%',
                listeners: {
                    specialKey : function(field, e) {
                        if(e.getKey() == e.ENTER) {
                            Ext.getCmp("training_grid").getStore().proxy.extraParams["query"] = Ext.getCmp("search_training").getValue();
                            query = Ext.getCmp("search_training").getValue();
                            RefreshExperienceStore();
                        }
                    }
                }
            },
            { xtype: 'tbfill'},
            { xtype: 'button', id: 'add_training', text: 'ADD', icon: './image/add.png', tooltip: 'Add Training', 
                handler: function() {
                    if(!applicantForm.form.isValid()){errorFunction("Error!",'Please fill-in first the required fields (Marked red).');return;}
                    AddEditDeleteDetails("Add", "Training", "training_grid");
                }
            },
            { xtype: 'button', id: 'edit_training', text: 'EDIT', icon: './image/edit.png', tooltip: 'Edit Training', handler: function(){ AddEditDeleteDetails("Edit", "Training", "training_grid");}},
            { xtype: 'button', id: 'delete_training', text: 'DELETE', icon: './image/delete.png', tooltip: 'Delete Training', handler: function(){ AddEditDeleteDetails("Delete", "Training", "training_grid");}}
        ]
    });
    RefreshTrainingStore();

    var educationRowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            text: 'Add',
            icon: './image/add.png',
            handler: function(){ AddEditDeleteDetails("Add", "Education", "education_grid");}
        }, {
            text: 'Edit',
            icon: './image/edit.png',
            handler: function(){ AddEditDeleteDetails("Edit", "Education", "education_grid");}
        }, {
            text: 'Delete',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteDetails("Delete", "Education", "education_grid");}
        }]
    });

    var eligibilityRowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            text: 'Add',
            icon: './image/add.png',
            handler: function(){ AddEditDeleteDetails("Add", "Eligibility", "eligibility_grid");}
        }, {
            text: 'Edit',
            icon: './image/edit.png',
            handler: function(){ AddEditDeleteDetails("Edit", "Eligibility", "eligibility_grid");}
        }, {
            text: 'Delete',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteDetails("Delete", "Eligibility", "eligibility_grid");}
        }]
    });

    var experienceRowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            text: 'Add',
            icon: './image/add.png',
            handler: function(){ AddEditDeleteDetails("Add", "Experience", "experience_grid");}
        }, {
            text: 'Edit',
            icon: './image/edit.png',
            handler: function(){ AddEditDeleteDetails("Edit", "Experience", "experience_grid");}
        }, {
            text: 'Delete',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteDetails("Delete", "Experience", "experience_grid");}
        }]
    });

    var trainingRowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            text: 'Add',
            icon: './image/add.png',
            handler: function(){ AddEditDeleteDetails("Add", "Training", "training_grid");}
        }, {
            text: 'Edit',
            icon: './image/edit.png',
            handler: function(){ AddEditDeleteDetails("Edit", "Training", "training_grid");}
        }, {
            text: 'Delete',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteDetails("Delete", "Training", "training_grid");}
        }]
    });

    applicantForm = Ext.create('Ext.form.Panel', {
    	region  : 'north',
        height 	: 90,
        width   : '100%',
        bodyStyle : 'padding:10px;',
        autoScroll : true,
        border: false,
        fieldDefaults: {
            labelWidth: 100,
            anchor  : '100%',
            msgTarget: 'side',
            afterLabelTextTpl: required,
            allowBlank: false
        },
        items: 	[{
        	xtype: 'fieldcontainer',
        	fieldLabel: 'Complete Name',
        	layout: 'hbox',
        	defaultType: 'textfield',
        	items: [{
                id 			: 'fname',
        		name 		: 'fname',
        		emptyText 	: 'First Name',
        		flex 		: 1
        	},{
	            xtype 		: 'splitter'
	        },{
                id 			: 'mname',
	        	name 		: 'mname',
        		emptyText 	: 'Middle Name',
        		flex 		: 1,
                afterLabelTextTpl: null,
                allowBlank 	: true
        	},{
	            xtype 		: 'splitter'
	        },{
                id 			: 'lname',
	        	name 		: 'lname',
        		emptyText 	: 'Last Name',
        		flex 		: 1
        	},{
	            xtype 		: 'splitter'
	        },{
                id 			: 'suffix',
	        	name 		: 'suffix',
        		emptyText 	: 'Suffix',
        		flex 		: 0.3,
                afterLabelTextTpl: null,
                allowBlank 	: true
        	}]
        },{
        	xtype 		: 'container',
        	layout 		: 'hbox',
        	items: [{
	        	xtype 		: 'textfield',
                id          : 'phone_no',
	        	name        : 'phone_no',
	        	emptyText 	: '09xxxxxxxxx',
	            fieldLabel  : 'Phone No.',
	            flex 		: 1
	        },{
	        	xtype 		: 'textfield',
                id          : 'email_add',
	        	name        : 'email_add',
	        	emptyText 	: 'juandelacruz@gmail.com',
	            fieldLabel  : 'Email Add.',
	        	labelAlign 	: 'right',
	            flex 		: 1,
                afterLabelTextTpl: null,
                allowBlank: true
	        }]
        }]
    });

    var tabPanel = Ext.create('Ext.tab.Panel', {
    	plain 	 	: true,
    	activeTab  	: 0,
    	defaults 	: {
    		// bodyPadding: 10
    	},
    	items: [{
    		title: 'Educational Background',
            name: 'educ_background',
            items: [educationGrid]
    	},{
    		title: 'Civil Service Eligibility',
            name: 'eligibility',
            items: [eligibilityGrid]
    	},{
    		title: 'Work Experience',
            name: 'experience',
            items: [experienceGrid]
    	},{
            title: 'L&D Interventions/Trainings',
            name: 'training',
            items: [trainingGrid]
        }],
        listeners: {
            specialKey: function(field, e) {
                if(e.getKey() == e.TAB) {

                }
            },
            tabChange: function(tabPanel, newTab, oldTab, eOpts) {
                
            }
        }
    });

    applicantWindow = Ext.create('Ext.window.Window', {
    	title		: type + ' Applicant',
    	closable	: true,
    	modal		: true,
    	width		: 800,
    	// autoHeight	: true,
    	resizable	: false,
    	border 		: false,
    	bodyBorder 	: false,
    	buttonAlign	: 'center',
    	header: {titleAlign: 'center'},
    	items: [applicantForm, tabPanel],
    	buttons: [{
            text    : 'Save',
            icon    : './image/save.png',
            handler : function(){
                if(!applicantForm.form.isValid()){
                    errorFunction("Error!",'Please fill-in the required fields (Marked red).');
                    return;
                }
                Ext.Msg.show({
                    title   : 'Confirmation',
                    msg     : 'Are you sure you want to Save?',
                    width   : '100%',
                    icon    : Ext.Msg.QUESTION,
                    buttons : Ext.Msg.YESNO,
                    fn: function(btn){
                        if(btn == 'yes'){
                            applicantCRUD(type);
                        }
                        applicantWindow.close();
                    }
                });
            }
        },{
    	    text	: 'Close',
    	    icon	: './image/close.png',
    	    handler: function(){
    	    	applicantWindow.close();
    	    }
    	}]
	}).show();

    applicantForm.getForm().load({
        url: 'cat_applicants_masterlist/headerview',
        timeout: 30000,
        waitMsg:'Loading data...',
        params: {id: applicantID},
        success: function(form, action) {
            var data = action.result.data;
        },      
        failure: function(f,action) { errorFunction("Error!",'Please contact system administrator.'); }
    });
}