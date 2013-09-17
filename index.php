<?
	$_ERRORS = Array();
	
	$str_password = "blank";
	$arr_default_values = Array(
									"titel"			=>	"Terminfinder by eMeidi.com",
									"zeitraum-sel"	=>	"checked",
									"ausnahmen"		=>	"",//"Sonntag",
									"zeitraum"		=>	date("d.m.Y"),
									"daten"			=>	"",//date("d.m.Y"),
									"tageszeit"		=>	"ganzer Tag", 
									"teilnehmer"	=>	"Person 1"
								);
	
	$arr_weekdays = Array(
								"Sonntag" 	=>	"Sunday",
								"Montag"	=>	"Monday",
								"Dienstag"	=>	"Tuesday",
								"Mittwoch"	=>	"Wednesday",
								"Donnerstag"	=>	"Thursday",
								"Freitag"	=>	"Friday",
								"Samstag"	=>	"Saturday",
								
								"Dimanche"	=>	"Sunday",
								"Lundi"		=>	"Monday",
								"Mardi"		=>	"Tuesday",
								"Mercredi"	=>	"Wednesday",
								"Jeudi"		=>	"Thursday",
								"Vendredi"	=>	"Friday",
								"Samedi"	=>	"Saturday",
								
								"Domenica"	=>	"Sunday",
								"Lunedi"	=>	"Monday",								
								"Martedi"	=>	"Tuesday",
								"Mercoledi"	=>	"Wednesday",
								"Giovedi"	=>	"Thursday",
								"Venerdi"	=>	"Friday",
								"Sabato"	=>	"Saturday"
								);
	
	$str_settings_file = dirname(__FILE__) . "/" . "settings.txt";
	$str_dump_file = dirname(__FILE__) . "/" . "dump.txt";
	
	function terminfinder__make_chain($str_form_element_name) {
		global $arr_default_values;
		global $_SETTINGS;
		
		$str_output = NULL;
		
		if(isset($_SETTINGS[$str_form_element_name]) && is_array($_SETTINGS[$str_form_element_name])) {
			$arr_values = $_SETTINGS[$str_form_element_name];
		}
		
		if(!isset($arr_values))
			$arr_values = Array();
		
		if(!is_array($arr_values))
			$arr_values = Array();
		
		if(count($arr_values) < 1) {
			$arr_values = Array();
			
			if(isset($arr_default_values[$str_form_element_name]))
				$arr_values[] = $arr_default_values[$str_form_element_name];
		}
		
		if(isset($_POST["ctrlAdd"][$str_form_element_name])) {
			if(isset($arr_default_values[$str_form_element_name]))
				$arr_values[] = $arr_default_values[$str_form_element_name];
		}
		
		$int_counter = 0;
		foreach($arr_values as $str_value) {
			$str_output .= "<input type=\"text\" name=\"" . $str_form_element_name . "[]\" value=\"" . htmlentities($str_value) . "\">";
			
			if(count($arr_values) > 1)
				$str_output .= "<input type=\"submit\" name=\"ctrlRemove[" . $str_form_element_name . "][" . $int_counter . "]\" value=\"-\">";
			
			$str_output .= "<input type=\"submit\" name=\"ctrlAdd[" . $str_form_element_name . "][" . $int_counter . "]\" value=\"+\">";
			$str_output .= "<br>";
			
			$int_counter += 1;
		}
		
		return $str_output;
	}
	
	function terminfinder__get_form_val($arr_field_props) {
		global $_SETTINGS;
		
		$str_output = NULL;
		
		if(!is_array($arr_field_props))
			return $str_output;
		
		if(count($arr_field_props) > 1)
			return $str_output;
		
		foreach($arr_field_props as $int_key=>$str_field_name)
			echo NULL;
		
		if(!isset($_SETTINGS[$str_field_name]))
			return $str_output;
		
		if(is_array($_SETTINGS[$str_field_name])) {
			
			if(isset($_SETTINGS[$str_field_name][$int_key]))
				$str_output = $_SETTINGS[$str_field_name][$int_key];
		}
		else {
			$str_output = $_SETTINGS[$str_field_name];
		}	
		
		return $str_output;
	}
	
	function terminfinder__store() {
		$bol_output = FALSE;
		
		if(!isset($_POST))
			return $bol_output;
		
		if(!is_array($_POST))
			return $bol_output;
		
		if(isset($_POST["formSettings"]))
			$bol_output = terminfinder__store_settings();
		elseif(isset($_POST["formCalendar"]))
			$bol_output = terminfinder__store_calendar();
		
		return $bol_output;
	}
	
	function terminfinder__store_settings() {
		global $_ERRORS;
		global $str_password;
		global $str_settings_file;
		global $str_dump_file;
		
		$bol_output = FALSE;
		
		if(!isset($_POST))
			return $bol_output;
		
		if(!is_array($_POST))
			return $bol_output;
		
		if(!isset($_POST['ctrlPasswort']))
			return $bol_output;
		
		if($_POST['ctrlPasswort'] != $str_password) {
			$_ERRORS['store_settings'] = "Sie haben ein falsches Passwort angegeben. Die Änderungen wurden nicht gespeichtert.";
			return $bol_output;
		}
		
		$arr_form_values = $_POST;
		foreach($arr_form_values as $str_key=>$unk_value) {
			$bol_unset = FALSE;
			
			if(eregi("^ctrl",$str_key)) {
				$bol_unset = TRUE;
				
				$str_ctrl_arg = "ctrlRemove";
				if($str_key == $str_ctrl_arg) {
					$arr_remove = $_POST[$str_ctrl_arg];
					
					foreach($arr_remove as $str_field_name=>$arr_key)
						echo NULL;
					
					foreach($arr_key as $int_key=>$str_grbg)
						echo NULL;
					
					if(isset($arr_form_values[$str_field_name][$int_key]))
						unset($arr_form_values[$str_field_name][$int_key]);
				}
			}
			
			if(eregi("^form",$str_key))
				$bol_unset = TRUE;
			
			if($bol_unset)
				unset($arr_form_values[$str_key]);
		}
		
		$bol_success = terminfinder__write_data($arr_form_values,$str_settings_file);
		$bol_success = terminfinder__write_data(Array(),$str_dump_file);
		
		return $bol_output;
	}
	
	function terminfinder__write_data($arr_data,$str_file) {
		$bol_output = FALSE;
		
		$str_data_raw = serialize($arr_data);
		
		$ptr_file = fopen($str_file,"w");
		
		if($ptr_file === FALSE)
			die("Could not open '" . $str_file . "' for writing");
		
		$int_bytes_written = fwrite($ptr_file,$str_data_raw);
		
		if($int_bytes_written === FALSE)
			die("Could not write to '" . $str_dump_file . "'");
		
		$bol_output = TRUE;
		
		return $bol_output;
	}
	
	function terminfinder__get_data($str_file) {
		$arr_output = Array();
		
		if(!is_file($str_file))
			die("'" . $str_file . "' not found");
		
		$str_data_raw = implode("",file($str_file));
		
		if(strlen($str_data_raw) > 0) {
			$arr_output = unserialize($str_data_raw);
		}
		
		if(!is_array($arr_output))
			die("Unserialized string is not an array");
		
		return $arr_output;
	}
	
	function terminfinder__get_days() {
		global $_SETTINGS;
		
		$arr_output = Array();
		
		if(!isset($_SETTINGS["is_range"]))
			return $arr_output;
		
		if($_SETTINGS["is_range"] == "true")
			$arr_output = terminfinder__get_days_range();
		else
			$arr_output = terminfinder__get_days_dates();
			
		return $arr_output;		
	}
	
	function terminfinder__get_days_range() {
		global $_ERRORS;
		global $_SETTINGS;
		global $arr_weekdays;
		
		$arr_output = Array();
		
		$arr_date[0] = $_SETTINGS["zeitraum"][0];
		$arr_date[1] = $_SETTINGS["zeitraum"][1];
		
		$str_regex_pattern = "/^([0-9]{1,})\.([0-9]{1,})\.([0-9]{4})$/";
		
		$arr_vars = Array("str_date_start","str_date_end");
		foreach($arr_date as $int_key=>$str_date) {
			$arr_matches = Array();
			preg_match($str_regex_pattern,$str_date,$arr_matches);
			
			if(count($arr_matches) != 4) {
				$_ERRORS['get_days_range'] = "Die Daten sind falsch formatiert. Bitte benutzen Sie zwingend vierstellige Jahreszahlen.";
				return $arr_output;
			}
			
			$arr_date[$int_key] = $arr_matches[3] . "-" . sprintf("%02d",$arr_matches[2]) . "-" . sprintf("%02d",$arr_matches[1]);
		}
		
		$utm_start = strtotime($arr_date[0]);
		$utm_end = strtotime($arr_date[1]);
		
		$int_output = $utm_end - $utm_start;
		if($int_output <= 0)
			return $arr_output;
		
		$arr_hide = $_SETTINGS["ausnahmen"];
		
		foreach($arr_hide as $int_key=>$str_var) {
			if(!isset($arr_weekdays[$str_var]))
				continue;
			
			$arr_hide[$int_key] = $arr_weekdays[$str_var];
		}
		
		$utm_tmp = $utm_start;
		while($utm_tmp <= $utm_end) {
			if(!in_array(date("l",$utm_tmp),$arr_hide) && !in_array(date("d.m.Y",$utm_tmp),$arr_hide) && !in_array(date("j.n.Y",$utm_tmp),$arr_hide))
				$arr_output[] = $utm_tmp;
			
			$utm_tmp += (24*60*60);
		}
		
		return $arr_output;
	}
	
	function terminfinder__get_days_dates() {
		global $_ERRORS;
		global $_SETTINGS;
		
		$arr_output = Array();
		
		if(!isset($_SETTINGS["daten"]))
			return $arr_output;
		
		if(!is_array($_SETTINGS["daten"]))
			return $arr_output;
		
		if(count($_SETTINGS["daten"]) < 1)
			return $arr_output;
		
		$str_regex_pattern = "/^([0-9]{1,})\.([0-9]{1,})\.([0-9]{4})$/";
		
		$arr_tmp = Array();
		foreach($_SETTINGS["daten"] as $str_date) {
			$arr_matches = Array();
			preg_match($str_regex_pattern,$str_date,$arr_matches);
			
			if(count($arr_matches) != 4) {
				$_ERRORS['get_days_dates'] = "Die Daten sind falsch formatiert. Bitte benutzen Sie zwingend vierstellige Jahreszahlen.";
				return $arr_output;
			}
			
			$arr_tmp[] = $arr_matches[3] . "-" . sprintf("%02d",$arr_matches[2]) . "-" . sprintf("%02d",$arr_matches[1]);
		}
		
		foreach($arr_tmp as $str_date)
			$arr_output[] = strtotime($str_date);
		
		return $arr_output;
	}
	
	function terminfinder__make_cal() {
		global $_SETTINGS;
		global $str_dump_file;
		global $str_settings_file;
		
		$str_output = NULL;
		
		$bol_proceed = TRUE;
		$arr_fields_req = Array('teilnehmer','tageszeit','titel');
		foreach($arr_fields_req as $str_field) {
			if(!isset($_SETTINGS[$str_field]))
				$bol_proceed = FALSE;
		}
		
		if(!$bol_proceed) {
			$str_output .= "<h1>Installation</h1>";
			$str_output .= "<p>Es fehlen einige Einstellungen. Bitte konfigurieren Sie TerminGenius! mit Hilfe des Wizards.</p>";
			$str_output .= "<p><a href=\"javascript:showHideSettings();\">TerminGenius! konfigurieren</a></p>";
			
			$str_output .= "<h2>Passwort</h2>";
			$str_output .= "<p>Das Initialpasswort lautet 'blank' und kann aus Sicherheitsgründen nur von Hand in der Datei <tt>index.php</tt> verändert werden.</p>";
			
			$str_output .= "<h2>System-Check</h2>";
			
			$arr_files = Array($str_dump_file,$str_settings_file);
			foreach($arr_files as $str_path) {
				$str_output .= "<p><tt>" . htmlentities($str_path) . "</tt></p>";
				
				if(!is_file($str_path)) {
					$str_output .= "<ul><li><p class=\"pError\"><b>Fehler:</b> Datei nicht gefunden.</p></li></ul>";
					continue;
				}
				
				if(!is_writable($str_path)) {
					$str_output .= "<ul><li><p class=\"pError\"><b>Fehler:</b> Datei ist schreibgeschützt.</p></li></ul>";
					continue;
				}
				
				$str_output .= "<ul><li><p class=\"pSuccess\"><b>Erfolg:</b> Datei vorhanden und beschreibbar.</p></li></ul>";
			}
			
			return $str_output;
		}
		
		$arr_persons = $_SETTINGS["teilnehmer"];
		$int_num_parts_per_day = count($_SETTINGS["tageszeit"]);
		
		$arr_days = terminfinder__get_days();
		$arr_dump_data = terminfinder__get_data($str_dump_file);
		
		$int_num_days = count($arr_days);
		$int_num_items = $int_num_days * $int_num_parts_per_day;
		
		$str_output .= "<h1>" . htmlentities($_SETTINGS["titel"]) ."</h1>";
		$str_output .= "<p>" . nl2br(htmlentities($_SETTINGS["kommentar"])) . "</p>";
		$str_output .= "<p><a href=\"javascript:showHideSettings();\">Einstellungen</a></p>";
		$str_output .= "<table cellpadding=\"4\" cellspacing=\"0\" border=\"1\">" . "\n";
		
		$str_output .= "<tr>" . "\n";
		$str_output .= "\t<td>&nbsp;</td>" . "\n";
		$str_output .= "\t<td>&nbsp;</td>" . "\n";
		foreach($arr_days as $utm_day)
			$str_output .= "\t<td>" . date("D d.m.",$utm_day) . "</td>" . "\n";
		$str_output .= "\t<td>&nbsp;</td>" . "\n";
		$str_output .= "</tr>" . "\n";
		
		$arr_top = Array();
		foreach($arr_persons as $int_id=>$str_person) {
			$str_output .= "<tr>";
			
			$str_output .= "<form action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"POST\">" . "\n";
			$str_output .= "<input type=\"hidden\" name=\"formCalendar\" value=\"true\">" . "\n";
			$str_output .= "<input type=\"hidden\" name=\"personal-id\" value=\"" . $int_id . "\">" . "\n";
			
			$str_output .= "\t<td>" . htmlentities($str_person) . "</td>" . "\n";
			
			$str_output .= "\t<td>";
			$str_output .= "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"100%\" class=\"tblTageszeit\">" . "\n";
			
			foreach($_SETTINGS["tageszeit"] as $str_tagsezeit) {
				$str_output .= "<tr>";				
				$str_output .= "<td nowrap>" . htmlentities($str_tagsezeit) . "&nbsp;&raquo;" . "</td>";				
				$str_output .= "</tr>";
				
			}
			$str_output .= "</td>";
			$str_output .= "</table>";
			
			$int_num_day = 0;
			$int_num_items = 0;
			foreach($arr_days as $utm_day) {
				$int_num_day++;
				
				if(!isset($arr_top[$utm_day]))
					$arr_top[$utm_day] = 0;
						
				$str_output .= "\t<td>" . "\n";
				
				$str_output .= "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"100%\" class=\"tblTageszeit\">" . "\n";
				
				for($i = 1; $i <= $int_num_parts_per_day; $i++) {
					$int_num_items++;
					
					if(isset($arr_dump_data[$int_id][$int_num_items])) {
						$str_bgcolor = "#009933";
						$str_checked = " checked";
						
						$arr_top[$utm_day] += 1;
					}
					else {
						$str_bgcolor = "#FF0000";
						$str_checked = NULL;
					}
					
					$str_output .= "<tr align=\"center\" valign=\"top\"><td bgcolor=\"" . $str_bgcolor . "\">" . "<input type=\"checkbox\" name=\"date-id[" . $int_num_items . "]\" value=\"" . $int_num_items . "\"" . $str_checked . ">" . "</td></tr>" . "\n";
				}
				
				$str_output .= "</table>" . "\n";
				
				$str_output .= "</td>" . "\n";
			}
			
			$str_output .= "\t<td><input type=\"submit\" value=\"speichern\"></td>" . "\n";
			
			$str_output .= "</form>" . "\n";
			$str_output .= "</tr>" . "\n";
		}
		
		$str_output .= "<tr>";
		$str_output .= "<td>Favorit</td><td></td>";
		
		foreach($arr_days as $utm_day) {
			$str_output .= "<td>" . $arr_top[$utm_day] . "</td>";
		}
		
		$str_output .= "<td></td>";
		$str_output .= "</tr>";
		
		$str_output .= "</table>" . "\n";
		
		return $str_output;
	}
	
	function terminfinder__store_calendar() {
		global $str_dump_file;
		global $_SETTINGS;
		
		if(!isset($_POST["personal-id"]))
			die("personal-id not submitted");
		
		$int_personal_id = intval($_POST["personal-id"]);
		
		if(!isset($_SETTINGS["teilnehmer"][$int_personal_id]))
			die("personal-id not found");
		
		if(!isset($_POST["date-id"]))
			$_POST["date-id"] = Array();
		
		if(!is_array($_POST["date-id"]))
			die("date-id is no valid array");
		
		$arr_dump_data = terminfinder__get_data($str_dump_file);
		
		$arr_dump_data[$int_personal_id] = Array();
				
		foreach($_POST["date-id"] as $int_date_id=>$str_grb) {
			$int_date_id = intval($int_date_id);
			$arr_dump_data[$int_personal_id][$int_date_id] = 1;
		}
		
		$bol_success = terminfinder__write_data($arr_dump_data,$str_dump_file);
		
		if(!$bol_success)
			die("Write dump failed");
	}
	
	function terminfinder__get_range() {
		global $_SETTINGS;
		
		$bol_output = TRUE;
		
		if(isset($_GET["range"]))
			$str_selector = $_GET["range"];
		elseif(isset($_SETTINGS["is_range"]))
			$str_selector = $_SETTINGS["is_range"];
		else
			return $bol_output;
		
		switch($str_selector) {
			case "true":
				$bol_output = TRUE;
				break;
			case "false":
				$bol_output = FALSE;
				break;
			default:
				$bol_output = TRUE;
				break;
		}
		
		return $bol_output;
	}
	
	function terminfinder__debug() {
		global $_ERRORS;
		
		$str_output = NULL;
		
		if(!is_array($_ERRORS))
			return $str_output;
		
		if(count($_ERRORS) < 1)
			return $str_output;
		
		$str_output .= "<div id=\"debug\">";
		$str_output .= "<p><b>Fehler</b></p>";
		$str_output .= "<ul>";
		foreach($_ERRORS as $str_func=>$str_err_msg) {
			$str_output .= "<li>[" . htmlentities($str_func) . "] " . htmlentities($str_err_msg) . "</li>";
		}
		$str_output .= "</ul>";
		$str_output .= "</div>";
		
		return $str_output;
	}
	
	$_SETTINGS = terminfinder__get_data($str_settings_file);
	
	if(isset($_POST) && count($_POST) > 0)
		terminfinder__store();
	
	$_SETTINGS = terminfinder__get_data($str_settings_file);
	
	$bol_is_range = terminfinder__get_range();
	
	$arr_range_nav_classes = Array("active","inactive");
	if(!$bol_is_range)
		$arr_range_nav_classes = array_reverse($arr_range_nav_classes);
	
	if(!isset($_POST["ctrlPasswort"]))
		$_POST["ctrlPasswort"] = NULL;
	
	$str_output_cal = terminfinder__make_cal();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>TerminGenius!</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="DE">

