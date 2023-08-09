// setTimeout("UpdateSessionData();", 0);
var query = null;

function ExportDocs(type) {
    params = new Object();
    params.query    = query;
    params.type     = type;
    params.filetype     = 'grid'; 
    ExportDocument('cat_applicants_masterlist/exportdocument', params, type);
}

Ext.onReady(function(){
	var store = new Ext.data.JsonStore({
        pageSize: 15,
        storeId: 'myStore',
        proxy: {
            type: 'ajax',
            url: 'cat_applicants_masterlist/applicants_list',
            timeout : 1800000,
            extraParams: {query:query},
            remoteSort: false,
            params: {start: 0, limit: 15},
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
        fields: [{name: 'id', type: 'int'}, 'applicant_name', 'phone_no', 'email_add', 'position_applied', 'notes', 'educ_highest', 'eligibility', 'experience', 'date_application_received', 'applic_type']
    });
    
    var RefreshGridStore = function() {
        Ext.getCmp("list_grid").getStore().reload({params:{reset:1, start:0 }, timeout: 300000});
    };

    var grid = Ext.create('Ext.grid.Panel', {
        id      : 'list_grid',
        region  : 'center',
        store   : store,
        cls     : 'gridCss',
        columns: [
            Ext.create('Ext.grid.RowNumberer', {width: 25}),
            {dataIndex: 'id', hidden: true},
            {text: 'Name', locked: true, dataIndex: 'applicant_name', align: 'left', width: 225, renderer:columnWrap},
            {text: 'Phone No.', locked: true, dataIndex: 'phone_no', align: 'left', width: 100, renderer:columnWrap},
            {text: 'Email Add.', locked: true, dataIndex: 'email_add', align: 'left', width: 180, renderer:columnWrap},
            {text: 'Position/s Applied', lockable: false, dataIndex: 'position_applied', align: 'left', width: '15%', renderer:columnWrap},
            {text: 'Application Notes', dataIndex: 'notes', align: 'left', width: '20%', renderer:columnWrap},
            {text: 'Course', dataIndex: 'educ_highest', align: 'left', width: '20%', renderer:columnWrap},
            {text: 'Eligibility', dataIndex: 'eligibility', align: 'left', width: '20%', renderer:columnWrap},
            {text: 'Experience', dataIndex: 'experience', align: 'left', width: '20%', renderer:columnWrap},
            {text: 'Date Received', dataIndex: 'date_application_received', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Type', dataIndex: 'applic_type', align: 'left', width: '7%', renderer:columnWrap}
        ],
        width: '100%',
        height  : sheight,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {
                    ViewRecord();
                },
                itemcontextmenu: function(view, record, item, index, e){
                    e.stopEvent();
                    rowMenu.showAt(e.getXY());
                }
            }
        },
        bbar: Ext.create('Ext.PagingToolbar', {
            id: 'pageToolbar',
            store: store,
            pageSize: setLimit,
            displayInfo: true,
            displayMsg: 'Displaying {0} - {1} of {2}',
            emptyMsg: "No record/s to display"
        })
    });
	RefreshGridStore();

	var rowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            id: 'addRecordRow',
            text: 'Add',
            icon: './image/add.png',
            handler: function(){ AddEditDeleteApplicant("Add");}
        }, {
            id: 'editRecordRow',
            text: 'Edit',
            icon: './image/edit.png',
            handler: function(){ AddEditDeleteApplicant("Edit");}
        }, {
            id: 'deleteRecordRow',
            text: 'Delete',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteApplicant("Delete");}
        }, {
            text: 'Update Application',
            icon: './image/details.png',
            handler: function(){ UpdateApplication();}
        }, {
            text: 'Applicant Details',
            icon: './image/view.png',
            handler: function(){ ViewRecord();}
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
        },
        { xtype: 'tbfill'},
        { xtype: 'button', id: 'addRecord', text: 'ADD', icon: './image/add.png', tooltip: 'Add Applicant', handler: function(){ AddEditDeleteApplicant("Add");}},
        { xtype: 'button', id: 'editRecord', text: 'EDIT', icon: './image/edit.png', tooltip: 'Edit Applicant', handler: function(){ AddEditDeleteApplicant("Edit");}},
        { xtype: 'button', id: 'deleteRecord', text: 'DELETE', icon: './image/delete.png', tooltip: 'Delete Applicant', handler: function(){ AddEditDeleteApplicant("Delete");}},
        { xtype: 'button', id: 'updateApplication', text: 'UPDATE APPLICATION', icon: './image/details.png', tooltip: 'Update Application', handler: function(){ UpdateApplication();}},
        { xtype: 'button', id: 'viewRecord', text: 'APPLICANT DETAILS', icon: './image/view.png', tooltip: 'APPLICANT DETAILS', handler: function(){ ViewRecord();}}
        ]
    });
});