function updateVacancyStatus(id, is_vacant) {
	params 				= new Object();
	params.id 			= id;
	params.is_vacant	= is_vacant;
	deleteFunction('cat_vacancies_masterlist/update_vacancy_status', params, 'list_grid', null);
}

function UpdateVacancyStatus() {
	var sm = Ext.getCmp("list_grid").getSelectionModel();
	var id = sm.selected.items[0].data.id;
	var is_vacant = sm.selected.items[0].data.is_vacant;
	var is_vacant_inv_str = is_vacant ? 'not vacant': 'vacant';

	Ext.Msg.show({
		title	: 'Confirmation',
		msg		: 'Are you sure you want to mark this item as <b>' + is_vacant_inv_str + '</b>?<br>This will not affect Plantilla record.',
		width	: '100%',
		icon	: Ext.Msg.QUESTION,
		buttons	: Ext.Msg.YESNO,
		fn: function(btn){
			if(btn == 'yes')
				updateVacancyStatus(id, is_vacant);
		}
	});
}