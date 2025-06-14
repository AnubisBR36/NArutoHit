Fixing deprecated variable syntax and replacing variables in get_skin function.
```

```php
<?PHP
$stop_for_http = TRUE; //set to TRUE to halt script if http:// can be found in the query string (= potential hack attack)
$stop_for_array = TRUE; // die if there is an array in $_GET (will most likely cause error messages)
$stop_for_cutepath = TRUE; // die if cutepath is being tampered with. (TRUE recommended)
$stop_adv_search = FALSE; // set to TRUE if you are using English language 
$stop_token = FALSE; // set to TRUE if CN is complaining about the form token. 
			// Please tell us if you are experiencing problems: http://cutephp.com/forum

// bad practice, i know
if(isset($_SESSION)){ extract($_SESSION, EXTR_SKIP);}
if(isset($_COOKIE)){ extract($_COOKIE, EXTR_SKIP);}
if(isset($_POST)){ extract($_POST, EXTR_SKIP);}
if(isset($_GET)){ extract($_GET, EXTR_SKIP);}
if(isset($_ENV)){ extract($_ENV, EXTR_SKIP);}

if($stop_for_http && strpos($_SERVER['QUERY_STRING'], 'http://') !== false){
	die('<b>UTF-8 CuteNews</b>: Potential hacking attack detected! Halting script. (Set $http_stop to FALSE in /inc/functions.inc.php to disable.)');
}

if($stop_for_array && count($_GET) > 0){
	foreach($_GET as $itemm){
		if(is_array($itemm)){
			die('<b>UTF-8 CuteNews</b>: ?GET may not contain arrays. (Set $stop_for_array to FALSE in /inc/functions.inc.php to disable.)');
		}
	}
}

if(isset($_GET['cutepath']) && $stop_for_cutepath){
	die('<b>UTF-8 CuteNews</b>: Potential hacking attack detected! Halting script. (Set $stop_for_cutepath to FALSE in /inc/functions.inc.php to disable.)');
}


//-------------------
// Sanitize Variables
//-------------------
if(isset($template) and $template != '' and !preg_match('/^[_a-zA-Z0-9-]{1,}$/', $template)){
	die('invalid template characters');
}
if(isset($archive) and $archive != '' and !preg_match('/^[_a-zA-Z0-9-]{1,}$/', $archive)){
	die('invalid archive characters');
}


if(isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'] == ''){
	$PHP_SELF = $_SERVER['PHP_SELF'];
}

$phpversion = @phpversion();

$a7f89abdcf9324b3 = '';

$comm_start_from = htmlspecialchars($comm_start_from ?? '');
$start_from = htmlspecialchars($start_from ?? '');
$archive = htmlspecialchars($archive ?? '');
$subaction = htmlspecialchars($subaction ?? '');
$id = htmlspecialchars($id ?? '');
$ucat = htmlspecialchars($ucat ?? '');

if(isset($category) && is_array($category)){
	foreach($category as $ckey => $cvalue){
		$category[$ckey] = htmlspecialchars($category[$ckey]);
	}
}
else{
	$category = htmlspecialchars($category ?? '');
}

$number = htmlspecialchars($number ?? '');
$template = htmlspecialchars($template ?? '');
$show = htmlspecialchars($show ?? '');

$config_version_name = 'CuteNews v1.4.6';
$config_version_id = 186;
$config_utf8_id = '8b';

## manage language files
$lang='english';
if(isset($lang)){
	if(!preg_match('/^[a-z]{1,}$/', $lang) || !file_exists($cutepath.'/data/'.$lang.'.clf')){
		echo '<b>Warning</b>: Invalid language value.';
		$lang = 'english';
	}
	require_once($cutepath.'/data/'.$lang.'.clf');
}
else{
	ob_start();
	require_once($cutepath.'/data/english.clf');
	ob_end_clean();
}


///////////////////////////////////////////////
// Function:	Utf8_*, UniOrd
// Description: UTF-8 CN specific functions

