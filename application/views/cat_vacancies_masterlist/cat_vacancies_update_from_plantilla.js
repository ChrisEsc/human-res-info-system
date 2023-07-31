function UpdateFromPlantilla(type) {
	Ext.MessageBox.wait('Loading...');
	Ext.Ajax.request({
		url     : "cat_vacancies_masterlist/update_from_plantilla",
		timeout : 1800000, // 30 minutes
        method  : 'POST',
        params  : {type: type},
        success: function(f,a) {
        	Ext.MessageBox.hide();
        	Ext.getCmp("list_grid").getStore().reload({params:{reset:1, start:0 }, timeout: 1000});
        }
	});
}