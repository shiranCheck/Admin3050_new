<?php 
	require_once('include.php');
	require_once('inc/clsparsexml.php');
	checksession();
	function getLabel($wizard_id){
		$parser=&new ParseXML;
		
		if(!file_exists('wizards/lang/'.strtolower($_COOKIE['LANGID']).'/lang.xml'))$langid="en"; else $langid=$_COOKIE['LANGID']?strtolower($_COOKIE['LANGID']):
		"en";
		$langXML=$parser->GetXMLTree('wizards/lang/'.$langid.'/lang.xml');
		$parser=NULL;
		foreach($langXML['LANGUAGES'][0]['LANGUAGE']as$lang_key=>$lang_val)
		if($lang_val['ATTRIBUTES']['ID']==$wizard_id)$label=$lang_val['ATTRIBUTES']['VALUE'];
		return$label;
	}

	
	function getwizards(){
		global$alang,$skin_dir;
		$ret='';
		$parser=new ParseXML();
		$WXML=$parser->GetXMLTree('wizards/wizards.xml');
		foreach($WXML['ROOT'][0]['WIZARDS'][0]['WIZARD']as$key=>$val){
			$disable=$val["ATTRIBUTES"]["DISABLE"];
			switch($val["ATTRIBUTES"]['ID']){
				case'accountwizard':
					
					if(isuserright("U")||$_SESSION['ACCOUNT']=='ADMIN')
					if($_SESSION["ACCOUNT"]!=$disable)$ret[]=$val;
					break;
				case'domainwizard':
					
					if($_SESSION["ACCOUNT"]!=$disable)$ret[]=$val;
					break;
		}

	}

	return$ret;
}


function getwizardslist(){
	global$alang,$skin_dir;
	$ret='';
	$parser=new ParseXML();
	$WXML=$parser->GetXMLTree('wizards/wizards.xml');
	foreach($WXML['ROOT'][0]['WIZARDS'][0]['WIZARD']as$key=>$val){
		$disable=$val["ATTRIBUTES"]["DISABLE"];
		switch($val["ATTRIBUTES"]['ID']){
			case'accountwizard':
				
				if(isuserright("U")||$_SESSION['ACCOUNT']=='ADMIN')
				if($_SESSION["ACCOUNT"]!=$disable)$ret.='<div class="odkaz"><a href="#" onClick="newwindow(\'wizard.html?wpath='.$val['ATTRIBUTES']['PATH'].'&wscript='.$val['ATTRIBUTES']['SCRIPT'].'\');" ><center><img src="'.$skin_dir.'images/wizards/'.$val['ATTRIBUTES']['ICON'].'" /><div>'.getLabel($val['ATTRIBUTES']['ID']).'</div></center></a></div>';
				break;
			case'domainwizard':
				
				if($_SESSION["ACCOUNT"]!=$disable)$ret.='<div class="odkaz"><a href="#" onClick="newwindow(\'wizard.html?wpath='.$val['ATTRIBUTES']['PATH'].'&wscript='.$val['ATTRIBUTES']['SCRIPT'].'\');" ><center><img src="'.$skin_dir.'images/wizards/'.$val['ATTRIBUTES']['ICON'].'" /><div>'.getLabel($val['ATTRIBUTES']['ID']).'</div></center></a></div>';
				break;
	}

}

return$ret;
}

?>