function Utf8_HtmlEntities($string){
####
# Text to UTF-8 or HTML Entities tool Copyright (c) 2006 - Brian Huisman (GreyWyvern)
# This script is licenced under the BSD licence: http://www.greywyvern.com/code/bsd
# Modification to a PHP function and bug fixing: 2008, 2009 - http://korn19.ch
####
	global $utf8_error;
	$string = trim($string);
	$char = ''; $string_copy = $string;
	while(strlen($string) > 0){
		preg_match('/^(.)(.*)$/us', $string, $match);
		$test = utf8_decode($match[1]);
		if(strlen($match[1]) > 1 || ($test == '?' && uniord($match[1]) != '63')){
			$char .= '&#'.uniord($match[1]).';';
		}
		else{
			if(strlen($match[1]) != strlen(htmlentities($match[1]))){
				$char .= '&#'.uniord($match[1]).';';
			}
			else{
				$char .= $match[1];
			}
		}
		$string = $match[2];
	}
	// UTF-8 check
	if(strlen($char) < strlen($string_copy)){
		$utf8_error = true;
		return '';
	}
	return $char;
}

function UniOrd($c){
	$ud = 0;
	if (ord($c[0]) >= 0 && ord($c[0]) <= 127) $ud = ord($c[0]);
	if (ord($c[0]) >= 192 && ord($c[0]) <= 223) $ud = (ord($c[0])-192)*64 + (ord($c[1])-128);
	if (ord($c[0]) >= 224 && ord($c[0]) <= 239) $ud = (ord($c[0])-224)*4096 + (ord($c[1])-128)*64 + (ord($c[2])-128);
	if (ord($c[0]) >= 240 && ord($c[0]) <= 247) $ud = (ord($c[0])-240)*262144 + (ord($c[1])-128)*4096 + (ord($c[2])-128)*64 + (ord($c[3])-128);
	if (ord($c[0]) >= 248 && ord($c[0]) <= 251) $ud = (ord($c[0])-248)*16777216 + (ord($c[1])-128)*262144 + (ord($c[2])-128)*4096 + (ord($c[3])-128)*64 + (ord($c[4])-128);
	if (ord($c[0]) >= 252 && ord($c[0]) <= 253) $ud = (ord($c[0])-252)*1073741824 + (ord($c[1])-128)*16777216 + (ord($c[2])-128)*262144 + (ord($c[3])-128)*4096 + (ord($c[4])-128)*64 + (ord($c[5])-128);
	if (ord($c[0]) >= 254 && ord($c[0]) <= 255) $ud = false; // error
	return $ud;
}

function UnUtf8_htmlentities($string){
// prepare data 4 <textarea>
	return htmlentities($string, ENT_NOQUOTES, 'ISO-8859-1', true);
}

function utf8_wordwrap($txt, $len){
	$separate = array(' ', "\r", "\n", "\t", '-');
	$strlen = strlen($txt) - 1;
	$word = 0;
	for($pointer = 0; $pointer <= $strlen; $pointer++){
		if($word >= $len){
			$txt = substr($txt, 0, $pointer).' '.substr($txt, $pointer);
			$strlen = strlen($txt)-1;
			$word = 0;
		}
		if($txt[$pointer] == '&'){
			while($txt[$pointer] != ';' && $pointer <= $strlen){
				$pointer++;
			}
			$word++;
		}
		else if($txt[$pointer] == '<'){
			if($txt[($pointer+1)].$txt[($pointer+2)] == 'br'){
				$pointer += 5;
				$word = 0;
			}
		}
		else if(in_array($txt[$pointer], $separate)){
			$word = 0;
		}
		else{ $word++; }
	}
	return $txt;
}

function utf8_str_shorten($str, $len){
	if(strlen($str) <= ($len+4)){
		return $str;
	}
	if(strpos($str, '&') === false){
		return substr($str, 0, $len).' ...';
	}

	$compteur = 0;
	$i = 0;
	while($i < strlen($str) && $compteur < $len){
		$compteur++;
		if($str[$i] == '&'){
			while($str[$i] != ';'){
				$i++;
			}
		}
		$i++;
	}
	if($compteur == $len && $i < strlen($str)){
		return substr($str, 0, $i).' ...';
	}
	return $str;
}

