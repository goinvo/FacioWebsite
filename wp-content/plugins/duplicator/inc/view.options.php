<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
<div id="dialog-options" title="<?php _e("Duplicator Options", 'wpduplicator') ?>">
	<form id="form-duplicator-opts" method="post">
		<input type="hidden" name="action" value="settings" />
		<div id="dup-tabs-opts">
			<ul>
				<li><a href="#dup-tabs-opts-1"><?php _e("Package", 'wpduplicator') ?></a></li>
				<li><a href="#dup-tabs-opts-2"><?php _e("Installer", 'wpduplicator') ?></a></li>
				<li><a href="#dup-tabs-opts-3"><?php _e("System", 'wpduplicator') ?></a></li>
				<!--li><a href="#dup-tabs-opts-4"><?php _e("FTP", 'wpduplicator') ?></a></li>-->
			</ul>
			
			<!-- =============================================================================
			TAB 1 PACKAGE -->
			<div id="dup-tabs-opts-1">
				<div style="text-align:left;">
				
					<!-- PROCESSING -->
					<fieldset style="width:97%;">
						<legend><b><?php _e("Processing", 'wpduplicator') ?></b></legend>
						
						<?php 
							$safe_value = ini_get('safe_mode');
							if( stristr($safe_value, 'on') ){
								$max_time_val  = ini_get('max_execution_time');
								$max_read_only = "readonly='true'";
							} else {
								$max_time_val  = $GLOBALS['duplicator_opts']['max_time'];
								$max_read_only = "";
							}
						?>
						<table width="100%" border="0">
							<tr>
								<td style="width:50%"><?php _e("Max Time", 'wpduplicator') ?>: <input type="text" name="max_time" id="max_time" value="<?php echo $max_time_val ?>" <?php echo $max_read_only ?> maxlength="4" style="width:75px" /> <?php _e("seconds", 'wpduplicator') ?></td>
								<td style="width:50%"><?php _e("Max Memory", 'wpduplicator') ?>: <input type="text" name="max_memory" id="max_memory" value="<?php echo preg_replace('/\D/', '', $GLOBALS['duplicator_opts']['max_memory'] ) ?>" maxlength="4" style="width:45px" /> MB</td>
							</tr>
						</table>	
						<div style="display:inline-block; line-height:16px">
						<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?> /> 
						<label for="email-me">
							<?php 
								printf("%s: <i style='font-size:11px'>%s</i>",
									__("Email when completed", 'wpduplicator'),
									__("WP-Admin email is included.  Add extra emails semicolon separated.", 'wpduplicator'));
							?>
						</label><br/>
						<input type="text" name="email_others" id="email_others"  value="<?php echo $GLOBALS['duplicator_opts']['email_others'] ?>" style="width:95%" /> <br/>
						</div>
					</fieldset><br/>
					
					<!-- FILTERS -->
					<fieldset style="width:97%; line-height: 17px">
						<legend><b><?php _e("Exclusion Filters", 'wpduplicator') ?></b></legend>
						
						<label for="dir_bypass"><?php _e("Directories", 'wpduplicator') ?>: </label> 
						<textarea name="dir_bypass" id="dir_bypass" style="width:625px;height:50px;font-size:11px" /><?php echo $GLOBALS['duplicator_opts']['dir_bypass'] ?></textarea><br/>
						<div style='font-size:11px; margin:-6px 0px 5px 0px'><i><?php printf("%s: %s",__("Root Path", 'wpduplicator'), rtrim(DUPLICATOR_WPROOTPATH, '/'));	?></i></div>
						
						<label class="no-select"><?php _e("File extensions", 'wpduplicator') ?>:</label><br/>
						<input type="text" name="skip_ext" id="skip_ext"  value="<?php echo $GLOBALS['duplicator_opts']['skip_ext'] ?>" style="width:95%" /> <br/>
						
						<i style="font-size:11px;"><?php printf("%s (/path1;/path2 or exe;txt;)", __("Separate all filters by semicolon", 'wpduplicator')); ?></i>
					</fieldset><br/>
					
					<!-- ENCODEING OPTIONS -->
					<fieldset style="width:97%; line-height: 17px"  class='no-select'>
						<legend><b><?php _e("Database Encoding", 'wpduplicator') ?></b></legend>
						
							<input type="checkbox" name="dbiconv" id="dbiconv" <?php echo ($duplicator_dbiconv) ? 'checked="checked"' : ''; ?> /> 
							<label for="dbiconv"><?php _e("Enable character conversion encoding", 'wpduplicator') ?></label><br/>
					
							<i style='font-size:11px;'>
								<?php 
									printf("%s %s %s %s <br/> %s",
										__("From", 'wpduplicator'), DUPLICATOR_DB_ICONV_IN,
										__("to", 'wpduplicator'), DUPLICATOR_DB_ICONV_OUT,
										__("Disable this option for extended or double byte character sets", 'wpduplicator')) ;
										
								?>
							</i><br/><br/>					
							
					</fieldset>
					<!--div style='position:absolute; bottom:5px'>	
						<i style='font-size:10px'><?php _e("Having issues saving these options?  Temporarily disable all 'Object Caches' (i.e. W3C Total Object Cache)", 'wpduplicator') ?>.</i>
					</div-->
				</div>
			</div>
			
			
			<!-- =============================================================================
			TAB 2 INSTALLER -->
			<div id="dup-tabs-opts-2">
				<fieldset style="height:70px">
					<legend><b><?php _e("Settings Defaults", 'wpduplicator') ?></b></legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
						<tr>
							<td style="width:130px"><?php _e("Install URL", 'wpduplicator') ?></td>
							<td><input type="text" name="nurl" id="nurl" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>" /></td>
						</tr>
					</table>
				</fieldset><br/>
			
				<fieldset style="height:165px">
					<legend><b><?php _e("Database Defaults", 'wpduplicator') ?></b></legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td style="width:130px"><?php _e("Host", 'wpduplicator') ?></td>
						<td><input type="text" name="dbhost" id="dbhost" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbhost'] ?>"  maxlength="2100"/></td>
					</tr>
					<tr>
						<td><?php _e("Name", 'wpduplicator') ?></td>
						<td><input type="text" name="dbname" id="dbname" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbname'] ?>" maxlength="100" /></td>
					</tr>
					<tr>
						<td><?php _e("User", 'wpduplicator') ?></td>
						<td><input type="text" name="dbuser" id="dbuser" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbuser'] ?>"  maxlength="100" /></td>
					</tr>
					</table>
				</fieldset>
				<i style="font-size:11px"><?php _e("The installer can have these fields pre-filled at install time.  These values are optional.", 'wpduplicator') ?></i>
			</div>

			<!-- =============================================================================
			TAB 3 SYSTEM -->
			<div id="dup-tabs-opts-3">
				<fieldset style="height:100px">
					<legend><b><?php _e("Uninstall Options", 'wpduplicator') ?></b></legend>
					
						<input type="checkbox" name="rm_snapshot" id="rm_snapshot" <?php echo ($rm_snapshot) ? 'checked="checked"' : ''; ?> /> 
						<label for="rm_snapshot"><?php _e("Delete entire snapshot directory when removing plugin", 'wpduplicator') ?></label><br/>
						<i style='font-size:11px'><?php _e("Snapshot Directory", 'wpduplicator'); ?>: <?php echo duplicator_safe_path(DUPLICATOR_SSDIR_PATH); ?></i><br/>
					
				</fieldset>
			</div>


			<!--div id="dup-tabs-opts-4">
					FTP in Version 1.1
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td style="width:130px">Host</td>
						<td><input type="text" name="ftp-host" id="ftp-host" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-host'] ?>"  style="width:300px" /></td>
						<td>Port</td>
						<td><input type="text" name="ftp-port" id="ftp-port" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-port'] ?>" style="width:60px" /></td>
					</tr>
					<tr>
						<td style="white-space:nowrap">User Name</td>
						<td colspan="3"><input type="text" name="ftp-user" id="ftp-user" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-user'] ?>"  class="txt-settings"/></td>
					</tr>
					<tr>
						<td style="white-space:nowrap">Password</td>
						<td colspan="3"><input type="password" name="ftp-pass" id="ftp-pass" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-pass'] ?>"  class="txt-settings"/></td>
					</tr>
					</table>
			</div>-->
			
		</div>
		
		<input type="button" id="opts-save-btn" class="btn-save-opts" value="<?php _e("Save", 'wpduplicator') ?>" style="position:absolute;bottom:20px; right:115px" onclick="Duplicator.saveSettings()" />
		<input type="button" id="opts-close-btn" class="btn-save-opts" value="<?php _e("Close", 'wpduplicator') ?>" style="position:absolute;bottom:20px; right:30px" onclick="Duplicator.optionsClose()" />
	</form>
</div>