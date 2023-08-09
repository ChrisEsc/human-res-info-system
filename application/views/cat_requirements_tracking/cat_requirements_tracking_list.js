// setTimeout("UpdateSessionData();", 0);
var query = null;

function ExportDocs(type) {
    params = new Object();
    params.query    = query;
    params.type     = type;
    params.filetype     = 'grid'; 
    ExportDocument('cat_requirements_tracking/exportdocument', params, type);
}

Ext.onReady(function(){
	var store = new Ext.data.JsonStore({
        pageSize: 10,
        storeId: 'myStore',
        proxy: {
            type: 'ajax',
            url: 'cat_requirements_tracking/list',
            timeout : 1800000,
            extraParams: {query:query},
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
        fields: [{name: 'id', type: 'int'}, 'record_type', 'control_number', 'date_communication', 'date_logged', 'subject', 'from_name', 'to_name', 'priority', 'status', 'status_style', 'division_description', 'division_code', 'side_notes', 'action_taken', 'date_action_taken', 'duration_action_taken', 'attachment_full_names', 'attachment_links', 'date_uploaded', 'attachment_descriptions']
    });
    
    var RefreshGridStore = function() {
        Ext.getCmp("list_grid").getStore().reload({params:{reset:1, start:0 }, timeout: 300000});
    };

    var grid = Ext.create('Ext.grid.Panel', {
        id      : 'list_grid',
        region  : 'center',
        store   : store,
        cls     : 'gridCss',
        syncRowHeight: false,
        columns: [
            Ext.create('Ext.grid.RowNumberer', {width: 25}),
            {dataIndex: 'id', hidden: true},
            {dataIndex: 'status', hidden: true},
            {text: 'Ctrl<br>#', locked: true, dataIndex: 'control_number', align: 'center', width: 65, renderer:columnWrap},
            {text: 'Com.<br>Date', locked: true, dataIndex: 'date_communication', align: 'center', width: 70, renderer:columnWrap},
            {text: 'Details', locked: true, dataIndex: 'subject', align: 'left', width: 400, renderer:columnWrap},
            {text: 'From', lockable: false, dataIndex: 'from_name', align: 'center', width: '14%', renderer:columnWrap},
            {text: 'For (To)', dataIndex: 'to_name', align: 'center', width: '14%', renderer:columnWrap},
            {text: 'Date Logged', dataIndex: 'date_logged', align: 'center', width: '12%', renderer:columnWrap, hidden: true},
            {text: 'Priority', dataIndex: 'priority', align: 'center', width: '7%', renderer:priorityRenderer},
            {text: 'Status', dataIndex: 'status_style', align: 'left', width: '12%', renderer:columnWrap},
            {text: 'Communication Softcopy', dataIndex: 'attachment_links', align: 'left', width: '20%', renderer:columnWrap},
            {text: 'Assigned<br>Division', dataIndex: 'division_code', align: 'center', width: '9%', renderer:addTooltip},
            {text: 'Side Notes', dataIndex: 'side_notes', align: 'left', width: '35%', renderer:columnWrap},
            {text: 'Action Taken by Division', dataIndex: 'action_taken', align: 'left', width: '35%', renderer:columnWrap},
            {text: 'Action Taken<br>Date', dataIndex: 'date_action_taken', align: 'center', width: '12%', renderer:columnWrap},
            {text: 'Action Taken<br>Duration', dataIndex: 'duration_action_taken', align: 'center', width: '12%', renderer:addTooltip},
            {text: 'Softcopy<br>Date Filed', dataIndex: 'date_uploaded', align: 'center', width: '12%', renderer:columnWrap},
            {text: 'Softcopy<br>Description', dataIndex: 'attachment_descriptions', align: 'left', width: '15%', renderer:columnWrap}
        ],
        width: '100%',
        height  : sheight,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {

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
            handler: function(){ AddEditDeleteApplicant('Add');}
        }, {
            id: 'editRecordRow',
            text: 'Edit',
            icon: './image/edit.png',
            handler: function(){ AddEditDeleteApplicant('Edit');}
        }, {
            id: 'deleteRecordRow',
            text: 'Delete',
            icon: './image/delete.png',
            handler: function(){ AddEditDeleteApplicant('Delete');}
        }, {
            id: 'uploadDocumentRow',
            text: 'Upload Scanned Document',
            icon: './image/upload.png',
            handler: function(){ UploadDocument('Upload');}
        },{
            text: 'View Record',
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
        { xtype: 'button', id: 'addRecord', text: 'ADD', icon: './image/add.png', tooltip: 'Add Applicant', handler: function(){ AddEditDeleteApplicant('Add');}},
        { xtype: 'button', id: 'editRecord', text: 'EDIT', icon: './image/edit.png', tooltip: 'Edit Applicant', handler: function(){ AddEditDeleteApplicant('Edit');}},
        { xtype: 'button', id: 'deleteRecord', text: 'DELETE', icon: './image/delete.png', tooltip: 'Delete Applicant', handler: function(){ AddEditDeleteApplicant('Delete');}},
        // { xtype: 'button', id: 'uploadDocument', text: 'UPLOAD Scanned Document', icon: './image/upload.png', tooltip: 'Upload Scanned Document', handler: function(){ UploadDocument('Upload');}},
        { xtype: 'button', id: 'viewRecord', text: 'VIEW RECORD', icon: './image/view.png', tooltip: 'View Record', handler: function(){ ViewRecord();}}
        ]
    });
});