// setTimeout("UpdateSessionData();", 0);
var query = null;
var publication_status = 5, // publications => 1-published, 2-unpublished, 3-expiring, 4-expired, 5-all
    show_all_items = 0; //

function ExportDocs(type) {
    params = new Object();
    params.query    = query;
   // params.appointment_status   = appointment_status;
    params.publication_status   = publication_status;
    params.show_all_items       = show_all_items;
    params.filetype = type; 
    ExportDocument('cat_generate_publication/exportdocument', params, type);
}

Ext.onReady(function(){
	var store = new Ext.data.JsonStore({
        pageSize: 50,
        storeId: 'myStore',
        proxy: {
            type: 'ajax',
            url: 'cat_generate_publication/publication_list',
            timeout : 1800000,
            extraParams: {query:query, publication_status: publication_status, show_all_items: show_all_items},
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
        fields: [{name: 'id', type: 'int'}, 'item_desc', 'item_desc_detail', {name:'plantilla_item_no', type: 'int'}, {name:'posgrade', type:'int'}, 'step_1', 'occupant_desc', 'education', 'experience', 'training', 'eligibility', 'competency', 'description','latest_posting','public_status', 'public_remarks', 'public_remarks_style', 'is_vacant']
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
            {text: 'Item Name', dataIndex: 'item_desc', align: 'left', width: '15%', render:columnWrap},
            {text: 'Parenthetical Pos.', dataIndex: 'item_desc_detail', align: 'left', width: '12%', renderer:columnWrap},
            {text: 'Item No.', dataIndex: 'plantilla_item_no', align: 'center', width: '6%', renderer:columnWrap},
            {text: 'SG', dataIndex: 'posgrade', align: 'center', width: '3%', renderer:columnWrap},
            {text: 'Monthly Rate', dataIndex: 'step_1', align: 'center', width: '10%', renderer:columnWrap},
            {text: 'Incumbent', dataIndex: 'occupant_desc', align: 'left', width: '15%', renderer:columnWrap, hidden: true},
            {text: 'Education', dataIndex: 'education', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Experience', dataIndex: 'experience', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Training', dataIndex: 'training', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Eligibility', dataIndex: 'eligibility', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Competency', dataIndex: 'competency', align: 'center', width: '5%', renderer:columnWrap},
            {text: 'Office', dataIndex: 'description', align: 'left', width: '10%', renderer:columnWrap},
            {text: 'Latest Posting', dataIndex: 'latest_posting', align: 'left', width: '8%', renderer:columnWrap},
            {text: 'Public. Status', dataIndex: 'public_status', align: 'left', width: '8%', renderer:columnWrap, hidden: true},
            {text: 'Public. Remarks', dataIndex: 'public_remarks_style', align: 'left', width: '8%', renderer:columnWrap},
            {xtype: 'checkcolumn', text: 'Vacant', dataIndex: 'is_vacant', align: 'center', width: 80, listeners:{beforecheckchange: function() {return false}}}
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
            id          : 'publication_status',
            valueField  : 'id',
            displayField: 'description',
            emptyText   : 'Publication Status',
            triggerAction: 'all',
            enableKeyEvents : true,
            editable    : false,
            mode        : 'local',
            store       : new Ext.data.ArrayStore({
                fields: ['id', 'description'],
                data: [[1, 'Published'], [2, 'Unpublished'], [3, 'Expiring'], [4, 'Expired'], [5, 'All']]
            }),
            listeners: {
                select: function(combo, record, index) {
                    publication_status = record[0].data.id;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["publication_status"] = publication_status;
                    RefreshGridStore();
                }
                
            }
        }, {
            xtype       : 'checkbox',
            boxLabel    : 'Show Non-Vacant Items',
            id          : 'show_all_items_checkbox',
            listeners   : {
                change: function(checkbox, newValue, oldValue, eOpts) {
                    if(newValue) show_all_items = 1;
                    else show_all_items = 0; 
                    // show_all_items = newValue;
                    Ext.getCmp("list_grid").getStore().proxy.extraParams["show_all_items"] = show_all_items;
                    RefreshGridStore();
                }
            }
        }, 
        { xtype: 'tbfill'},
        {
            text: 'DOWNLOAD',
            tooltip: 'Download Data to Excel File Format',
            icon: './image/download.png',
            handler: function() {
                ExportDocs('Excel');
            }
        }]
    });
});