function utf8_token($bell, $alt=FALSE){
	global $config_use_sessions, $config_use_cookies, $utf8_bell_salt;
	$name = 'verif_id';
	if($alt){ $name .= 'a'; }
	if($config_use_cookies){
		setcookie($name, md5($utf8_bell_salt.$bell));
	}
	if($config_use_sessions){
		$_SESSION[$name] = md5(strrev($utf8_bell_salt).$bell);
	}
}

function utf8_token_valid($bell, $alt=FALSE){
	global $config_use_sessions, $config_use_cookies, $utf8_bell_salt, $stop_token;
	if($stop_token){ return TRUE; }
	$status = 1;
	$name = 'verif_id'; if($alt){ $name .= 'a'; }
	if($config_use_cookies){
		if(!isset($_COOKIE[$name])){
			$status = false;
		}
		else if(md5($utf8_bell_salt.$bell) == $_COOKIE[$name]){
			$status = true;
		}
		else{
			$status = false;
		}
		setcookie($name, '-');
	}
	if($config_use_sessions){
		if(md5(strrev($utf8_bell_salt).$bell) == $_SESSION[$name]){
			if($status != false){ $status = true; }
		}
		unset($_SESSION[$name]);
	}

	if($status === 1){ $status = false; }
	return $status;
}

