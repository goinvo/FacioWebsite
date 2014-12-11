<?php
/*
Plugin Name: blogVault
Plugin URI: http://blogvault.net/
Description: Easiest way to backup your blog
Author: akshat
Author URI: http://blogvault.net/
Version: 0.87
 */

/* Global response array */
global $bvVersion;
global $bvRespArray;
$bvRespArray = array("blogvault" => "response");
$bvVersion = '0.87';

function bvStatusAdd($key, $value) {
	global $bvRespArray;
	$bvRespArray[$key] = $value;
}

function bvStatusAddArray($key, $value) {
	global $bvRespArray;
	if (!isset($bvRespArray[$key])) {
		$bvRespArray[$key] = array();
	}
	$bvRespArray[$key][] = $value;
}

class bvHttpClient {
	var $user_agent = 'bvHttpClient';
	var $host;
	var $port;
	var $timeout = 20;
	var $errormsg = "";
	var $conn;
	var $mode;

	function bvHttpClient() {
		$host = "pluginapi.blogvault.net";
		if ($_REQUEST['svrno']) {
			$sno = intval($_REQUEST['svrno']);
			$host = "pluginapi".$sno.".blogvault.net";
		}
		$this->mode = $_REQUEST['mode'];
		if ($this->mode === "resp") {
			bvStatusAdd("mode", "resp");
			return;
		}
		$this->mode = $_REQUEST['mode'];
		if ($this->mode === "resp") {
			bvStatusAdd("mode", "resp");
			return;
		}
		$this->host = $host;
		$this->port = 80;
		if ($_REQUEST['ssl']) {
			$this->port = 443;
			$this->host = $_REQUEST['ssl']."://".$host;
		}
		if (!$this->conn = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			$this->errormsg = "Cannot Open Connection tp Host";
			bvStatusAdd("httperror", "Cannot Open Connection to Host");
			return;
		}
		socket_set_timeout($this->conn, $this->timeout);
	}

	function streamedPost($url,  $headers = array()) {
		$headers['Transfer-Encoding'] = "chunked";
		$this->sendRequest("POST", $url, $headers);
	}

	function newChunk($data) {
		if ($this->mode === "resp") {
			echo("bvchunk:");
		}
		$this->write(sprintf("%x\r\n", strlen($data)));
		$this->write($data);
		$this->write("\r\n");
	}

	function closeChunk() {
		$this->newChunk("");
	}

	function uploadChunkedFile($url, $field, $filename)
	{
		$this->multipartChunkedPost($url, array("Content-Disposition" => "form-data; name=\"".$field."\"; filename=\"".$filename."\"", "Content-Type" => "application/octet-stream"));
	}

	function multipartChunkedPost($url, $mph = array(), $headers = array()) {
		$rnd = rand(100000, 999999);
		$this->boundary = "----".$rnd;
		$prologue = "--".$this->boundary."\r\n";
		foreach($mph as $key=>$val) {
			$prologue .= $key.":".$val."\r\n";
		}
		$prologue .= "\r\n";
		$epilogue = "\r\n\r\n--".$this->boundary."--\r\n";
		$headers['Content-Type'] = "multipart/form-data; boundary=".$this->boundary;
		$this->streamedPost($url, $headers);
		$this->newChunk($prologue);
	}

	function newChunkedPart($data) {
		if (strlen($data) > 0)
			$this->newChunk($data);
	}

	function closeChunkedPart() {
		$epilogue = "\r\n\r\n--".$this->boundary."--\r\n";
		$this->newChunk($epilogue);
		$this->closeChunk();
	}

	function write($data) {
		if ($this->mode === "resp") {
			echo($data);
		} else {
			fwrite($this->conn, $data);
		}
	}

	function get($url, $headers = array()) {
		return $this->request("GET", $url, $headers);
	}

	function post($url, $headers = array(), $body = "") {
		if(is_array($body)) {
			$b = "";
			foreach($body as $key=>$val) {
				$b .= $key."=".urlencode($val)."&";
			}
			$body = substr($b, 0, strlen($b) - 1);
		}
		if ($this->mode === "resp") {
			$this->newChunk("bvpost:".$body);
		}
		return $this->request("POST", $url, $headers, $body);
	}

