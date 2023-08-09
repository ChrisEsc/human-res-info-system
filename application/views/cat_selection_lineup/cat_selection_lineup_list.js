// setTimeout("UpdateSessionData();", 0);
var query = null;
var hr_test_row_index = null,
    interview_row_index = null;
var lineup_status = 2; //  0-pending, 1-completed, 2-all
const DATE_HR_TEST_COLUMN_INDEX = 6,
    REMARKS_HR_TEST_COLUMN_INDEX = 7
    DATE_INTERVIEW_COLUMN_INDEX = 9
    REMARKS_INTERVIEW_COLUMN_INDEX = 10;

function ExportDocs(type) {
    params = new Object();
    params.query    = query;
    params.type     = type;
    params.filetype     = 'grid'; 
    ExportDocument('cat_selection_lineup/exportdocument', params, type);
}

Ext.onReady(function(){
	var store = new Ext.data.JsonStore({
        pageSize: 10,
        storeId: 'myStore',
        proxy: {
            type: 'ajax',
            url: 'cat_selection_lineup/selection_lineup_list',
            timeout : 1800000,
            extraParams: {query:query, lineup_status: lineup_status},
            remoteSort: false,
            params: {start: 0, limit: 10},
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'lineup_applicant_id',
                totalProperty: 'totalCount'
            }
        },
        listeners: {
            load: function(store, records, successful, eOpts) {
                
            }
        },
        fields: [{name: 'lineup_header_id', type: 'int'}, {name: 'lineup_vacancy_id', type: 'int'}, {name: 'lineup_applicant_id', type: 'int'}, {name: 'applicant_id', type: 'int'}, 'item_details', 'applicant_name', 'phone_no', 'email_add', 'date_lineup_opened', 'plantilla_item_no', 'posgrade', 'depcode', 'latest_posting', 'date_lineup', 'status_hr_test', 'date_hr_test', 'remarks_hr_test', 'status_interview', 'date_interview', 'remarks_interview', 'is_done_bi', 'is_done_paf', 'is_done_nir', 'remarks', 'status_psb', 'date_psb','is_selected', 'is_locked', 'date_psb'],
        groupField: 'lineup_header_id'
    });
    
    var RefreshGridStore = function() {
        Ext.getCmp("list_grid").getStore().reload({params:{reset:1, start:0 }, timeout: 300000});
    };

    var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
    	pluginId:  'cell_editing',
        clicksToEdit: 1,
        listeners: {
            edit: function(editor, e, eOpts) {
                Ext.getCmp("save").setDisabled(false); 
            }
        }
    });

    var grid = Ext.create('Ext.grid.Panel', {
        id      : 'list_grid',
        region  : 'center',
        store   : store,
        cls     : 'gridCss',
        features: [{
            id 	: 'group',
            ftype: 'grouping',
            groupHeaderTpl: [
			    '{children:this.formatName}',
			    {
			        formatName: function(children) {
			        	var str = "";
                        var item_nos = [];
			        	str += children[0].data.item_details;
			        	str += " (Item No: ";
			        	for(var i = 0; i < children.length; i++) {
                            item_no = children[i].data.plantilla_item_no;
                            if(item_nos.indexOf(item_no) === -1) item_nos.push(item_no);
			        	}
                        str += item_nos.join(", ");
			        	str += ") SG: ";
			        	str += children[0].data.posgrade + " of ";
			        	str += children[0].data.depcode;
			        	return str;
			        }
			    }
            ]
        }],
        plugins : [cellEditing],
        columns : [
            // Ext.create('Ext.grid.RowNumberer', {width: 25}),
            {text: 'Item<br>No.', locked: true, dataIndex: 'plantilla_item_no', align: 'center', width: 55},
            {dataIndex: 'lineup_applicant_id', hidden: true},
            {text: 'Latest<br>Posting', locked: true, dataIndex: 'latest_posting', align: 'center', width: 115, renderer: dateRenderer},
            {text: 'Name', locked: true, dataIndex: 'applicant_name', align: 'left', width: 200},
            {text: 'Contact #', locked: true, dataIndex: 'phone_no', align: 'left', width: 100},
            {text: 'Email Address', locked: true, dataIndex: 'email_add', align: 'left', width: 160, renderer: addTooltip},
            {
                text: 'HR Test',
                lockable: false,
                columns: [{
                    id: 'status_hr_test',
                    text: 'Status',
                    editor: new Ext.form.field.ComboBox({
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'description', 'background_color'],
                            data: [
                                [1, 'For HR Test', '#34bb50'],
                                [2, 'Done', 'Transparent'],
                                [3, 'Hold', '#ef5777'],
                                [4, 'Did Not Respond', '#ffc048']
                            ]
                        }),
                        displayField: 'description',
                        valueField: 'description',
                        triggerAction: 'all',
                        forceSelection  : true,
                        editable        : false
                    }),
                    dataIndex: 'status_hr_test',
                    align: 'center',
                    width: 130,
                    listeners: {checkchange: checkchange},
                    renderer: testInterviewPsbRenderer
                },{
                    text: 'Date',
                    dataIndex: 'date_hr_test',
                    align: 'center',
                    width: 100,
                    // format: 'M d, Y',
                    renderer: Ext.util.Format.dateRenderer('M d, Y'),
                    editor: {
                        xtype: 'datefield',
                        format: 'm/d/Y',
                        // minValue: '01/01/21',
                        disabledDays: [0, 6],
                        disabledDaysText: 'Must be on weekdays.',
                        emptyText: 'mm/dd/yyyy',
                        allowBlank: true,
                        listeners: {
                            focus: function(date, the, eOpts) {
                                date.expand();
                            },
                            specialkey: function(field, e) {
                                if(e.getKey() == e.ENTER) {}
                            }
                        }
                    }
                },{
                    text: 'Details',
                    dataIndex: 'remarks_hr_test',
                    align: 'left',
                    width: 200,
                    renderer: addTooltip,
                    editor: {
                        xtype: 'textarea',
                        rows: 1,
                        allowBlank: true
                    }
                }]
            }, {
                text: 'Interview',
                columns: [{
                    id: 'status_interview',
                    text: 'Status',
                    editor: new Ext.form.field.ComboBox({
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'description', 'background_color'],
                            data: [
                                [1, 'For Interview', '#34bb50'],
                                [2, 'Done', 'Transparent'],
                                [3, 'Hold', '#ef5777'],
                                [4, 'Did Not Respond', '#ffc048']
                            ]
                        }),
                        displayField: 'description',
                        valueField: 'description',
                        triggerAction: 'all',
                        forceSelection: true,
                        editable: false
                    }),
                    dataIndex: 'status_interview',
                    align: 'center',
                    width: 130,
                    listeners: {checkchange: checkchange},
                    renderer: testInterviewPsbRenderer
                },{
                    text: 'Date',
                    dataIndex: 'date_interview',
                    align: 'center',
                    width: 100,
                    renderer: Ext.util.Format.dateRenderer('M d, Y'),
                    editor: {
                        xtype: 'datefield',
                        format: 'm/d/Y',
                        minValue: '01/01/21',
                        disabledDays: [0, 6],
                        disabledDaysText: 'Must be on weekdays.',
                        emptyText: 'mm/dd/yyyy',
                        allowBlank: true,
                        listeners: {
                            focus: function(date, the, eOpts) {
                                date.expand();
                            },
                            specialkey: function(field, e) {
                                if(e.getKey() == e.ENTER) {}
                            }
                        }
                    }
                },{
                    text: 'Details',
                    dataIndex: 'remarks_interview',
                    align: 'left',
                    width: 200,
                    renderer: addTooltip,
                    editor: {
                        xtype: 'textarea',
                        rows: 1,
                        allowBlank: true
                    }
                }]
            },
            {xtype: 'checkcolumn', text: 'BI', dataIndex: 'is_done_bi', align: 'center', width: '5%', listeners: {checkchange: checkchange}},
            {xtype: 'checkcolumn', text: 'PAF', dataIndex: 'is_done_paf', align: 'center', width: '5%', listeners: {checkchange: checkchange}},
            {xtype: 'checkcolumn', text: 'NIR', dataIndex: 'is_done_nir', align: 'center', width: '5%', listeners: {checkchange: checkchange}},
                        {
                text: 'PSB Deliberation',
                columns: [{
                    id: 'status_psb',
                    text: 'Status',
                    editor: new Ext.form.field.ComboBox({
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'description', 'background_color'],
                            data: [
                                [1, 'Ready For PSB', '#34bb50'],
                                [2, 'Done', 'Transparent'],
                                [3, 'Deferred', '#ef5777']
                            ]
                        }),
                        displayField: 'description',
                        valueField: 'description',
                        triggerAction: 'all',
                        forceSelection  : true,
                        editable        : false
                    }),
                    dataIndex: 'status_psb',
                    align: 'center',
                    width: 130,
                    listeners: {checkchange: checkchange},
                    renderer: testInterviewPsbRenderer
                },{
                    text: 'Date',
                    dataIndex: 'date_psb',
                    align: 'center',
                    width: 100,
                    // format: 'M d, Y',
                    renderer: Ext.util.Format.dateRenderer('M d, Y'),
                }]
            }, 
        ],
        width: '100%',
        height  : sheight,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                cellclick: function(cell, td, cellIndex, record, tr, rowIndex, e, eOpts) {

                },
                itemdblclick: function() {

                },
                itemcontextmenu: function(view, record, item, index, e){
                    e.stopEvent();
                    rowMenu.showAt(e.getXY());
                }
            }
        }
        // ,
        // bbar: Ext.create('Ext.PagingToolbar', {
        //     id: 'pageToolbar',
        //     store: store,
        //     pageSize: setLimit,
        //     displayInfo: true,
        //     displayMsg: 'Displaying {0} - {1} of {2}',
        //     emptyMsg: "No record/s to display"
        // })
    });
	RefreshGridStore(); 

	var rowMenu = Ext.create('Ext.menu.Menu', {
		items: [{
			id: 'addApplicantRow',
			text: 'Place Applicant',
			icon: './image/add.png',
			handler: function(){ AddEditDeleteApplicant('Add');}
		},{
            id: 'deleteApplicantRow',
            text: 'Remove Applicant',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteApplicant('Delete');}
        },{
            id: 'viewApplicantRow',
            text: 'View Applicant Details',
            icon: './image/view.png',
            handler: function(){ ViewApplicant();}
        }]
	});

    Ext.create('Ext.panel.Panel', {
        title: '<?php echo mysqli_real_escape_string($this->db->conn_id, $module_name);?>',
        //width: swidth,
        width   : '100%',
        height  : sheight,
        renderTo: "innerdiv",
        layout  : 'border',
        border  : false,
        items   : [grid],
        tbar: [{
            xtype   : 'textfield',
            id      : 'searchId',
            emptyText: 'Search here...',
            width   : '25%',
            listeners: {
                specialKey : function(field, e) {
                    if(e.getKey() == e.ENTER) {
                        Ext.getCmp("list_grid").getStore().proxy.extraParams["query"] = Ext.getCmp("searchId").getValue();
                        query = Ext.getCmp("searchId").getValue();
                        RefreshGridStore();
                    }
                }
            }
        }, {
            xtype       : 'radio',
            boxLabel    : 'All',
            name        : 'lineup_status',
            checked     : true,
            listeners   : {
                focus: function() {
                    lineup_status = 2;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["lineup_status"] = 2;
                    RefreshGridStore();
                }
            }
        }, {
            xtype       : 'radio',
            boxLabel    : 'Pending',
            name        : 'lineup_status',
            listeners   : {
                focus: function() {
                    lineup_status = 0;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["lineup_status"] = 0;
                    RefreshGridStore();
                }
            }
        }, {
            xtype       : 'radio',
            boxLabel    : 'Completed',
            name        : 'lineup_status',
            listeners   : {
                focus: function() {
                    lineup_status = 1;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["lineup_status"] = 1;
                    RefreshGridStore();
                    var plugin = Ext.getCmp("list_grid").getPlugin("cellEditing");
                }
            }
        },
        { xtype: 'tbfill'},
        { xtype: 'button', id: 'save', disabled: true, text: 'SAVE', icon: './image/save.png', tooltip: 'Save', handler: function(){ SaveSelectionLineup();}},
        { xtype: 'button', id: 'addVacancy', text: 'ADD VACANCY', icon: './image/add.png', tooltip: 'Add Vacancy', handler: function(){ AddEditDeleteSelectionLineup('Add');}},
        { xtype: 'button', id: 'deleteVacancy', text: 'DELETE VACANCY', icon: './image/delete.png', tooltip: 'Delete Vacancy', handler: function(){ AddEditDeleteSelectionLineup('Delete');}}
        ]
    });
});

var checkchange = function(check, rowIndex, checked, eOpts) {
    var save_button = Ext.getCmp("save");
    if(save_button.isDisabled()) {
        Ext.getCmp("save").setDisabled(false);   
    }
};