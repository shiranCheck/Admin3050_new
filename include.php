<?php
	$encoding='UTF-8';
	define('SESSION_COOKIE_NAME','PHPSESSID_ADMIN');
	define('SHAREDLIB_PATH',get_cfg_var("icewarp_sharedlib_path"));
	ini_set('session.name',SESSION_COOKIE_NAME);
	session_start();
	define('USEAPIEXT',true);
	define('CRLF',"\r\n");
	require_once('function.php');
	require_once('accounts.php');
	require_once('domains.php');
	
	if(USEAPIEXT){
		require_once('api/api.php');
		require_once('api/domain.php');
		require_once('api/account.php');
		require_once('api/remoteaccount.php');
		require_once('api/statistics.php');
		require_once('api/schedule.php');
		require_once('api/gw.php');
	}

	$api=createobject("API");
	$osversion=(int)$api->GetProperty("C_OS");
	define('osversion',$osversion==0?"WINDOWS":
	"LINUX");
	$maxacc=$api->GetProperty("C_Accounts_Global_Console_ShowAccounts");
	define("maxaccountlist",($maxacc-1));
	$lang_settings=array();
	$layout_settings=array();
	include('layouts.html');
	include('languages.html');
	$langpath=$lang_settings[$language][1];
	securepath($lang_settings[$language][1]);
	
	if(!$encoding)$encoding=get_cfg_var('default_charset');
	$_COOKIE['LANGID']=$lang_settings[$language][2];
	require_once($langpath."alang.html");
	$alang["ACCESS_ERROR"]=$alang['TStrings_ad_gv_versioninfo'];
	$pom=$alang["TStatisticsForm_ChallengeList"];
	$pom=explode("|",$pom);
	unset($pom[1]);
	$pom=implode("|",$pom);
	$alang["TStatisticsForm_ChallengeList2"]=$pom;
	$alang["TStrings_ErrorResponseTypeL"]=$alang["TStrings_weberrorsourcedefault"]."|".$alang["TStrings_weberrorsourcefile"]."|".$alang["TStrings_weberrorsourceurl"];
	$alang["TContentFilterItemForm_Labels"]="&nbsp;|".$alang["TFrameAccounts_FilterBox"];
	$alang["TRulesItemForm_Labels"]="&nbsp;|&nbsp;|".$alang["TBWForm_FilterList"];
	$alang["TContentBool"]=$alang['TStrings_hland'].'/'.$alang['TStrings_hlor'];
	$pom=explode("|",$alang["TContentEditHeader_ActionEdit"]);
	$alang["TContentEditHeader_ActionEdit0"]=$pom[0];
	$alang["TContentEditHeader_ActionEdit1"]=$pom[1];
	$alang['TPushFormFolder_FolderTypes']="Contact|Event|Folder|Journal|Mail|Note|Taks";
	$skin_dir=$layout_settings[strtoupper($layout)][1];
	$days=array(0=>"su","mo","tu","we","th","fr","sa");
	$dayslang=array(0=>"TAntivirusSettingForm_Su","TAntivirusSettingForm_Mo","TAntivirusSettingForm_Tu","TAntivirusSettingForm_We","TAntivirusSettingForm_Th","TAntivirusSettingForm_Fri","TAntivirusSettingForm_Sa");
	$alang['FORM_FILTERLIST']=$alang['TFrameAccounts_SMSAllRadio'].'|'.$alang['TFrameAccounts_SMSFilterRadio'];
	$global['backpath']="";
	header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
	
	if(@!$noheadercache){
		
		if($SERVER_PROTOCOL=="HTTP/1.0")$nocache="Pragma: no-cache"; else $nocache="Cache-Control: no-store, no-cache, must-revalidate";
		header($nocache);
	}

	
	if(@$CHARSET)$meta='<meta http-equiv="content-type" content="text/html; charset='.@$CHARSET.'">';
	function dmp($var){
		print_r($var);
	}

	?>