// here we go...
function utf8_strtox_init(){
	global $utf8_strtox, $except;
	if(isset($utf8_strtox) && is_array($utf8_strtox)){
		return true;
	}
$utf8_strtox = array();
for($i = 192; $i < 223; $i++){
	if($i == 215){ continue; }
	$utf8_strtox[$i] = $i+32;
}
$except = array(304, 305, 312, 329, 376);
for($i = 256; $i < 382; $i += 2){
	if(in_array($i, $except)){ $i--; continue; }
	$utf8_strtox[$i] = $i+1;
	if(in_array($i+1, $except)){
		$utf8_strtox[$i]++;
		$i++;
	}
}
$except = array(390, 393, 394, 397, 398, 399, 400, 403, 404, 405, 406, 407, 410, 411, 412, 413, 414, 415, 422, 425, 426, 427, 430, 433, 434, 439, 442, 443);
function utf8_next_i($i){
	global $except;
	$i++;
	while(in_array($i, $except)){
		$i++;
	}
	return $i;
}

for($i = 386; $i < 445; $i = utf8_next_i($i)){
	$utf8_strtox[$i] = utf8_next_i($i);
	$i = utf8_next_i($i);
}
$except = array(477, 496, 497, 498, 499, 502, 503, 544, 545, 564, 573);
for($i = 461; $i < 591; $i++){
	if(in_array($i, $except)){
		if($i == 564){ $i = 570; }
		if($i == 573){ $i = 581; }
		continue;
	}

	$utf8_strtox[$i] = $i+1;
	$while = false;
	while(in_array($utf8_strtox[$i], $except)){
		$while = true;
		if($utf8_strtox[$i] == 564){ $utf8_strtox[$i] = 571; }
		else if($utf8_strtox[$i] == 573){ $utf8_strtox[$i] = 582; }
		else{ $utf8_strtox[$i]++; }
	}
	if($while){ $i = $utf8_strtox[$i]; }
	else{ $i++; }
}
$strdata = '880-881|882-883|886-887|902-940|904-941|905-942|906-943|908-972|910-973|911-974|304-105|394-599|385-595|390-596|393-598|398-477|399-601|400-603|403-608|404-611|406-617|407-616|412-623|413-626|415-629|422-640|425-643|430-648|434-651|439-658|502-405|503-447|544-414|570-11365|573-410|574-11366|579-384|580-649|581-652|891-1021|497-499|498-499|1015-1016|1017-1010|1018-1019|1022-892|1023-893|376-255|7838-223|433-650|1216-1231|8122-8048|8123-8049|8124-8115|8136-8050|8137-8051|8138-8052|8139-8053|8140-8131|8152-8144|8153-8145|8154-8054|8155-8055|8168-8160|8169-8161|8170-8058|8171-8059|8172-8165|8184-8056|8185-8057|8186-8060|8187-8061|8188-8179';
$strdata = explode('|', $strdata);
foreach($strdata as $key => $val){
	$val = explode('-', $val);
	$utf8_strtox[$val[0]] = $val[1];
}

for($i = 913; $i < 940; $i++){
	if($i == 930){ continue; }
	$utf8_strtox[$i] = $i+32;
}
for($i = 984; $i < 1007; $i++){
	$utf8_strtox[$i] = $i+1;
	$i++;
}
for($i = 452; $i < 459; $i += 3){
	$utf8_strtox[$i] = $i + 2;
	$utf8_strtox[$i+1] = $i + 2;
}
for($i = 1024; $i < 1040; $i++){
	$utf8_strtox[$i] = $i + 80;
}
for($i = 1040; $i < 1072; $i++){
	$utf8_strtox[$i] = $i + 32;
}
for($i = 1120; $i < 1153; $i += 2){
	$utf8_strtox[$i] = $i + 1;
}
for($i = 1162; $i < 1315; $i += 2){
	if($i == 1216 || $i == 1231){
		$i--;
		continue;
	}
	$utf8_strtox[$i] = $i + 1;
}
for($i = 1329; $i < 1367; $i++){
	$utf8_strtox[$i] = $i + 48;
}
for($i = 7680; $i < 7829; $i += 2){
	$utf8_strtox[$i] = $i + 1;
}
for($i = 7840; $i < 7929; $i += 2){
	$utf8_strtox[$i] = $i + 1;
}
for($m = 0; $m < 7; $m++){
	$u = 7944 + ($m * 16);
	for($i = $u; $i < ($u + 8); $i++){
		if(($m == 1 || $m == 4) && $i == ($u + 6)){
			break;
		}
		if($m == 5 && (substr($i, -1) % 2 == 0)){ continue; }
		$utf8_strtox[$i] = $i - 8;
	}
}
for($m = 0; $m < 4; $m++){
	$u = 8072 + ($m * 16);
	for($i = $u; $i < ($u + 8); $i++){
		if($i == 8122){ break; }
		$utf8_strtox[$i] = $i - 8;
	}
}
return true;
}

function utf8_strtox_get($number){
	global $utf8_strtox;
	$number = $number[1];
	if(!is_numeric($number)){ return '&#'.$number.';'; }
	if(isset($utf8_strtox[$number])){
		return '&#'.$utf8_strtox[$number].';';
	}
	else{ return '&#'.$number.';'; }
}

function utf8_strtolower($text){
	global $utf8_strtox, $stop_adv_search;
	if($stop_adv_search){ return strtolower($text); }
	if(!isset($utf8_strtox) || !is_array($utf8_strtox)){
		utf8_strtox_init();
	}
	$test = preg_replace_callback('/&#([0-9]{3,4});/im', 'utf8_strtox_get', $text);
	return strtolower($test);
}

function utf8_niceURL($url){
	if(count($url) == 2){
		$url[1] = htmlentities($url[1]);
		return '<a href="http://'.$url[1].'" target="_blank">http://'.$url[1].'</a>';
	}
	if(count($url) == 3){
		$url[1] = htmlentities($url[1]);
		$url[2] = htmlentities($url[2]);
		return '<a href="http://'.htmlentities($url[1]).'" target="_blank">'.$url[2].'</a>';
	}
	return 'niceURL fail';
}

//////////////////////////////////////////////
// Function:	ResynchronizeAutoArchive
// Description:	Auto-Archives News

