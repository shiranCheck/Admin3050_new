<?php 
	
	function getinterfacelist(){
		global$alang;
		$item["name"]=$alang["TConfigForm_BasicMode"];
		$item["id"]='basic';
		
		if(@$_COOKIE["GUI"]=='basic')$item["select"]=1; else $item["select"]=0;
		$items[0]=$item;
		$item["name"]=$alang["TConfigForm_AdvancedMode"];
		$item["id"]='advanced';
		
		if(@$_COOKIE["GUI"]=='advanced')$item["select"]=1; else $item["select"]=0;
		$items[1]=$item;
		$item["name"]=$alang["TConfigForm_DomainAdminBox"];
		$item["id"]='domainadmin';
		
		if(@$_COOKIE["GUI"]=='domainadmin')$item["select"]=1; else $item["select"]=0;
		$items[2]=$item;
		
		if(!isset($_COOKIE["GUI"]))$items[1]['select']=1;
		return$items;
	}

	?>