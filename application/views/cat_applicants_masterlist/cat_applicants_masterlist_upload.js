// var uploadWindow, uploadWindow;
// // var id, applicant_id, applic_type_desc;
// // var selected_lineup_applicant_id;

// function import_excel() {
// 	params = new Object();
// 	// params.id 							= id;
// 	// params.applicant_id  				= applicant_id;
// 	// params.date_application_received  	= Ext.getCmp("date_application_received").getValue();
// 	// params.position_applied  			= Ext.getCmp("position_applied").getValue();
// 	// params.notes  						= Ext.getCmp("notes").getValue();
// 	// params.applic_type_desc  			= applic_type_desc;

// 	addeditFunction('cat_applicants_masterlist/import_excel', params, 'list_grid', null, uploadWindow, uploadWindow);
// }

// function ImportExcel() {
// 	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';

// 	uploadWindow = Ext.create('Ext.form.Panel', {
// 		border 		: false,
// 		bodyStyle 	: 'padding:15px;',
// 		fieldDefaults: {
// 			labelAlign 	: 'right',
// 			labelWidth 	: 100,
// 			msgTarget 	: 'side',
// 			anchor	 	: '100%',
// 			afterLabelTextTpl: required,
// 		    allowBlank 	: false
//         },
//         items: [{
//         	xtype 		: 'filefield',
//         	name 		: 'applicants_import',
//         	fieldLabel 	: 'File',
//         	labelWidth  : 50,
//         	buttonText 	: 'Browse...'
//         }]
// 	});

// 	uploadForm = Ext.create('Ext.window.Window', {
// 		title		: 'Import Applicants from Excel',
// 		closable	: true,
// 		modal		: true,
// 		width		: 400,
// 		autoHeight	: true,
// 		resizable	: false,
// 		buttonAlign	: 'center',
// 		header: {titleAlign: 'center'},
// 		items: [uploadWindow],
// 		buttons: [{
// 		    text	: 'Save',
// 		    id 		: 'btn_save',
// 		    icon	: './image/save.png',
// 		    handler: function() {
// 				if(!uploadWindow.form.isValid()){
// 					errorFunction("Error!",'Please fill-in the required fields (Marked red).');
// 				    return;
// 		        }
// 				Ext.Msg.show({
// 					title	: 'Confirmation',
// 					msg		: 'Are you sure you want to upload?',
// 					width	: '100%',
// 					icon	: Ext.Msg.QUESTION,
// 					buttons	: Ext.Msg.YESNO,
// 					fn: function(btn){
// 						if(btn == 'yes') {
// 							import_excel();
// 						}
// 					}
// 				});
// 		    }
// 		}, {
// 		    text	: 'Close',
// 		    icon	: './image/close.png',
// 		    handler: function() {
// 		    	uploadWindow.close();
// 		    }
// 		}]
// 	}).show();
// }