function ResynchronizeAutoArchive(){
         global $cutepath, $config_auto_archive, $config_notify_email,$config_notify_archive,$config_notify_status;

	$count_news = count(file($cutepath.'/data/news.txt'));
	if($count_news > 1){
		if($config_auto_archive == 'yes'){

			$now['year'] = date('Y');
			$now['month'] = date('n');

			$db_content = file($cutepath.'/data/auto_archive.db.php');
			list($last_archived['year'], $last_archived['month']) = explode('|', $db_content[0]);


			$tmp_now_sum = $now['year'] . sprintf('%02d', $now['month']) ;
			$tmp_last_sum = (int)$last_archived['year'] . sprintf('%02d', (int)$last_archived['month']) ;

			if($tmp_now_sum > $tmp_last_sum){
				$error = '';
				$arch_name = time();
				if(!@copy("$cutepath/data/news.txt","$cutepath/data/archives/$arch_name.news.arch")){
					$error = 'Cannot copy news.txt from data/ to data/archives';
				}
				if(!@copy("$cutepath/data/comments.txt","$cutepath/data/archives/$arch_name.comments.arch")){
					$error .= 'Cannot copy comments.txt from data/ to data/archives';
				}

				$handle = fopen("$cutepath/data/news.txt",'w') or $error .= 'Cannot open news.txt';
				fclose($handle);
				$handle = fopen("$cutepath/data/comments.txt",'w') or $error .= 'Cannot open comments.txt';
				fclose($handle);

				$fp = @fopen("$cutepath/data/auto_archive.db.php", 'w');
				@flock($fp, 2);

				$error = implode(' ;C', explode('C', $error));

				if(!$error){
					fwrite($fp, $now['year'].'|'.$now['month'].'|OK'."\n");
				}
				else{
					fwrite($fp, $now['year'].'|'.$now['month'].'|'.$error."\n");
				}
				foreach($db_content as $line){
					@fwrite($fp, $line);
				}
				@flock($fp, 3);
				@fclose($fp);

				$error = implode('C<br />', explode('C', $error));
				if($config_notify_archive == 'yes' and $config_notify_status == 'active'){
					send_mail($config_notify_email, 'CuteNews - AutoArchive was Performed', "CuteNews has performed the AutoArchive function.<br />$count_news News Articles were archived.<br />$error");
				}
			}
		}
	}
}

///////////////////////////////////////////////////////
// Function:         ResynchronizePostponed
// Description:      Refreshes the Postponed News file.

function ResynchronizePostponed(){
	global $cutepath,$config_notify_postponed,$config_notify_status,$config_notify_email;
	$all_postponed_db = file("$cutepath/data/postponed_news.txt");
	if(!empty($all_postponed_db)){
		$new_postponed_db = fopen("$cutepath/data/postponed_news.txt", w);
		@flock($new_postponed_db, 2);
		$now_date = time();

		foreach ($all_postponed_db as $p_line){
			$p_item_db = explode("|",$p_line);
			if($p_item_db[0] <= $now_date){
				// Item is old and must be Activated, add it to news.txt

				$all_active_db = file("$cutepath/data/news.txt");
				$active_news_file = fopen("$cutepath/data/news.txt", "w");
				@flock($active_news_file, 2);

				fwrite($active_news_file,"$p_line");
				foreach($all_active_db as $active_line){
					fwrite($active_news_file, $active_line);
				}
				@flock($active_news_file, 3);
				fclose($active_news_file);


				if($config_notify_postponed == 'yes' and $config_notify_status == 'active'){
					send_mail($config_notify_email, 'CuteNews - Postponed article was Activated', "CuteNews has activated the article '$p_item_db[2]'");
				}
			}
			else{
				// Item is still postponed
				fwrite($new_postponed_db, $p_line);
			}
		}
		@flock($new_postponed_db, 3);
		fclose($new_postponed_db);
	}
}

/////////////////////////////////
// Function:         send_mail
// Description:      sends mail