	function request($method, $url, $headers = array(), $body = null) {
		$this->sendRequest($method, $url, $headers, $body);
		return $this->getResponse();
	}

	function sendRequest($method, $url, $headers = array(), $body = null) {
		if ($this->mode === "resp") {
			return;
		}
		$def_hdrs = array("Connection" => "keep-alive",
			"Host" => $this->host);
		$headers = array_merge($def_hdrs, $headers);
		$request = strtoupper($method)." ".$url." HTTP/1.1\r\n";
		if (null != $body) {
			$headers["Content-length"] = strlen($body);
		} else {
			$headers["Content-length"] = 0;
		}
		foreach($headers as $key=>$val) {
			$request .= $key.":".$val."\r\n";
		}
		$request .= "\r\n";
		if (null != $body) {
			$request .= $body;
		}
		$this->write($request);
		return $request;
	}

	function getResponse() {
		$response = array();
		$response['headers'] = array();
		$state = 1;
		$conlen = 0;
		if ($this->mode === "resp") {
			return $response;
		}
		stream_set_timeout($this->conn, 300);
		while (!feof($this->conn)) {
			$line = fgets($this->conn, 4096);
			if (1 == $state) {
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$response['errormsg'] = "Status code line invalid: ".htmlentities($line);
					return $response;
				}
				$response['http_version'] = $m[1];
				$response['status'] = $m[2];
				$response['status_string'] = $m[3];
				$state = 2;
				bvStatusAdd("respstatus", $response['status']);
				bvStatusAdd("respstatus_string", $response['status_string']);
			} else if (2 == $state) {
				# End of headers
				if (2 == strlen($line)) {
					if ($conlen > 0)
						$response['body'] = fread($this->conn, $conlen);
					return $response;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				$response['headers'][$key] = $val;
				if ($key == "content-length") {
					$conlen = intval($val);
				}
			}
		}
		return $response;
	}
}

function bvGetUrl($method) {
	global $bvVersion;
	$baseurl = "/bvapi/";
	$time = time();
	if ($time < get_option('bvLastSendTime')) {
		$time = get_option('bvLastSendTime') + 1;
	}
	update_option('bvLastSendTime', $time);
	$public = urlencode(get_option('bvPublic'));
	$secret = urlencode(get_option('bvSecretKey'));
	$time = urlencode($time);
	$version = urlencode($bvVersion);
	$sig = md5($public.$secret.$time.$version);
	return $baseurl.$method."?sig=".$sig."&bvTime=".$time."&bvPublic=".$public."&bvVersion=".$version;
}

