var id;

function ViewRecord() {   
    var sm = Ext.getCmp("list_grid").getSelectionModel();
    if(!sm.hasSelection()) {
        warningFunction("Warning","Please select a record!");
        return;
    }
    id = sm.selected.items[0].data.id;

    Ext.MessageBox.wait('Loading...');
    Ext.Ajax.request({
        url     : "cat_applicants_masterlist/view",
        method  : 'POST',
        params  : {id: id},
        success: function(f,a) {
            var response = Ext.decode(f.responseText);     
            var htmlData =
                '<table width="100%" style="background: #ff6666;border: solid 1px white;">' +
                    '<tr style="background: #ff6666;">';
            htmlData +=                    
                '</tr><tr >' +
                '<td valign="top" align="right" style="background: #ffbec2; padding: 2px;" width="20%"><font color=black size=2>Complete Name</td>' +
                '<td valign="top" align="left" style="background: #ffd9d9; padding: 2px;" width="80%"><font color=black size=2>'+response.header_details[0].applicant_name+'</td>' +
                '</tr>' +
                '<td valign="top" align="right" style="background: #ffbec2; padding: 2px;"><font color=black size=2>Phone No.</td>' +
                '<td valign="top" align="left" style="background: #ffd9d9; padding: 2px;"><font color=black size=2>'+response.header_details[0].phone_no+'</td>' +
                '</tr>' +
                '<tr >' +
                '<td valign="top" align="right" style="background: #ffbec2; padding: 2px;"><font color=black size=2>Email Add.</td>' +
                '<td valign="top" align="left" style="background: #ffd9d9; padding: 2px;"><font color=black size=2>'+response.header_details[0].email_add+'</td>' +
                '</tr>';
            htmlData += '</table>';
            htmlData +=
                '<table width="100%" style="background: #5aa865;border: solid 1px white;">';
            htmlData += '<tr style="background: #5aa865;"></tr><td colspan="8" align="center" style="padding: 2px;" width="100%"><font color=white size=2><b>Educational Background</b></font></td></tr>';
            htmlData += '<tr >' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="7%"><font color=black size=2><b>Level</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>School</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>Degree/Course</b></td>' +
                        '<td colspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>Period</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>Highest Level/ Units Earned</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>Year Graduated</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="13%"><font color=black size=2><b>Scholarship/ Academic Honors</b></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>From</b></td>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>To</b></td>';

            for(var i = 0; i < response.education_count; i++) { 
                 htmlData += '</tr><tr >' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].educ_level_desc+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].school+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].course+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].from_year+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].to_year+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].units_earned+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].year_grad+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.education_details[i].acad_honor+'</td>' +
                        '</tr>';
            };

            htmlData += '</table>';
            htmlData +=
                '<table width="100%" style="background: #5aa865;border: solid 1px white;">';
            htmlData += '<tr style="background: #5aa865;"></tr><td colspan="6" align="center" style="padding: 2px;" width="100%"><font color=white size=2><b>Civil Service Eligibility</b></font></td></tr>';
            htmlData += '<tr >' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="30%"><font color=black size=2><b>Eligibility</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>Rating</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="15%"><font color=black size=2><b>Date of Exam/ Conferement</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="25%"><font color=black size=2><b>Place</b></td>' +
                        '<td colspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>License (if applicable)</b></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>Number</b></td>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>Validity</b></td>';

            for(var i = 0; i < response.eligibility_count; i++) { 
                 htmlData += '</tr><tr >' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.eligibility_details[i].title+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.eligibility_details[i].rating+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.eligibility_details[i].exam_date+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.eligibility_details[i].exam_place+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.eligibility_details[i].license_no+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.eligibility_details[i].date_validity+'</td>' +
                        '</tr>';
            };

            htmlData += '</table>';
            htmlData +=
                '<table width="100%" style="background: #5aa865;border: solid 1px white;">';
            htmlData += '<tr style="background: #5aa865;"></tr><td colspan="8" align="center" style="padding: 2px;" width="100%"><font color=white size=2><b>Work Experience</b></font></td></tr>';
            htmlData += '<tr >' +
                        '<td colspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>Inclusive Dates</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="25%"><font color=black size=2><b>Position</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="15%"><font color=black size=2><b>Dept/Agency/ Office/Company</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="15%"><font color=black size=2><b>Monthly Salary</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>SG</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>Appt. Status</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="5%"><font color=black size=2><b>Gov\'t Service</b></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>From</b></td>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>To</b></td>';

            for(var i = 0; i < response.experience_count; i++) { 
                 htmlData += '</tr><tr >' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].from_date+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].to_date+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].position+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].agency_company+'</td>' +
                        '<td valign="top" align="right" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].monthly_salary+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].salary_grade+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].employment_status_desc+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.experience_details[i].government_service+'</td>' +
                        '</tr>';
            };

            htmlData += '</table>';
            htmlData +=
                '<table width="100%" style="background: #5aa865;border: solid 1px white;">';
            htmlData += '<tr style="background: #5aa865;"></tr><td colspan="8" align="center" style="padding: 2px;" width="100%"><font color=white size=2><b>Learning and Development Interventions/Trainings</b></font></td></tr>';
            htmlData += '<tr >' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="30%"><font color=black size=2><b>Title</b></td>' +
                        '<td colspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>Inclusive Dates</b></td>' +
                        '<td rowspan="2" valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="15%"><font color=black size=2><b>No. of Hrs.</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="15%"><font color=black size=2><b>Type</b></td>' +
                        '<td rowspan="2" valign="center" align="left" style="background: #c1e1c6; padding: 2px;" width="20%"><font color=black size=2><b>Conducted/Sponsored by</b></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>From</b></td>' +
                        '<td valign="center" align="center" style="background: #c1e1c6; padding: 2px;" width="10%"><font color=black size=2><b>To</b></td>';

            for(var i = 0; i < response.training_count; i++) { 
                 htmlData += '</tr><tr >' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.training_details[i].title+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.training_details[i].from_date+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.training_details[i].to_date+'</td>' +
                        '<td valign="top" align="center" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.training_details[i].duration+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.training_details[i].training_type_desc+'</td>' +
                        '<td valign="top" align="left" style="background: #d8f1dc; padding: 2px;" ><font color=black size=2>'+response.training_details[i].conducted_by+'</td>' +
                        '</tr>';
            };

            htmlData += '</table>';
            var htmlLoad = new Ext.XTemplate(htmlData);
            var applicationsStore = new Ext.data.JsonStore({
                storeId: 'applicationsStore',
                proxy: {
                    pageSize    : 10,
                    type        : 'ajax',
                    url         : 'cat_applicants_masterlist/applicationsview',
                    timeout     : 1800000,
                    extraParams : {id: id},
                    remoteSort  : false,
                    params: {start: 0, limit: 10},
                    reader: {
                        type: 'json',
                        root: 'data',
                        idProperty: 'id',
                        totalProperty: 'totalCount'
                    }
                },
                fields:[{name: 'lineup_applicant_id', type: 'int'}, 'item_code', 'item_desc', 'item_desc_detail', 'posgrade', 'depcode', 'psb_status', 'date_lineup', 'date_hr_test', 'remarks_hr_test', 'date_interview', 'remarks_interview', 'is_done_bi', 'is_done_paf', 'is_done_nir', 'date_psb', 'psb_result'],
            });

            var RefreshApplicationsStore = function() {Ext.getCmp("applications_grid").getStore().reload({params:{start:0 }, timeout: 300000});};

            var applicationsGrid = Ext.create('Ext.grid.Panel', {
                id      : 'applications_grid',
                store   : applicationsStore,
                cls     : 'gridCss',
                columns: [
                    {dataIndex: id, hidden: true},
                    {text: 'Item Details', dataIndex: 'item_desc', align: 'left', width: '47%', render: columnWrap},
                    {text: 'SG', dataIndex: 'posgrade', align: 'center', width: '10%', render: columnWrap},
                    {text: 'Status', dataIndex: 'psb_status', align: 'left', width: '37%', render: columnWrap},
                ],
                width   : '99%',
                // height  : 500,
                margin  : '0 0 10 0',
                plugins: [{
                    ptype: 'rowexpander',
                    rowBodyTpl: new Ext.XTemplate(
                        '<p><b>Date of Lineup:</b> {date_lineup}</p>',
                        '<p><b>Date HR Test:</b> {date_hr_test}</p>',
                        '<p><b>HR Test Details:</b> {remarks_hr_test}</p>',
                        '<p><b>Date Interview:</b> {date_interview}</p>',
                        '<p><b>Interview Details:</b> {remarks_interview}</p>',
                        '<p><b>Background Investigation:</b> {is_done_bi}</p>',
                        '<p><b>Potential Assessment Form:</b> {is_done_paf}</p>',
                        '<p><b>Next-in-Rank:</b> {is_done_nir}</p>',
                        '<p><b>Date PSB:</b> {date_psb}</p>',
                        '<p><b>PSB Result:</b> {psb_result}</p>',
                    )
                }]
            });
            RefreshApplicationsStore();

            var centerPanel = Ext.create('Ext.panel.Panel', {
                region  : 'center',
                autoScroll : true,
                buttonAlign : 'center',
                html    : htmlLoad.applyTemplate(null),
                buttons: [{
                    text    : 'Close',
                    icon    : './image/close.png',
                    handler: function() {
                        mainWindow.close();
                    }
                }]
            });

            var eastPanel = Ext.create('Ext.panel.Panel', {
                title       : 'Applications',
                split       : true,
                collapsed   : true,
                collapsible : true,
                region      : 'east',
                autoScroll  : true,
                width       : '50%',
                items       : [applicationsGrid]
            });

            mainWindow = Ext.create('Ext.window.Window', {
                title       : 'Applicant Details',
                header      : {titleAlign: 'center'},
                closable    : true,
                modal       : true,
                width       : 1200,
                height      : 600,
                resizable   : false,        
                layout      : 'border',
                items       : [eastPanel, centerPanel]
            }).show();
            Ext.MessageBox.hide();  
        }
    });    
}