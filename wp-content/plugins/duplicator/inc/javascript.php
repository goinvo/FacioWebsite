<script type="text/javascript">

jQuery.noConflict()(function($) {
	jQuery(document).ready(function() {
	
	//Unique namespace
	Duplicator = new Object();
	Duplicator.DEBUG_AJAX_RESPONSE = false;
	Duplicator.AJAX_TIMER = null;
	
	Duplicator.startAjaxTimer = function() {
		Duplicator.AJAX_TIMER = new Date();
	}
	
	Duplicator.endAjaxTimer = function() {
		var endTime = new Date();
		Duplicator.AJAX_TIMER =  (endTime.getTime()  - Duplicator.AJAX_TIMER) /1000;
	}
	
	/** **********************************************
	*  METHOD: Duplicator.setStatus  
	*  Sets the status of the Duplicator status bar */
	Duplicator.setStatus = function(msg, img, postmsg) {
		//Clean Status Bar
		$("#img-status-error").hide();
		$("#img-status-progress").hide();
		
		$('#span-status').html(msg);
		switch (img) {
			case 'error' 	: $("#img-status-error").show('slow'); break;
			case 'progress' : $("#img-status-progress").show('slow'); break;
		}
		$('#span-status-post').html(postmsg);
	}
	

	/** **********************************************
	*  METHOD: Duplicator.toggleToolbarState  
	*  Disables or enables the toolbar
	*  @param state		Disabled/Enabled */ 
	Duplicator.toggleToolbarState = function(state) {
		if (state == "DISABLED") {
			$('#toolbar-table input, div#duplicator-installer').attr("disabled", "true");
			$('#toolbar-table input, div#duplicator-installer').css("background-color", "#efefef");
		} else {
			$('#toolbar-table input, div#duplicator-installer').removeAttr("disabled");
			$('#toolbar-table input, div#duplicator-installer').css("background-color", "#f9f9f9");
		}	
	}
	

	/** **********************************************
	*  METHOD: Duplicator.reload  
	*  Performs reloading the page and diagnotic handleing */
	Duplicator.reload = function(data) {
		if (Duplicator.DEBUG_AJAX_RESPONSE) {
			Duplicator.showSystemError('debug on', data);
		} else {
			Duplicator.toggleToolbarState("ENABLED");
			window.location.reload();
		}
	}
	

	/** **********************************************
	*  METHOD: Duplicator.createPackage  
	*  Performs Ajax post to create a new package
	*  Timeout (10000000 = 166 minutes) */
	Duplicator.createPackage = function(packname) {
		Duplicator.toggleToolbarState("DISABLED");

		$.ajax({
			type: "POST",
			url: ajaxurl,
			timeout: 10000000,
			data: "package_name=" + packname +"&action=duplicator_create",
			beforeSend: function() {Duplicator.startAjaxTimer(); },
			complete: function() {Duplicator.endAjaxTimer(); },
			success:    function(data) { 
				Duplicator.reload(data);
			},
			error:      function(data) { 
				Duplicator.showSystemError('Duplicator.createPackage', data);
				Duplicator.toggleToolbarState("ENABLED");
			}
		});
	}


	/** **********************************************
	 *  METHOD: Save Settings
	 *  Saves the Settings */
	Duplicator.saveSettings = function() {
		var q;
		var email_me   		= $('#email-me').is(':checked') ? 1 : 0;
		var dbiconv    		= $('#dbiconv').is(':checked')  ? 1 : 0;
		var log_level  		= $("select#log_level").val() ? $("select#log_level").val() : 0;
		var email_others	= $("input#email_others").val();
		var dir_bypass 		= $("textarea#dir_bypass").val();
		var rm_snapshot   	= $('#rm_snapshot').is(':checked') ? 1 : 0;

		//append semicolon if user forgot
		if (dir_bypass.length > 1) {
			var has_semicolon	= dir_bypass.charAt(dir_bypass.length - 1) == ";";
			var dir_bypass		= (has_semicolon) ? dir_bypass : dir_bypass + ";";
			$("textarea#dir_bypass").val(dir_bypass);
		}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			timeout: 10000000,
			data: 
			{
				'action'  		: 'duplicator_settings',
				'dbhost' 		: $("input#dbhost").val(),
				'dbname'  		: $("input#dbname").val(),
				'dbuser'  		: $("input#dbuser").val(),
				'nurl'  		: $("input#nurl").val(),
				'dbiconv'  		: dbiconv,
				'email-me'  	: email_me,
				'email_others'  : email_others,
				'max_time'  	: $("input#max_time").val(),
				'max_memory'  	: $("input#max_memory").val(),
				'skip_ext'  	: $("input#skip_ext").val(),
				'dir_bypass'  	: $("textarea#dir_bypass").val(),
				'log_level'  	: log_level,
				'rm_snapshot'  	: rm_snapshot
			},
			beforeSend: function() {Duplicator.startAjaxTimer(); },
			complete: function() {Duplicator.endAjaxTimer(); },
			success: function(data) { 
				$('#opts-save-btn').val("<?php _e('Saving', 'wpduplicator') ?>...");
				window.location.reload();
			},
			error: function(data) { 
				Duplicator.showSystemError('Duplicator.saveSettings', data);
			}
		});
	 }


	/** **********************************************
	 *  METHOD: Delete Package
	 *  Removes all selected package sets */
	Duplicator.deletePackage = function (event) {
		var arr = new Array;
		var count = 0;
		$("input[name=delete_confirm]").each(function() {
			 if (this.checked) { arr[count++] = this.id; }
		});
		var list = arr.join(',');
		if (list.length == 0) {
			alert("<?php _e('Please select at least one package to delete.', 'wpduplicator') ?>");
			return;
		}
		
		var answer = confirm("<?php _e('Are you sure, you want to delete the selected package(s)?', 'wpduplicator') ?>");
		if (answer){
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: "duplicator_delid="+list+"&action=duplicator_delete",
				beforeSend: function() {Duplicator.startAjaxTimer(); },
				complete: function() {Duplicator.endAjaxTimer(); },
				success: function(data) { 
					Duplicator.reload(data); 
				},
				error: function(data) { 
					Duplicator.showSystemError('Duplicator.deletePackage', data);
				}
			});
		} else {
			Duplicator.setStatus("<?php _e('Ready to create new package.', 'wpduplicator') ?>");
		}
		if (event)
			event.preventDefault(); 
	};


	/** **********************************************
	 *  ATTACHED EVENT: Submit Main Form
	 *  Process Package and Installer */
	$("#form-duplicator").submit(function (event) {
		event.preventDefault();   
		
		//Validate length test
		if ($("input[name=package_name]").val().length <= 0) 	{
			Duplicator.setStatus("<?php _e('Please enter a backup name.', 'wpduplicator') ?>", "error");
			return;
		}
		
		//Vatlidate alphanumeric test
		var newstring = $("input[name=package_name]").val().replace(/ /g, "");
		$("input[name=package_name]").val(newstring)
		if ( ! /^[0-9A-Za-z|_]+$/.test($("input[name=package_name]").val())) {
			Duplicator.setStatus("<?php _e('Alpanumeric characters only on package name', 'wpduplicator') ?>", "error");
			return;
		}
		
		var packname = $("input[name=package_name]").val();
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: "duplicator_new="+ packname +"&action=duplicator_system_check",
			beforeSend: function() {
				Duplicator.setStatus("<?php _e("Evaluating WordPress Setup. Please Wait", 'wpduplicator') ?>...", 'progress');
			},
			success: function(data) {
				Duplicator.setStatus("<?php _e('Ready to create new package.', 'wpduplicator') ?>");
				if (data.Success) {
					Duplicator.showCreateConfirmation(packname);
				} else {
					Duplicator.showSystemCheck(data);
				}
			},
			error: function(data) { 
				Duplicator.showSystemError('form-duplicator submit', data);
			}
		});
	});


	/*  ============================================================================
	MAIN GRID
	Actions that revolve around the main grid */
	$("input#select-all").click(function (event) {
		var state = $('input#select-all').is(':checked') ? 1 : 0;
		$("input[name=delete_confirm]").each(function() {
			 this.checked = (state) ? true : false;
			 Duplicator.rowColor(this);
		});
	});

	Duplicator.rowColor = function(chk) {
		if (chk.checked) {
			$(chk).parent().parent().css("text-decoration", "line-through");
		} else {
			$(chk).parent().parent().css("text-decoration", "none");
		}
	}
	
	Duplicator.toggleDetail = function(id) {
		$('#' + id).toggle();
		return false;
	}
	
		
	Duplicator.downloadFile = function(name, button) {
		$(button).addClass('dup-button-selected');
		window.open(name, '_self'); 
		return false;
	}
	


	/*  ============================================================================
	DIALOG: WINDOWS
	Browser Specific. IE9 does not support modal correctly this is a workaround  */
	Duplicator._dlgCreate = function(evt, ui) {
		if (! $.browser.msie) {
			$('#' + this.id).dialog('option', 'modal',  	true);
			$('#' + this.id).dialog('option', 'draggable',  true);
		} else {
			$('#' + this.id).dialog('option', 'draggable',  false);
			$('#' + this.id).dialog('option', 'open',  function() {$("div#wpwrap").addClass('ie-simulated-overlay');} );
		}
	}
	Duplicator._dlgClose = function(evt, ui) {
		if ($.browser.msie) {$("div#wpwrap").removeClass('ie-simulated-overlay');}
	}
	$("#dialog-options").dialog( {autoOpen:false, height:610, width:750, create:Duplicator._dlgCreate, close:Duplicator._dlgClose });
	$("#dup-dlg-system-check").dialog({autoOpen:false, height:600, width:700, create:Duplicator._dlgCreate, close:Duplicator._dlgClose, modal: true, buttons: {<?php _e("Cancel", 'wpduplicator') ?>: function() { $(this).dialog("close");}}});
	$("#dup-dlg-system-error").dialog({autoOpen:false, height:550, width:650, create:Duplicator._dlgCreate, close:Duplicator._dlgClose });	
	$("#dup-dlg-quick-path").dialog({autoOpen:false, height:355, width:800, create:Duplicator._dlgCreate, close:Duplicator._dlgClose });	
	$("#dup-dlg-package-confirm").dialog(
		{autoOpen:false, height:285, width:625, create:Duplicator._dlgCreate, close:Duplicator._dlgClose,
		buttons: {
				"<?php _e('Create Package Set', 'wpduplicator') ?>" : function() {
					$(this).dialog("close");
					Duplicator.processCreateConfirmation(true);
				},
				Cancel: function() {
					$(this).dialog("close");
					Duplicator.processCreateConfirmation(false);
				}
			}
		}
	);	
	

	/*  ============================================================================
	DIALOG:	OPTIONS DIALOG 
	Actions that revolve around the options dialog */
	$("#dup-tabs-opts").tabs();
	Duplicator.optionsAppendByPassList = function(path) {
		Duplicator.optionsOpen();
		 $('#dir_bypass').append(path + ";");
		 $('#dup-tabs-opts').tabs('option', 'selected', 0);
		 $('#dir_bypass').animate({ borderColor: "blue", borderWidth: 2 }, 3000);
		 $('#dir_bypass').animate({ borderColor: "#dfdfdf", borderWidth: 1  }, 100);
	}
	Duplicator.optionsOpen  = function() {$("div#dialog-options").dialog("open");}
	Duplicator.optionsClose = function() {$('div#dialog-options').dialog('close');}


	/*  ============================================================================
	DIALOG: SYSTEM-CHECKS
	Actions that revolve around the systems check dialog */
	Duplicator.showSystemCheck = function(data) {
		//Set Pass/Fail Flags
		for (key in data) {
			var html = (data[key] == 'Fail') ? "<div class='dup-sys-fail'>Fail</div>" : "<div class='dup-sys-pass'>Pass</div>";
			$("#" + key).html(html)
		}

		$('#system-check-msg').animate({ scrollTop: $('#system-check-msg').attr("scrollHeight") }, 2000)
		$("#dup-dlg-system-check").dialog("open");
		Duplicator.setStatus("<?php _e('Ready to create new package.', 'wpduplicator'); ?>");
	}	

	//Performs the ajax request for a system check
	Duplicator.getSystemCheck = function() {
		Duplicator.setStatus("<?php _e('Checking System Status.  Please Wait!', 'wpduplicator'); ?>", 'progress');
		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: "action=duplicator_system_check",
			beforeSend: function() {Duplicator.startAjaxTimer(); },
			complete: function() {Duplicator.endAjaxTimer(); },			
			success: function(data) {Duplicator.showSystemCheck(data);},
			error: function(data)   {
				Duplicator.showSystemError('Duplicator.getSystemCheck', data);
			}
		});
	}
	
	//Show the size and file count of the directory to be zipped
	Duplicator.getSystemDirectory = function() {
		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: "action=duplicator_system_directory",
			beforeSend: function() { 
				Duplicator.startAjaxTimer(); 
				var html = "<?php _e('Scanning Please Wait', 'wpduplicator'); ?>... " + "<img src='<?php echo DUPLICATOR_PLUGIN_URL  ?>img/progress.gif' style='height:7px; width:46px;'  />" ;
				$('#dup-sys-scannow-data, #dup-dlg-package-confirm-scannow-data').html(html);	
			},
			complete: function() {Duplicator.endAjaxTimer(); },
			success: function(data) {
				var size    =  data.size 	|| "<?php _e('unreadable', 'wpduplicator'); ?>";
				var count   =  data.count 	|| "<?php _e('unreadable', 'wpduplicator'); ?>";
				var folders =  data.folders || "<?php _e('unreadable', 'wpduplicator'); ?>";
				var flag    =  (data.flag || size.indexOf("-") != -1) ? "<?php _e('*Scan Error', 'wpduplicator'); ?>" : "";
				var html    =  size + " " + count +  " <?php _e('Files', 'wpduplicator'); ?>, " + folders +  " <?php _e('Folders', 'wpduplicator'); ?> " + flag; 
				$('#dup-sys-scannow-data, #dup-dlg-package-confirm-scannow-data').html("<i>" + html + "</i>");
				
			},
			error: function(data)   {
				$('#dup-sys-scannow-data, #dup-dlg-package-confirm-scannow-data').html("<?php _e('error scanning directory', 'wpduplicator'); ?>");
				Duplicator.showSystemError('Duplicator.getSystemDirectory', data);
			}
		});
	}
	
	//Toggle the system requirment details
	Duplicator.showSystemDetails = function() {
		if ($(this).parents('li').children('div.dup-sys-check-data-details').is(":hidden")) {
			$(this).children('span').addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');;
			$(this).parents('li').children('div.dup-sys-check-data-details').show(250);
		} else {
			$(this).children('span').addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
			$(this).parents('li').children('div.dup-sys-check-data-details').hide(250);
		}
	}

	//Make the system requirments toggle
	$('#dup-sys-check-data-reqs a').each(function() {
		$(this).attr('href', 'javascript:void(0)');
		$(this).click(Duplicator.showSystemDetails);
		$(this).prepend("<span class='ui-icon ui-icon-triangle-1-e dup-toggle' />");
	});
	
	/*  ============================================================================
	DIALOG: QUICK PATH
	Shows the Quick Path Dialog 'Show Download Links' */
	Duplicator.showQuickPath = function(db, install, pack) {
		$("#dup-dlg-quick-path").dialog("open");
		var msg = <?php printf('"%s:\n" + db + "\n\n%s:\n" + install + "\n\n%s:\n" + pack;', 
		__("DATABASE",  'wpduplicator'), 
		__("PACKAGE", 'wpduplicator'), 
		__("INSTALLER",   'wpduplicator')); 
		?>
		$("#dup-dlg-quick-path-data").val(msg);

		return false;
	}
	
	Duplicator.selectQuickPath = function() {$('#dup-dlg-quick-path-data').select();}
	$(".dup-dlg-quick-path-download-link").button({ icons: {primary: "ui-icon-locked"} });
	$(".dup-dlg-quick-path-database-link").button({ icons: {primary: "ui-icon-script"} });
	$(".dup-installer-btn").button({ icons: {primary: "ui-icon-disk"} });
	
	
	/*  ============================================================================
	DIALOG: SYSTEM ERROR
	Show the Sytem Error Dialog */
	Duplicator.showSystemError = function(action, xhrData) {
		Duplicator.endAjaxTimer();
		var time = Duplicator.AJAX_TIMER || 'not set';
		var msg  = '<?php _e('AJAX Response', 'wpduplicator') ?>' + ' ' + action + '<br/>';
		msg += "duration: " + time + " secs<br/>code: " + xhrData.status + "<br/>status: " + xhrData.statusText + "<br/>response: " +  xhrData.responseText;
		$("#dup-system-err-msg2").html(msg);
		$("#dup-dlg-system-error").dialog("open");
		Duplicator.setStatus("<?php _e('Ready to create new package.', 'wpduplicator'); ?>");
	}
	
	
	/*  ============================================================================
	DIALOG: CREATE PACKAGE CONFIRMATION
	Show the Create package dialog */
	Duplicator.showCreateConfirmation = function(packname) {
		$("#dup-dlg-package-confirm-msg").html(packname);
		$("#dup-dlg-package-confirm").dialog('open');
	}
	
	Duplicator.processCreateConfirmation = function(result) {
		if (result) {
			var msg = "<?php _e('Creating package may take several minutes. Please Wait... ', 'wpduplicator'); ?>";
			var postmsg = "<?php printf(" &nbsp; <a href='javascript:void(0)' onclick='Duplicator.openLog()'>[%s]</a>", 	__('Preview Log', 'wpduplicator'));?>";
			Duplicator.setStatus(msg, 'progress', postmsg);
			Duplicator.createPackage($("input[name=package_name]").val());
		} else {
			Duplicator.setStatus("<?php _e('Ready to create new package.', 'wpduplicator') ?>");
		}
		return result;
	}
	

	/*  ============================================================================
	MISC ROUTINES */
	$("div#div-render-blanket").show();
	Duplicator.newWindow = function(url) {window.open(url);}

	Duplicator.openLog = function() { 				
		window.open('<?php echo DUPLICATOR_PLUGIN_URL .'files/log-view.php'; ?>', 'duplicator_logs');
	}



	});
});
</script>