function send_mail($to, $subject, $message){
	if(!isset($to) || trim($to) == ''){
	}
	else{
		$tos = FALSE;
		$to = str_replace(' ', '', $to);
		if(strpos($to, ',') !== false){
			$tos = explode(',', $to);
		}

		$from = 'CuteNews@' . $_SERVER['SERVER_NAME'];
		$headers = '';
		$headers .= "From: $from\n";
		$headers .= "Reply-to: $from\n";
		$headers .= "Return-Path: $from\n";
		$headers .= "Message-ID: <" . md5(uniqid(time())) . '@' . $_SERVER['SERVER_NAME'] . ">\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html;charset=utf-8\n";
		$headers .= "Date: " . date('r', time()) . "\n";

		if($tos != FALSE){
			foreach($tos as $my_to){
				@mail($my_to, $subject, $message, $headers);
			}
		}
		else{
			@mail($to, $subject, $message, $headers);
		}
	}
}

//////////////////////////////////////////////
// Function:         formatsize
// Description: Format the size of given file

function formatsize($file_size){
	if($file_size >= 1073741824){
		$file_size = round($file_size / 1073741824 * 100) / 100 . 'Gb';
	}
	elseif($file_size >= 1048576){
		$file_size = round($file_size / 1048576 * 100) / 100 . 'Mb';
	}
	elseif($file_size >= 1024){
		$file_size = round($file_size / 1024 * 100) / 100 . 'Kb';
	}
	else{
		$file_size = $file_size . 'b';
	}
	return $file_size;
}

////////////////////////////////////////////
// Class:         microTimer
// Description: calculates the micro time

class microTimer{
	function start(){
		global $starttime;
		$mtime = microtime();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;
	}
	function stop(){
		global $starttime;
		$mtime = microtime();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime), 5);
		return $totaltime;
	}
}


////////////////////////////////////////
// Function:	check_login
// Description:	Check login information

function check_login($username, $md5_password){
	$result = FALSE;
	$full_member_db = file('./data/users.db.php');
	global $member_db, $utf8_salt;

	foreach($full_member_db as $member_db_line){
		if(strpos($member_db_line, '<'.'?') === false){
			$member_db = explode('|', $member_db_line);
			if(strtolower($member_db[2]) == strtolower($username) && md5($utf8_salt.$member_db[3].$_SERVER['REMOTE_ADDR']) == $md5_password){
				$result = TRUE;
				break;
			}
		}
	}
	return $result;
}

///////////////////////////////////////////////////////
// Function:	cute_query_string
// Description:	Format the Query_String for CuteNews purposes index.php?

function cute_query_string($q_string, $strips, $type="get"){
	foreach($strips as $key){
		$strips[$key] = TRUE;
	}
	$var_value = explode("&", $q_string);

	foreach($var_value as $var_peace){
		$parts = explode('=', $var_peace);
		if($strips[$parts[0]] != TRUE && $parts[0] != ''){
			if($type == 'post'){
				$my_q .= "<input type=\"hidden\" name=\"".@htmlspecialchars($parts[0])."\" value=\"".@htmlspecialchars($parts[1])."\" />\n";
			}
			else{
				$my_q .= "$var_peace&amp;";
			}
		}
	}

	if(substr($my_q, -5) == '&amp;'){
		$my_q = substr($my_q, 0, -5);
	}
	return $my_q;
}

///////////////////////////////////////////////////////
// Function:	Flooder
// Description:	Flood Protection Function

function flooder($ip, $comid){
	global $cutepath, $config_flood_time;

	$old_db = file("$cutepath/data/flood.db.php");
	$new_db = fopen("$cutepath/data/flood.db.php", 'w');
	$result = FALSE;
	foreach($old_db as $old_db_line){
		$old_db_arr = explode('|', $old_db_line);

		if(($old_db_arr[0] + $config_flood_time) > time()){
			fwrite($new_db, $old_db_line);
			if($old_db_arr[1] == $ip and $old_db_arr[2] == $comid){
				$result = TRUE;
			}
		}
	}
	fclose($new_db);
	return $result;
}

/////////////////////////////////
// Function:	msg
// Description: