<?php 
date_default_timezone_set('Asia/Manila');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>HRIS</title>
	<link rel="icon" type="image/x-icon" href="image/logo-ch-min.png">
	<!-- for extjs -->
	<script type="text/javascript" src="<?php echo base_url(); ?>extjs/extjs-build/ext-all.js"></script>
	<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>extjs/extjs-build/resources/css/ext-all.css"> -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>extjs/extjs-build/resources/css/ext-all-neptune.css">

	<!-- for css -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>css/myExt.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>css/menu.css"/>

	<script type="module" src = "https://unpkg.com/pdf-lib@1.4.0/dist/pdf-lib.min.js"></script>
	<!--<script type="text/javascript"src="<?php echo base_url(); ?>assets/js/Ext.ux.Exporter/Exporter-all.js"></script>
	<script type="text/javascript"src="<?php echo base_url(); ?>assets/js/Ext.ux.Exporter/Exporter-all.js"></script>-->

	<!-- messages -->
	<script type="text/javascript">	
		// var sheight = screen.availHeight-195;
		// var sheight = screen.availHeight-(screen.availHeight*0.20);
		// var swidth = screen.availWidth-(screen.availWidth*0.0625);	//6.25%. based on ratio and proportion of old implementation
		
		var sheight = window.innerHeight-(window.innerHeight*0.15);
		var swidth = window.innerWidth-(window.innerWidth*0.0625);	//6.25%. based on ratio and proportion of old implementation

		var setLimit = Math.round(window.innerWidth/38);

		function warningFunction(title_, msg_) {
			Ext.Msg.show({
				title   : title_,
				width   : '100%',
				msg     : msg_,
				closable: false,
				icon    : Ext.Msg.WARNING,
				buttons : Ext.Msg.OK
			});	
		}

		function infoFunction(title_, msg_) {
			Ext.Msg.show({
				title   : title_,
				width   : '100%',
				msg     : msg_,
				closable: false,
				icon    : Ext.Msg.INFO,
				buttons : Ext.Msg.OK
			});	
		}

		function infoLockFunction(title_, msg_) {
			Ext.Msg.show({
				title   : title_,
				width   : '100%',
				msg     : msg_,
				closable: false,
				icon    : 'lock-icon',
				buttons : Ext.Msg.OK
			});
		}

		function infoPanel(val_title, val_html) {
			Ext.create('Ext.window.Window', {
				title: val_title,
				width: 750,
				height: 410,
				layout: 'fit',
				closable: true,
                modal: true,
				resizable: false,
				maximizable : true,
				//autoScroll: true,
				items: {  // Let's put an empty grid in just to illustrate fit layout
					xtype: 'panel',
					html: val_html,
					margin: '12px',
					padding: '12px',
					overflowY: 'auto',
				}
			}).show();
		}

		function loadFunction(title_, msg_) {
			Ext.Msg.show({
				title   : title_,
				width   : '100%',
				msg     : msg_,
				closable: false,
				icon    : Ext.Msg.INFO
			});
		}

		function errorFunction(title_, msg_) {
			Ext.Msg.show({
				title   : title_,
				width   : '100%',
				msg     : msg_,
				closable: false,
				icon    : Ext.Msg.ERROR,
				buttons : Ext.Msg.OK
			});	
		}

		function processingFunction(msg_) {
			Ext.MessageBox.show({
				msg     : msg_,
				width   : '100%',
				wait    : true,
				waitConfig : {interval:100}
			});	
		}
		
		Ext.tip.QuickTipManager.init();	
		Ext.QuickTips.interceptTitles = true;
		Ext.QuickTips.init();

		if(Ext.isSafari && Ext.safariVersion == 7) {
		    delete Ext.tip.Tip.prototype.minWidth;
		} 
		
		if(Ext.isIE10) { 
	        Ext.supports.Direct2DBug = true;
	    }

      	function addTooltip(value, metadata, record, rowIndex, colIndex, store){
	        metadata.tdAttr = 'data-qtip="' + value + '"';
	        return value;
	    }
	</script>

	<!-- CRUD -->
	<script type="text/javascript">	
		function deleteFunction(url, params, extgrid, extgrid1) {
			processingFunction("Processing data, please wait...");

			Ext.Ajax.request({
			    url: url,
			    method	: 'POST',
			    params: params,
			    timeout: 1800000,
			    success: function(f,a) {
			    	try {
				    	Ext.MessageBox.hide();
						var response = Ext.decode(f.responseText);
						if (response.success == true) {
							Ext.getCmp(extgrid).getStore().reload({params:{start:0 }, timeout: 1000});
							if (extgrid1) Ext.getCmp(extgrid1).getStore().reload({params:{start:0 }, timeout: 1000}); 
							infoFunction("Status", response.data);
							// if (Ext.getCmp("pageToolbar"))	Ext.getCmp("pageToolbar").doRefresh();
						}
						else
							errorFunction("Error!", response.data);
					}
					catch(err) {
						errorFunction("Error!", 'Connection Problem / Error Occurred.');
						// errorFunction("Error!",err);
					}
			    },
				failure: function(f,action) { errorFunction("Error!", 'Please contact system administrator.'); }
			});
		}

		function addeditFunction(url, params, extgrid, extgrid1, extform, extwindow) {
			processingFunction("Processing data, please wait...");

			extform.submit({
				url: url,
				method: "POST",	
				params: params,
				timeout: 1800000,
				submitEmptyText: false,
			    success: function(f,action) {
			    	try {
						Ext.MessageBox.hide();
						if(action.result.success == true) {
							if(extwindow != null) extwindow.close();						
							if(extgrid) {Ext.getCmp(extgrid).getStore().reload({params:{reset:1, start:0 }, timeout: 1000});} 
							if(extgrid1) {Ext.getCmp(extgrid1).getStore().reload({params:{reset:1, start:0 }, timeout: 1000});}
							infoFunction("Status", action.result.data);
							// if (Ext.getCmp("pageToolbar"))	Ext.getCmp("pageToolbar").doRefresh();
						}
					}
					catch(err) {
						// errorFunction("Error!", 'Connection Problem / Error Occurred.');
						errorFunction("Error!",err);
					}
			    },
				failure: function(f,action) {errorFunction("Error!", action.result.data); }
		    }); 
		}
	</script> 

	<!-- Files -->
	<script type="text/javascript">	
		function export_excel(url, params, type) {
			processingFunction("Processing data, please wait...");

			Ext.Ajax.request({
			    url: url,
			    method	: 'POST',
			    params: params,
			    timeout: 1800000,
			    success: function(f,a) {
					var response = Ext.decode(f.responseText);
					Ext.MessageBox.hide();

					if (response.success == true) {
						if (type == "PDF")
							window.open("<?php echo base_url(); ?>"+response.filename,'PDFWindow','toolbar=0,menubar=0,location=0,di rectories=0,status=0,resizable=0');
						else
							window.location = "<?php echo base_url(); ?>"+response.filename;
					}
					else
						errorFunction("Error!", response.data);
			    },
				failure: function(f,action) { errorFunction("Error!", 'Please contact system administrator.'); }
			});			
		}

		function ExportDocument(url, params, type) {          
			Ext.Msg.show({
				title	: 'Confirmation',
				msg		: 'Are you sure you want to download in '+type+' format?',
				width	: '100%',
				icon	: Ext.Msg.QUESTION,
				buttons	: Ext.Msg.YESNO,
				fn: function(btn){
					if (btn == 'yes')
						export_excel(url, params, type);
				}
			});
		}
	</script>

	<!-- Date Range -->
	<script type="text/javascript">
	    Ext.apply(Ext.form.field.VTypes, {
	        daterange: function(val, field) {
	            var date = field.parseDate(val);

	            if(!date) {
	                return false;
	            }
	            if(field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
	                var start = field.up('form').down('#' + field.startDateField);
	                start.setMaxValue(date);
	                start.validate();
	                this.dateRangeMax = date;
	            }
	            else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
	                var end = field.up('form').down('#' + field.endDateField);
	                end.setMinValue(date);
	                end.validate();
	                this.dateRangeMin = date;
	            }
	            /*
	             * Always return true since we're only using this vtype to set the
	             * min/max allowed values (these are tested for after the vtype test)
	             */
	            return true;
	        },

	        daterangeText: 'Start date must be less than end date',

	        password: function(val, field) {
	            if (field.initialPassField) {
	                var pwd = field.up('form').down('#' + field.initialPassField);
	                return (val == pwd.getValue());
	            }
	            return true;
	        },

	        passwordText: 'Passwords do not match'
	    });

		Ext.define('Ext.form.field.Month', {
	        extend: 'Ext.form.field.Date',
	        alias: 'widget.monthfield',
	        requires: ['Ext.picker.Month'],
	        alternateClassName: ['Ext.form.MonthField', 'Ext.form.Month'],
	        selectMonth: null,
	        createPicker: function() {
	            var me = this,
	                format = Ext.String.format;
	            return Ext.create('Ext.picker.Month', {
	                pickerField: me,
	                ownerCt: me.ownerCt,
	                renderTo: document.body,
	                floating: true,
	                hidden: true,
	                focusOnShow: true,
	                minDate: me.minValue,
	                maxDate: me.maxValue,
	                disabledDatesRE: me.disabledDatesRE,
	                disabledDatesText: me.disabledDatesText,
	                disabledDays: me.disabledDays,
	                disabledDaysText: me.disabledDaysText,
	                format: me.format,
	                showToday: me.showToday,
	                startDay: me.startDay,
	                minText: format(me.minText, me.formatDate(me.minValue)),
	                maxText: format(me.maxText, me.formatDate(me.maxValue)),
	                listeners: {
	                    select: { scope: me, fn: me.onSelect },
	                    monthdblclick: { scope: me, fn: me.onOKClick },
	                    yeardblclick: { scope: me, fn: me.onOKClick },
	                    OkClick: { scope: me, fn: me.onOKClick },
	                    CancelClick: { scope: me, fn: me.onCancelClick }
	                },
	                keyNavConfig: {
	                    esc: function() {
	                        me.collapse();
	                    }
	                }
	            });
	        },
	        onCancelClick: function() {
	            var me = this;
	            me.selectMonth = null;
	            me.collapse();
	        },
	        onOKClick: function() {
	            var me = this;
	            if (me.selectMonth) {
	                me.setValue(me.selectMonth);
	                me.fireEvent('select', me, me.selectMonth);
	            }
	            me.collapse();
	        },
	        onSelect: function(m, d) {
	            var me = this;
	            me.selectMonth = new Date((d[0] + 1) + '/1/' + d[1]);
	        }
	    });

	</script>

	<script type="text/javascript">
		function UpdateSessionData(){    
		    Ext.Ajax.request({
		        url: 'commonquery/updateSession',
		        method: 'POST',
		        success: function(f,a) {
		            var response = Ext.decode(f.responseText);                                   		           
		            if(response.success == true) {
		                Ext.Msg.show({
		                    title   : 'Invalid Session',
		                    msg     : 'Session is already expired!. Please login again.',
		                    width   : '100%',
		                    closable: false,
		                    icon    : Ext.Msg.ERROR,
		                    buttons : Ext.Msg.OK,
		                    fn: function(btn){
		                        if(btn == 'ok') {
		                        	loadFunction('Route', 'Please wait. re-routing to login page.');
		                            Ext.Ajax.request({
								        url: 'logout/terminateSession',
								        method: 'POST',
								        params: {id: response.data},
								        success: function(f,a) {
        									window.location = "<?php echo base_url(); ?>";   	
        								}
								    });  
		                        }
		                    }		                    
		                });
		            }
		            else
		            	setTimeout("UpdateSessionData();", 10000);  
		        },
		        failure: function(f,action) { errorFunction("Error!", 'Please contact system administrator.'); }
		    });    
		}
	</script>

	<!-- other functions -->
	<script type="text/javascript">
		function dateRenderer(value) {
			try {
				return Ext.Date.format(new Date(value), 'M d, Y');
			}
			catch(err) {
				return value;
			}
		}
		function columnWrap(value) {
	        return '<div style= "white-space:normal !important;">' + value + '</div>';
	    }

	    function displayComponent(id, state) {
	        if(state == "show") {
	            Ext.getCmp(id).setVisible(true);
	            Ext.getCmp(id).setDisabled(false);
	        }
	        else {
	            Ext.getCmp(id).setVisible(false);
	            Ext.getCmp(id).setDisabled(true);
	        }   
	    }

	    function checkIfDirtyCell(value, metadata, record, rowIndex, colIndex, store) {
	        split = value.split("-");	//splits the original value to 2 values, FIRST is the boolean if dirty (1) or not (2), SECOND is the actual timestamp string
	        if(split[0] == "1") {
	        	metadata.innerCls = 'x-grid-dirty-cell';
	        	metadata.tdAttr = 'data-qtip="Incomplete"';
	        	return columnWrap(split[1]);
	        }
	        else if(split[0] == "0")
	        	return columnWrap(split[1]);	//wrap column before returning
	        else
	        	return columnWrap(value);
	    }

	    function testInterviewPsbRenderer(value) {
	    	if(value == "For HR Test" || value == "For Interview" || value == "Ready For PSB")
	    		return '<div style="background-color:#34bb50 !important;"><font color=white><b>' + value + '</b></font></div>';
	    	else if(value == "Done")
	    		return '<div style="background-color:transparent !important;"><font color=black><b>' + value + '</b></font></div>';
	    	else if(value == "Hold" || value == "Deferred")
				return '<div style="background-color:#ef5777 !important;"><font color=white><b>' + value + '</b></font></div>';
			else if(value == "Did Not Respond")
	    		return '<div style="background-color:#ffc048 !important;"><font color=white><b>' + value + '</b></font></div>';
	    }

		function MonitorablesStatusRenderer(value) {
			if (value == 'FOR REVIEW')
				return '<div style="background-color:#ef5777 !important;"><font color=white><b>' + 'FOR REVIEW' + '</b></font></div>';
			else if (value == 'FOR CONCURRENCE')
				return '<div style="background-color:#ef5777 !important;"><font color=white><b>' + 'FOR CONCURRENCE' + '</b></font></div>';
	    	else if (value == 'FOR APPROVAL')
	    		return '<div style="background-color:#ffc048 !important;"><font color=white><b>' + 'FOR APPROVAL' + '</b></font></div>';
			else if (value == 'APPROVED')
	    		return '<div style="background-color:#33cc33 !important;"><font color=white><b>' + 'APPROVED' + '</b></font></div>';
			else if (value == 'APPROVED PENDING EVALUATION')
	    		return '<div style="background-color: #33cc33 !important;"><font color=white><b>' +'FOR EVALUATION'+ '</b></font></div>';
			else if (value == 'FOR EVALUATION')
				return '<div style="background-color: #33cc33 !important;"><font color=white><b>' +'FOR EVALUATION'+ '</b></font></div>';
			else if (value == 'APPROVED AND EVALUATED')
	    		return '<div style="background-color: #33cc33 !important;"><font color=white><b>' + 'EVALUATED' + '</b></font></div>';
			else
	    		return '<div style="background-color:#ff0000 !important;"><font color=white><b>' + 'ERROR ' + value + '</b></font></div>';
	    }

		function MonitorablesPutolRenderer(value) {
			var tmp = document.createElement("DIV");
			tmp.innerHTML = value;
			//return tmp.textContent || tmp.innerText || "";
			var new_str = '';
			new_str = tmp.textContent; //value;
			return '<div style="white-space:normal !important;">'+ new_str.substring(0, 300)  + '... ' +'</div>';
	    }

		function divisionRenderer(value) {
			var div_string = "";
			if(value.includes(1)) {
				div_string = div_string + ('AD' + '<br>');
			}

			if(value.includes(2)) {
				div_string = div_string +('UDP'+ '<br>');
			}

			if(value.includes(6)) {
				div_string = div_string +('HCD'+ '<br>');
			}

			if(value.includes(4)) {
				div_string = div_string +('LHE'+ '<br>');
			}

	    	return '<div style="background-color:#34bb50 !important;"><font color=white><b>' + div_string + '</b></font></div>';
	    }

		function weekRenderer(value, meta) {
			//value[1] is count of daily logs
			if(value[0] == '' && value[1]!=0) {
				//meta.style = "background-color:pink; color:pink "	
				//metaData.attr = 'style="background-color:pink;"';
				meta.style = "background-color:pink; color:black "	
				return value[1];
			}
			else if(value[0] != '' && value[1]==0) {
				//meta.style = "background-color:pink; color:pink "	
				//metaData.attr = 'style="background-color:pink;"';
				return value[0];
			}
			else {				
				//meta.style = "background-color:green;"	
				return value[0];
			}
		}

		function sendSMS4(toWhom, txtMessage, senderModule) {
			Ext.Ajax.request({
				url: "commonquery/text_blast",
				method: 'POST',
				params: {
					sent_to:toWhom,
					sent_from_module:senderModule,
					txt_message:txtMessage,
				},
				success: function (response, opts) {
					Ext.Msg.alert('Status', 'Saved successfully.')
				},
				failure: function (response, opts) {
					Ext.Msg.alert('Status', 'Save Failed.');
				}
			})
		}

		function sendSMS2(sendThisJSON) {
			console.log ('attempting to send to multiple users')
			var data = new FormData();
			data.append("datajson", sendThisJSON);

			var xhr = new XMLHttpRequest();
			xhr.withCredentials = false;

			xhr.addEventListener("readystatechange", function() {
				if(this.readyState === 4) {
					console.log(this.responseText);
				}
			});

			xhr.open("POST", "https://smsgateway24.com/getdata/addalotofsms");
			xhr.send(data);
		}
	</script>
</head>