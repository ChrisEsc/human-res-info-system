// setTimeout("UpdateSessionData();", 0);
var query = null;
var psb_status = 2; // 0-pending, 1-completed, 2-all

function ExportDocs(type) {
    params = new Object();
    params.query    = query;
    params.status   = psb_status;
    params.filetype = type;
    ExportDocument('cat_psb/exportdocument', params, type);
}

Ext.onReady(function(){
    var store = new Ext.data.JsonStore({
        pageSize: 10,
        storeId: 'myStore',
        proxy: {
            type: 'ajax',
            url: 'cat_psb/psb_list',
            timeout : 1800000,
            extraParams: {query:query, psb_status: psb_status},
            remoteSort: false,
            params: {start: 0, limit: 10},
            reader: {
                type: 'json',
                root: 'data',
                idProperty: 'lineup_vacancy_id',
                totalProperty: 'totalCount'
            }
        },
        listeners: {
            load: function(store, records, successful, eOpts) {
                
            }
        },
        fields: [{name: 'lineup_vacancy_id', type: 'int'}, {name: 'lineup_header_id', type: 'int'}, {name: 'selected_lineup_applicant_id', type: 'int'}, 'item_details', 'plantilla_item_no', 'posgrade', 'depcode', 'latest_posting', 'selected_applicant_name', 'date_psb', 'remarks', 'is_locked'],
        groupField: 'lineup_header_id'
    });
    
    var RefreshGridStore = function() {
        Ext.getCmp("list_grid").getStore().reload({params:{reset:1, start:0 }, timeout: 300000});
    };

    var grid = Ext.create('Ext.grid.Panel', {
        id      : 'list_grid',
        region  : 'center',
        store   : store,
        cls     : 'gridCss',
        features: [{
            id  : 'group',
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
        columns : [
            {dataIndex: 'lineup_vacancy_id', hidden: true},
            {text: 'Item No.', dataIndex: 'plantilla_item_no', align:   'center', width: '10%'},
            {text: 'Latest Posting', dataIndex: 'latest_posting', align: 'center', width: '15%', renderer: dateRenderer},
            {text: 'Selected Applicant', dataIndex: 'selected_applicant_name', width: '29%'},
            {text: 'PSB Date', dataIndex: 'date_psb', align: 'center', width: '25%', renderer: Ext.util.Format.dateRenderer('M d, Y')},
            {text: 'Remarks', dataIndex: 'remarks', width: '20%'}
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
        }
    });
    RefreshGridStore(); 

    var rowMenu = Ext.create('Ext.menu.Menu', {
        items: [{
            id: 'updateRow',
            text: 'Update PSB',
            icon: './image/details.png',
            handler: function(){ UpdatePSB();}
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
            xtype 		: 'radio',
            boxLabel 	: 'All',
            name 		: 'psb_status',
            checked 	: true,
            listeners 	: {
            	focus: function() {
            		psb_status = 2;
            		Ext.getCmp("list_grid").getStore().proxy.extraParams["psb_status"] = 2;
            		RefreshGridStore();
            	}
            }
        }, {
            xtype 		: 'radio',
            boxLabel 	: 'Pending',
            name 		: 'psb_status',
            listeners 	: {
            	focus: function() {
            		psb_status = 0;
            		Ext.getCmp("list_grid").getStore().proxy.extraParams["psb_status"] = 0;
            		RefreshGridStore();
            	}
            }
        }, {
            xtype 		: 'radio',
            boxLabel 	: 'Completed',
            name 		: 'psb_status',
            listeners 	: {
            	focus: function() {
            		psb_status = 1;
            		Ext.getCmp("list_grid").getStore().proxy.extraParams["psb_status"] = 1;
            		RefreshGridStore();
            	}
            }
        },
        { xtype: 'tbfill'},
        { xtype: 'button', id: 'update', text: 'UPDATE PSB', icon: './image/details.png', tooltip: 'Update PSB', handler: function(){ UpdatePSB();}},
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