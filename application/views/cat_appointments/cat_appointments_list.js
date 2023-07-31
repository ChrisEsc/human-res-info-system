// setTimeout("UpdateSessionData();", 0);
var query = null;
var appointment_type = 6,  // type => 1-original, 2-promotion, 3-reappointment, 4-reemploy, 5-transfer, 6-all
    appointment_status = 5; // appointments => 1-approved, 2-pending, 3-cancelled, 4-disapproved, 5-all

function ExportDocs(type) {
    params = new Object();
    params.query                = query;
    params.appointment_type     = appointment_type;
    params.appointment_status   = appointment_status;
    params.filetype             = type; 
    ExportDocument('cat_appointments/exportdocument', params, type);
}

Ext.onReady(function(){
	var store = new Ext.data.JsonStore({
        pageSize: 50,
        storeId: 'myStore',
        proxy: {
            type: 'ajax',
            url: 'cat_appointments/appointments_list',
            timeout : 1800000,
            extraParams: {query:query, appointment_type: appointment_type, appointment_status: appointment_status},
            remoteSort: false,
            params: {start: 0, limit: 50},
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
        fields: [{name: 'id', type: 'int'}, 'appointee', {name: 'plantilla_item_no', type: 'int'}, 'item_code', 'item_desc', 'item_desc_detail', 'item_desc_full', 'depcode', 'appointment_type', 'appointment_status', 'effectivity', 'vice_name', 'csc_action_date', 'vacated_item_no', 'vacated_item_code', 'vacated_item_desc_full', 'vacated_depcode']
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
            Ext.create('Ext.grid.RowNumberer', {width: 40}),
            {dataIndex: 'id', hidden: true},
            {
                text: 'Vacated Item',
                columns: [{
                    text: 'Item No.', 
                    dataIndex: 'vacated_item_no', 
                    align: 'center', 
                    width: 75, 
                    renderer:columnWrap
                }, {
                    text: 'Item Desc.', 
                    dataIndex: 'vacated_item_desc_full', 
                    align: 'left', 
                    width: 250, 
                    renderer:columnWrap
                }, {
                    text: 'Code', 
                    dataIndex: 'vacated_item_code', 
                    align: 'left', 
                    width: 80, 
                    renderer:columnWrap
                }, {
                    text: 'Dept.', 
                    dataIndex: 'vacated_depcode', 
                    align: 'left', 
                    width: 70, 
                    renderer:columnWrap
                }]
            },
            {text: 'Appointee', dataIndex: 'appointee', align: 'left', width: '15%', renderer:columnWrap},
            {
                text: 'New Item',
                columns: [{
                    text: 'Item No.', 
                    dataIndex: 'plantilla_item_no', 
                    align: 'center', 
                    width: 75, 
                    renderer:columnWrap
                }, {
                    text: 'Item Desc.', 
                    dataIndex: 'item_desc_full', 
                    align: 'left', 
                    width: 250, 
                    renderer:columnWrap
                }, {
                    text: 'Code', 
                    dataIndex: 'item_code', 
                    align: 'left', 
                    width: 80, 
                    renderer:columnWrap
                }, {
                    text: 'Dept.', 
                    dataIndex: 'depcode', 
                    align: 'left', 
                    width: 70, 
                    renderer:columnWrap
                }]
            },
            {text: 'Type', dataIndex: 'appointment_type', align: 'center', width: '5%', renderer:columnWrap},
            {text: 'Status', dataIndex: 'appointment_status', align: 'center', width: '5%', renderer:columnWrap},
            {text: 'Effectivity', dataIndex: 'effectivity', align: 'center', width: '7%', renderer:columnWrap},
            {text: 'Vice Name', dataIndex: 'vice_name', align: 'left', width: '15%', renderer:columnWrap},
            {text: 'CSC Action Date', dataIndex: 'csc_action_date', align: 'left', width: '8%', renderer:columnWrap}
        ],
        width: '100%',
        height  : sheight,
        margin: '0 0 10 0',
        viewConfig: {
            listeners: {
                itemdblclick: function() {
                    // ViewRecord();
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
            pageSize: 50,
            displayInfo: true,
            displayMsg: 'Displaying {0} - {1} of {2}',
            // displayMsg: 'Displaying {2} records',
            emptyMsg: "No record/s to display"
        })
    });
	RefreshGridStore(); 

	var rowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            text: 'Mark Vacant/Not Vacant',
            icon: './image/edit.png',
            handler: function(){ UpdateVacancyStatus();}
        }
        // ,{
        //     text: 'View Record',
        //     icon: './image/view.png',
        //     handler: function(){ ViewRecord();}
        // }
        ]
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
        }, '-', {
            xtype       : 'combo',
            width       : 150,
            id          : 'appointment_type',
            valueField  : 'id',
            displayField: 'description',
            emptyText   : 'Appointment Type',
            triggerAction: 'all',
            enableKeyEvents : true,
            editable    : false,
            mode        : 'local',
            store       : new Ext.data.ArrayStore({
                fields: ['id', 'description'],
                data: [[1, 'Original'], [2, 'Promotion'], [3, 'Reappointment'], [4, 'Reemploy'], [5, 'Transfer'], [6, 'All']]
            }),
            listeners: {
                select: function(combo, record, index) {
                    appointment_type = record[0].data.description;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["appointment_type"] = appointment_type;
                    RefreshGridStore();
                }
            }
        }, {
            xtype       : 'combo',
            width       : 150,
            id          : 'appointment_status',
            valueField  : 'id',
            displayField: 'description',
            emptyText   : 'Appointment Status',
            triggerAction: 'all',
            enableKeyEvents : true,
            editable    : false,
            mode        : 'local',
            store       : new Ext.data.ArrayStore({
                fields: ['id', 'description'],
                data: [[1, 'Approved'], [2, 'Pending'], [3, 'Cancelled'], [4, 'Disapproved'], [5, 'All']]
            }),
            listeners: {
                select: function(combo, record, index) {
                    appointment_status = record[0].data.id;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["appointment_status"] = appointment_status;
                    RefreshGridStore();
                }
                
            }
        }, { 
            xtype: 'button', 
            id: 'clear', 
            text: 'CLEAR', 
            icon: './image/reload.png', 
            tooltip: 'Clear all filters', 
            handler: function (){
                Ext.getCmp("appointment_type").clearValue();
                Ext.getCmp("appointment_status").clearValue();
                Ext.getCmp("searchId").setValue("");

                query = null;
                appointment_type = 6;
                appointment_status = 5;

                Ext.getCmp("list_grid").getStore().proxy.extraParams["query"] = query;
                Ext.getCmp("list_grid").getStore().proxy.extraParams["appointment_type"] = appointment_type;
                Ext.getCmp("list_grid").getStore().proxy.extraParams["appointment_status"] = appointment_status;
                Ext.getCmp("pageToolbar").moveFirst();
            }
        },
        { xtype: 'tbfill'},
        // { xtype: 'button', id: 'viewRecord', text: 'VIEW RECORD', icon: './image/view.png', tooltip: 'View Record', handler: function(){ ViewRecord();}},
        {
            text: 'DOWNLOAD',
            tooltip: 'Download Data to Excel File Format',
            icon: './image/download.png',
            handler: function() {
                // ExportDocs('Excel');
            }
        }]
    });
});