<style type="text/css">

body, p, td					{font-family:Verdana,Tahoma,Helvetica,Arial,sans serif;font-size:12px;}

#divSettings				{visibility:hidden;position:absolute;right:0px;top:0px;width:300px;border:2px #AAAAAA solid;padding:0;margin:24px;z-index:99;background-color:#FFFFFF;}
#divSettings h1,h2,h3		{padding:4px;}
#divSettings h1				{margin-top:0px;background-color:#DDDDDD;}
#divSettings h2				{border-bottom:1px solid #AAAAAA;}

#divZeitraum				{position:relative;height:200px;}

#divZeitraumNav				{padding:0px 0px 0px 16px;z-index:2;}
#divZeitraumNav p			{margin:0;}
#divZeitraumNav a			{padding:4px;margin:0px 8px 0px 0px;background-color:#EEEEEE;vertical-align:bottom;text-decoration:none;}
#divZeitraumNav a.active	{border-left:1px #000000 solid;border-top:1px #000000 solid;border-right:1px #000000 solid;color:#000000;}
#divZeitraumNav a.inactive	{border-left:1px #DDDDDD solid;border-top:1px #DDDDDD solid;border-right:1px #DDDDDD solid;border-bottom:1px #000000 solid;color:#AAAAAA;}

.divFormElement				{padding:8px;}
.divTabs					{background-color:#EEEEEE;margin:3px 8px 0px 8px;padding:8px;border:1px solid #000000;}

.tblTageszeit tr td			{text-align:right;height:20px;}

.pError						{color:red;}
.pSuccess					{color:green;}

#debug						{border:2px solid #FF0000;padding:8px;color:#FF0000;width:400px;}

</style>
<script type="text/javascript">
function showHideSettings() {
	objTarget = document.getElementById("divSettings");
	
	if(!objTarget)
		return;
	
	if(objTarget.style.visibility == "")
		objTarget.style.visibility = "hidden";
	
	arrVis = Array();
	arrVis['visible'] = "hidden";
	arrVis['hidden'] = "visible";
	
	strVis = objTarget.style.visibility;
	objTarget.style.visibility = arrVis[strVis];
	
	return;
}

function checkPassword() {
	objTarget = document.getElementById('ctrlPasswort');
	
	if(objTarget.value == '') {
		alert('Bitte geben Sie das Passwort ein.');
		return false;
	}
	
	return true;
}
</script>
</head>
<body>

<div id="divSettings">
<form action="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST" onsubmit="return checkPassword();">
<input type="hidden" name="formSettings" value="true">

<h1>Einstellungen</h1>

<div class="divFormElement"><a href="javascript:showHideSettings();">ausblenden</a></div>

<h2>Passwort</h2>
<div class="divFormElement">
<input type="password" id="ctrlPasswort" name="ctrlPasswort" value="<? echo $_POST['ctrlPasswort']; ?>">
</div>

<h2>Titel / Kommentar</h2>
<div class="divFormElement"><input type="text" name="titel" value="<? echo terminfinder__get_form_val(Array("titel")); ?>"><br><textarea name="kommentar" rows="5" cols="30"><? echo htmlentities(terminfinder__get_form_val(Array("kommentar"))); ?></textarea></div>

<h2>Zeitraum</h2>

	<div id="divZeitraumNav"><p><a href="<? echo $_SERVER["PHP_SELF"]; ?>?range=true" class="<? echo $arr_range_nav_classes[0]; ?>">Zeitraum</a><a href="<? echo $_SERVER["PHP_SELF"]; ?>?range=false" class="<? echo $arr_range_nav_classes[1]; ?>">Einzelne Daten</a></p></div>
	
<div class="divTabs">
<? if($bol_is_range) { ?>
		<input type="text" name="zeitraum[]" value="<? echo terminfinder__get_form_val(Array(0 => "zeitraum")); ?>">&nbsp;bis<br>
		<input type="text" name="zeitraum[]" value="<? echo terminfinder__get_form_val(Array(1 => "zeitraum")); ?>">
		
		<h3>Ausnahmen</h3>
		<p>Bsp. <i>Dienstag</i> (Tagesnamen) oder <i><? echo date("d.m.Y"); ?></i> (Datum)</p>
		<? print(terminfinder__make_chain("ausnahmen")); ?>

		<input type="hidden" name="daten[]" value="">
		<input type="hidden" name="is_range" value="true">
<? } else { ?>
		<? print(terminfinder__make_chain("daten")); ?>
		
		<input type="hidden" name="zeitraum[]" value="">
		<input type="hidden" name="zeitraum[]" value="">
		<input type="hidden" name="ausnahmen[]" value="">
		<input type="hidden" name="is_range" value="false">
<? } ?>
</div>

<h2>Tageszeit</h2>
<div class="divFormElement"><? print(terminfinder__make_chain("tageszeit")); ?></div>

<h2>Teilnehmer</h2>
<div class="divFormElement"><? print(terminfinder__make_chain("teilnehmer")); ?></div>

<div class="divFormElement"><input type="submit" value="speichern"></div>

</form>
</div>

<? print(terminfinder__debug()); ?>

<? print($str_output_cal); ?>

<?
	if(isset($_POST["formSettings"]) || isset($_GET["range"]))
		print("<script type=\"text/javascript\">showHideSettings();</script>");
?>

<p><small>OSS by <a href="http://www.eMeidi.com/">eMeidi.com</a></small></p>

</body>
</html>