function bvScanFiles($initdir = "./", $offset = 0, $limit = 0, $bsize = 512) {
	$i = 0;
	$j = 0;
	$dirs = array();
	$dirs[] = $initdir;
	$j++;
	$bfc = 0;
	$bfa = array();
	$current = 0;
	$recurse = true;
	if ($_REQUEST["recurse"] == "false") {
		$recurse = false;
	}
	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$clt->uploadChunkedFile(bvGetUrl("listfiles")."&recurse=".$_REQUEST["recurse"]."&offset=".$offset."&initdir=".urlencode($initdir), "fileslist", "allfiles");
	while ($i < $j) {
		$dir = $dirs[$i];
		$d = opendir(ABSPATH.$dir);
		while ($d && (($file = readdir($d)) !== false)) {
			if ($file == '.' || $file == '..') { continue; }
			$relfile = $dir.$file;
			$absfile = ABSPATH.$relfile;
			if (is_dir($absfile)) {
				if (is_link($absfile)) { continue; }
				$dirs[] = $relfile."/";
				$j++;
			}
			$stats = @stat($absfile);
			$fdata = array();
			if (!$stats)
				continue;
			$current++;
			if ($offset >= $current)
				continue;
			if (($limit != 0) && (($current - $offset) > $limit)) {
				$i = $j;
				break;
			}
			foreach(preg_grep('#size|uid|gid|mode|mtime#i', array_keys($stats)) as $key ) {
				$fdata[$key] = $stats[$key];
			}

			$fdata["filename"] = $relfile;
			if (($fdata["mode"] & 0xF000) == 0xA000) {
				$fdata["link"] = @readlink($absfile);
			}
			$bfa[] = $fdata;
			$bfc++;
			if ($bfc == 512) {
				$str = serialize($bfa);
				$clt->newChunkedPart(strlen($str).":".$str);
				$bfc = 0;
				$bfa = array();
			}
		}
		closedir($d);
		$i++;
		if ($recurse == false)
			break;
	}
	if ($bfc != 0) {
		$str = serialize($bfa);
		$clt->newChunkedPart(strlen($str).":".$str);
	}
	$clt->closeChunkedPart();
	$resp = $clt->getResponse();
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

function bvGetValidFiles($files)
{
	$outfiles = array();
	foreach($files as $file) {
		if (!file_exists($file) || !is_readable($file) ||
		    (!is_file($file) && !is_link($file))) {
			bvStatusAddArray("missingfiles", $file);
			continue;
		}
		$outfiles[] = $file;
	}
	return $outfiles;
}

function bvFileStat($file) {
	$stats = @stat(ABSPATH.$file);
	$fdata = array();
	foreach(preg_grep('#size|uid|gid|mode|mtime#i', array_keys($stats)) as $key ) {
		$fdata[$key] = $stats[$key];
	}

	$fdata["filename"] = $file;
	return $fdata;
}

function bvFileMD5($files, $offset = 0, $limit = 0, $bsize = 102400) {
	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$clt->uploadChunkedFile(bvGetUrl("filesmd5")."&offset=".$offset, "filemd5", "list");
	$files = bvGetValidFiles($files);
	foreach($files as $file) {
		$fdata = array();
		$fdata = bvFileStat($file);
		$_limit = $limit;
		$_bsize = $bsize;
		if (!file_exists(ABSPATH.$file)) {
			bvStatusAddArray("missingfiles", $file);
			continue;
		}
		if ($offset == 0 && $_limit == 0) {
			$md5 = md5_file(ABSPATH.$file);
		} else {
			if ($_limit == 0)
				$_limit = $fdata["size"];
			if ($offset + $_limit < $fdata["size"])
				$_limit = $fdata["size"] - $offset;
			$handle = fopen(ABSPATH.$file, "rb");
			$ctx = hash_init('md5');
			fseek($handle, $offset, SEEK_SET);
			$dlen = 1;
			while (($_limit > 0) && ($dlen > 0)) {
				if ($_bsize > $_limit)
					$_bsize = $_limit;
				$d = fread($handle, $_bsize);
				$dlen = strlen($d);
				hash_update($ctx, $d);
				$_limit -= $dlen;
			}
			fclose($handle);
			$md5 = hash_final($ctx);
		}
		$fdata["md5"] = $md5;
		$sfdata = serialize($fdata);
		$clt->newChunkedPart(strlen($sfdata).":".$sfdata);
	}
	$clt->closeChunkedPart();
	$resp = $clt->getResponse();
	if ($resp['status'] != '200') {
		return false;
	}
	
	return true;
}

function bvUploadFiles($files, $offset = 0, $limit = 0, $bsize = 102400) {
	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$clt->uploadChunkedFile(bvGetUrl("filedump")."&offset=".$offset, "filedump", "data");

	foreach($files as $file) {
		if (!file_exists(ABSPATH.$file)) {
			bvStatusAddArray("missingfiles", $file);
			continue;
		}
		$handle = fopen(ABSPATH.$file, "rb");
		if (($handle != null) && is_resource($handle)) {
			$fdata = bvFileStat($file);
			$sfdata = serialize($fdata);
			$_limit = $limit;
			$_bsize = $bsize;
			if ($_limit == 0)
				$_limit = $fdata["size"];
			if ($offset + $_limit > $fdata["size"])
				$_limit = $fdata["size"] - $offset;
			$clt->newChunkedPart(strlen($sfdata).":".$sfdata.$_limit.":");
			fseek($handle, $offset, SEEK_SET);
			$dlen = 1;
			while (($_limit > 0) && ($dlen > 0)) {
				if ($_bsize > $_limit)
					$_bsize = $_limit;
				$d = fread($handle, $_bsize);
				$dlen = strlen($d);
				$clt->newChunkedPart($d);
				$_limit -= $dlen;
			}
			fclose($handle);
		} else {
			bvStatusAddArray("unreadablefiles", $file);
		}
	}
	$clt->closeChunkedPart();
	$resp = $clt->getResponse();
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

/* This informs the server about the activation */
function bvActivateServer() {
	global $wpdb;
	global $bvVersion;
	$body = array();
	$body['wpurl'] = urlencode(get_bloginfo("wpurl"));
	$body['abspath'] = urlencode(ABSPATH);
	if (defined('DB_CHARSET'))
		$body['dbcharset'] = urlencode(DB_CHARSET);
	$body['dbprefix'] = urlencode($wpdb->base_prefix);
	$body['bvversion'] = urlencode($bvVersion);
	$body['serverip'] = urlencode($_SERVER['SERVER_ADDR']);
	if (extension_loaded('openssl')) {
		$body['openssl'] = "1";
	}
	if (is_ssl()) {
		$body['https'] = "1";
	}
	$all_tables = bvGetAllTables();
	$i = 0;
	foreach ($all_tables as $table) {
		$body["all_tables[$i]"] = urlencode($table);
		$i++;
	}

	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$resp = $clt->post(bvGetUrl("activate"), array(), $body);
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

function bvDeactivateServer() {
	$body = array();
	$body['wpurl'] = urlencode(get_bloginfo("wpurl"));
	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$resp = $clt->post(bvGetUrl("deactivate"), array(), $body);
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

function bvDeactivateHandler() {
    bvDeactivateServer();
}

function bvListTables() {
	global $wpdb;

	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$clt->uploadChunkedFile(bvGetUrl("listtables"), "tableslist", "status");
	$data["listtables"] = $wpdb->get_results( "SHOW TABLE STATUS", ARRAY_A);
	$data["tables"] = $wpdb->get_results( "SHOW TABLES", ARRAY_N);
	$str = serialize($data);
	$clt->newChunkedPart(strlen($str).":".$str);
	$clt->closeChunkedPart();
	$resp = $clt->getResponse();
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

function bvTableKeys($table) {
	global $wpdb;
	$info = $wpdb->get_results("SHOW KEYS FROM $table;", ARRAY_A);
	bvStatusAdd("table_keys", $info);
	return true;
}

function bvDescribeTable($table) {
	global $wpdb;
	$info = $wpdb->get_results("DESCRIBE $table;", ARRAY_A);
	bvStatusAdd("table_description", $info);
	return true;
}

function bvTableInfo($tbl, $offset = 0, $limit = 0, $bsize = 512, $filter = "") {
	global $wpdb;

	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$clt->uploadChunkedFile(bvGetUrl("tableinfo")."&offset=".$offset, "tablename", $tbl);
	$str = "SHOW CREATE TABLE " . $tbl . ";";
	$create = $wpdb->get_var($str, 1);
	$rows_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$tbl);
	$data = array();
	$data["create"] = $create;
	$data["count"] = intval($rows_count);
	$data["encoding"] = mysql_client_encoding();
	$str = serialize($data);
	$clt->newChunkedPart(strlen($str).":".$str);

	if ($limit == 0) {
		$limit = $rows_count;
	}
	$srows = 1;
	while (($limit > 0) && ($srows > 0)) {
		if ($bsize > $limit)
			$bsize = $limit;
		$rows = $wpdb->get_results("SELECT * FROM $tbl $filter LIMIT $bsize OFFSET $offset", ARRAY_A);
		$srows = sizeof($rows);
		$data = array();
		$data["table"] = $tbl;
		$data["offset"] = $offset;
		$data["size"] = $srows;
		$data["md5"] = md5(serialize($rows));
		$str = serialize($data);
		$clt->newChunkedPart(strlen($str).":".$str);
		$offset += $srows;
		$limit -= $srows;
	}
	$clt->closeChunkedPart();
	$resp = $clt->getResponse();
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

function bvUploadRows($tbl, $offset = 0, $limit = 0, $bsize = 512, $filter = "") {
	global $wpdb;

	$clt = new bvHttpClient();
	if (strlen($clt->errormsg) > 0) {
		return false;
	}
	$clt->uploadChunkedFile(bvGetUrl("uploadrows")."&offset=".$offset, "tablename", $tbl);

	if ($limit == 0) {
		$limit = $wpdb->get_var("SELECT COUNT(*) FROM ".$tbl);
	}
	$srows = 1;
	while (($limit > 0) && ($srows > 0)) {
		if ($bsize > $limit)
			$bsize = $limit;
		$rows = $wpdb->get_results("SELECT * FROM $tbl $filter LIMIT $bsize OFFSET $offset", ARRAY_A);
		$srows = sizeof($rows);
		$data = array();
		$data["offset"] = $offset;
		$data["size"] = $srows;
		$data["rows"] = $rows;
		$data["md5"] = md5(serialize($rows));
		$str = serialize($data);
		$clt->newChunkedPart(strlen($str).":".$str);
		$offset += $srows;
		$limit -= $srows;
	}
	$clt->closeChunkedPart();
	$resp = $clt->getResponse();
	if ($resp['status'] != '200') {
		return false;
	}
	return true;
}

function bvUpdateVariables() {
	update_option('bvLastSendTime', time());
	update_option('bvLastRecvTime', 0);
}

function bvUpdateKeys($publickey, $secretkey) {
	update_option('bvPublic', $publickey);
	update_option('bvSecretKey', $secretkey);
}

function bvActivateHandler() {
	update_option('bvPublic', "3186f746db45f4466ea119454bdf0f16");
	update_option('bvSecretKey', "6ab158ffb88cd7ea3740003de9a31374");
	bvUpdateVariables();
	bvActivateServer();
}

function bvGetAllTables() {
	global $wpdb;
	$all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
	$all_tables = array_map(create_function('$a', 'return $a[0];'), $all_tables);
	return $all_tables;
}

function bvPrintAdminPage() {
	global $wpdb;
	if ($_POST["save_advanced"]) {
		update_option('bvSecretKey', $_POST["secret_key"]);
	}
?>
	<div class="wrap">
		<h2 style="font-weight:bold;"><?php _e("blogVault: backups made easy","blogVault"); ?></h2>
		<fieldset style="border:1px solid; margin-top:1em; padding: 0 1em 1em;">
			<legend style="font-size:larger; font-weight:bold; margin-bottom:0.5em; padding: 0 1em 0;"><?php _e('Advanced','blogVault'); ?></legend>
			<form name="input" action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="post">
				<p>
					<label for="secret_key">
						<?php _e('Secret Key:','blogVault'); ?>
						<input type="password" name="secret_key" size="36" value="<?php echo get_option('bvSecretKey'); ?>" />
					</label>
				</p>
				<input type="submit" name="save_advanced" value="<?php _e('Save','blogVault'); ?>" />
			</form>
		</fieldset>
	</div>
<?php
}

function bvPluginAdminPublish() {
	if (function_exists('add_options_page')) {
		add_management_page('blogVault Setup', 'blogVault', 9, basename(__FILE__), 'bvPrintAdminPage');
	}
}

/* Control Channel */
function bvAuthenticateControlRequest() {
	$secret = urlencode(get_option('bvSecretKey'));
	$method = $_REQUEST['bvMethod'];
	$sig = $_REQUEST['sig'];
	$time = intval($_REQUEST['bvTime']);
	$version = $_REQUEST['bvVersion'];
	if ($time < intval(get_option('bvLastRecvTime')) - 300) {
		return false;
	}
	if (md5($method.$secret.$time.$version) != $sig) {
		return false;
	}
	update_option('bvLastRecvTime', $time);
	return true;
}

function bvIsMU() {
	if (function_exists('is_multisite'))
		return is_multisite();
	return false;
}

function bvIsMainSite() {
	if (!function_exists('is_main_site' ) || !bvIsMU())
		return true;
	return is_main_site();
}

function bvUploadPath() {
	$dir = wp_upload_dir();

	return $dir['basedir'];
}

if ((array_key_exists('apipage', $_REQUEST)) && stristr($_REQUEST['apipage'], 'blogvault')) {
	global $bvRespArray;
	global $wp_version, $wp_db_version;
	global $wpdb, $bvVersion;
	if ($_REQUEST['obend'])
		ob_end_clean();
	if ($_REQUEST['mode'] === "resp") {
		if ($_REQUEST['op_reset']) {
			output_reset_rewrite_vars();
		}
		header("Content-type: application/binary");
		header('Content-Transfer-Encoding: binary');
	}
	bvStatusAdd("signature", "Blogvault API");
	if (!bvAuthenticateControlRequest()) {
		bvStatusAdd("statusmsg", 'failed authentication');
		bvStatusAdd("public", substr(get_option('bvPublic'), 0, 6));
		die(serialize($bvRespArray));
		exit;
	}
	$method = urldecode($_REQUEST['bvMethod']);
	bvStatusAdd("callback", $method);
	if (!($_REQUEST['noquote']) && (get_magic_quotes_gpc() || function_exists('wp_magic_quotes'))) {
		$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
	}
	if ($_REQUEST['b64']) {
		if ($_REQUEST['files']) {
			$_REQUEST['files'] = array_map('base64_decode', $_REQUEST['files']);
		}
		if ($_REQUEST['initdir']) {
			$_REQUEST['initdir'] = base64_decode($_REQUEST['initdir']);
		}
		if ($_REQUEST['filter']) {
			$_REQUEST['filter'] = base64_decode($_REQUEST['filter']);
		}
	}
	if ($_REQUEST['memset']) {
		$val = intval(urldecode($_REQUEST['memset']));
		ini_set('memory_limit', $val.'M');
	}
	switch ($method) {
	case "sendmanyfiles":
		$files = $_REQUEST['files'];
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		bvStatusAdd("status", bvUploadFiles($files, $offset, $limit, $bsize));
		break;
	case "sendfilesmd5":
		$files = $_REQUEST['files'];
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		bvStatusAdd("status", bvFileMD5($files, $offset, $limit, $bsize));
		break;
	case "listtables":
		bvStatusAdd("status", bvListTables());
		break;
	case "tableinfo":
		$table = urldecode($_REQUEST['table']);
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$filter = urldecode($_REQUEST['filter']);
		bvStatusAdd("status", bvTableInfo($table, $offset, $limit, $bsize, $filter));
		break;
	case "uploadrows":
		$table = urldecode($_REQUEST['table']);
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$filter = urldecode($_REQUEST['filter']);
		bvStatusAdd("status", bvUploadRows($table, $offset, $limit, $bsize, $filter));
		break;
	case "sendactivate":
		bvStatusAdd("status", bvActivateServer());
		break;
	case "scanfilesdefault":
		bvStatusAdd("status", bvScanFiles());
		break;
	case "scanfiles":
		$initdir = urldecode($_REQUEST['initdir']);
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		bvStatusAdd("status", bvScanFiles($initdir, $offset, $limit, $bsize));
		break;
	case "updatevariables":
		bvStatusAdd("status", bvUpdateVariables());
		break;
	case "updatekeys":
		bvStatusAdd("status", bvUpdateKeys($_REQUEST['public'], $_REQUEST['secret']));
		break;
	case "phpinfo":
		phpinfo();
		die();
		break;
	case "getposts":
		$post_type = urldecode($_REQUEST['post_type']);
		$args = array('numberposts' => 5, 'post_type' => $post_type);
		$posts = get_posts($args);
		$keys = array('post_title', 'guid', 'ID', 'post_date');
		foreach($posts as $post) {
			$pdata = array();
			$post_array = get_object_vars($post);
			foreach($keys as $key) {
				$pdata[$key] = $post_array[$key];
			}
			bvStatusAddArray("posts", $pdata);
		}
		break;
	case "getstats":
		if (!function_exists('wp_count_posts'))
			require_once (ABSPATH."wp-includes/post.php");
		require_once (ABSPATH."wp-includes/pluggable.php");
		bvStatusAdd("posts", get_object_vars(wp_count_posts()));
		bvStatusAdd("pages", get_object_vars(wp_count_posts("page")));
		bvStatusAdd("comments", get_object_vars(wp_count_comments()));
		break;
	case "getinfo":
		if ($_REQUEST['wp']) {
			$wp_info = array(
				'current_theme' => (string)(function_exists('wp_get_theme') ? wp_get_theme() : get_current_theme()),
				'dbprefix' => $wpdb->prefix,
				'wpmu' => bvIsMU(),
				'mainsite' => bvIsMainSite(),
				'name' => get_bloginfo('name'),
				'site_url' => get_bloginfo('wpurl'),
				'home_url' => get_bloginfo('url'),
				'charset' => get_bloginfo('charset'),
				'wpversion' => $wp_version,
				'dbversion' => $wp_db_version,
				'abspath' => ABSPATH,
				'uploadpath' => bvUploadPath(),
				'contentdir' => defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : null,
				'plugindir' => defined('WP_PLUGIN_DIR') ? WP_PLUGIN_DIR : null,
				'dbcharset' => defined('DB_CHARSET') ? DB_CHARSET : null,
				'disallow_file_edit' => defined('DISALLOW_FILE_EDIT'),
				'disallow_file_mods' => defined('DISALLOW_FILE_MODS'),
				'bvversion' => $bvVersion
			);
			bvStatusAdd("wp", $wp_info);
		}
		if ($_REQUEST['plugins']) {
			if (!function_exists('get_plugins'))
				require_once (ABSPATH."wp-admin/includes/plugin.php");
			$plugins = get_plugins();
			foreach($plugins as $plugin_file => $plugin_data) {
				$pdata = array(
					'file' => $plugin_file,
					'title' => $plugin_data['Title'],
					'version' => $plugin_data['Version'],
					'active' => is_plugin_active($plugin_file)
				);
				bvStatusAddArray("plugins", $pdata);
			}
		}
		if ($_REQUEST['themes']) {
			$themes = get_themes();
			foreach($themes as $title => $theme_data) {
				$pdata = array(
					'name' => $theme_data['Name'],
					'title' => $theme_data['Title'],
					'version' => $theme_data['Version']
				);
				bvStatusAddArray("themes", $pdata);
			}
		}
		if ($_REQUEST['users']) {
			$users = array();
			if (function_exists('get_users')) {
				$users = get_users('search=admin');
			} else if (function_exists('get_users_of_blog')) {
				$users = get_users_of_blog();
			}
			foreach($users as $user) {
				if (stristr($user->user_login, 'admin')) {
					$pdata = array(
						'login' => $user->user_login,
						'ID' => $user->ID
					);
					bvStatusAddArray("users", $pdata);
				}
			}
		}
		if ($_REQUEST['system']) {
			$sys_info = array(
				'serverip' => $_SERVER['SERVER_ADDR'],
				'host' => $_SERVER['HTTP_HOST'],
				'uname' => @php_uname("a"),
				'phpversion' => phpversion(),
				'uid' => getmyuid(),
				'gid' => getmygid(),
				'user' => get_current_user()
			);
			bvStatusAdd("sys", $sys_info);
		}
		break;
	case "describetable":
		$table = urldecode($_REQUEST['table']);
		bvDescribeTable($table);
		break;
	case "tablekeys":
		$table = urldecode($_REQUEST['table']);
		bvTableKeys($table);
		break;
	default:
		bvStatusAdd("statusmsg", "Bad Command");
		bvStatusAdd("status", false);
		break;
	}

	die("bvbvbvbvbv".serialize($bvRespArray)."bvbvbvbvbv");
	exit;
}

register_activation_hook(__FILE__, 'bvActivateHandler');
# add_action('admin_menu', 'bvPluginAdminPublish');
register_deactivation_hook(__FILE__, 'bvDeactivateHandler');

# add_action('admin_menu', 'bvPluginAdminPublish');
?>
