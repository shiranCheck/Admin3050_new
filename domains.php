<?php 
	
	function domainsort($a,$b){
		return strcasecmp($a["DOMAIN"],$b["DOMAIN"]);
	}

	
	function checkdomainobjects(){
		global$api,$oDomain;
		
		if(!$api){
			$api=createobject("api");
		}

		
		if(!$oDomain){
			$oDomain=createobject("domain");
		}

	}

	
	function getdomaindesc($dom){
		global$oDomain;
		$oDomain->Open($dom);
		return slashes_js($oDomain->GetProperty("d_description"));
	}

	
	function slashes_js($string){
		$string=str_replace("\\","\\\\",$string);
		$string=str_replace("'","&apos;",$string);
		$string=str_replace("\"","\\\"",$string);
		return$string;
	}

	
	function slashes_input($string){
		$string=str_replace("'","&apos;",$string);
		$string=str_replace("\"","&quot;",$string);
		return$string;
	}

	
	function getdomainlist(){
		global$api,$domain;
		checkdomainobjects();
		$list=array();
		$skiplist=array();
		$item["DOMAIN"]="";
		$item["DESC"]="";
		$list[]=$item;
		$all=false;
		
		if($_SESSION["ACCOUNT"]=='DOMAINADMIN'){
			$firstdomain=$_SESSION["DOMAIN"];
			$file=getuserrightsfile();
			
			if(file_exists($file)){
				$flist=file($file);
				for($i=0;$i<count($flist);$i++){
					$dom=strtolower(trim($flist[$i]));
					
					if(!$dom)continue;
					
					if($dom=="*"){
						$all=true;
						break;
					}

					
					if(strpos($dom,"="))continue;
					
					if($dom[0]=="!"){
						$dom=substr($dom,1,strlen($dom));
						
						if($dom==$_SESSION['DOMAIN']){
							$reset=true;
						}

						$skiplist[$dom]=true;
						continue;
					}

					
					if($dom!=$firstdomain){
						$item["DOMAIN"]=$api->IDNToUTF8($dom);
						$item["DESC"]=getdomaindesc($dom);
						$list[]=$item;
					}

				}

				
				if($reset){
					$firstdomain=reset($list);
					$firstdomain=$firstdomain['DOMAIN'];
					$_SESSION['DOMAIN']=$firstdomain;
					$_SESSION['RESET']=true;
				}

			}

		}

		
		if($_SESSION["ACCOUNT"]=='ADMIN'||$_SESSION["ACCOUNT"]=='GATEWAY'||$all){
			$firstdomain=$api->GetDomain(0);
			$dc=$api->GetDomainCount();
			for($i=1;$i<$dc;$i++){
				$dom=$api->GetDomain($i);
				$item["DOMAIN"]=$api->IDNToUTF8($dom);
				$item["DESC"]=getdomaindesc($dom);
				
				if(@$skiplist[$item["DOMAIN"]])continue;
				$list[]=$item;
			}

		}

		usort($list,"domainsort");
		
		if($_SESSION['RESET']){
			array_shift($list);
		} else {
			$list[0]["DOMAIN"]=$api->IDNToUTF8($firstdomain);
			$list[0]["DESC"]=getdomaindesc($firstdomain);
		}

		convertdomains($list);
		return$list;
	}

	
	function processdomaincommands(){
		global$edit,$dom,$oDomain;
		checkdomainobjects();
		
		if(!$dom)return;
		
		if(@$_REQUEST["deleteflag"]){
			for($i=0;$i<count($dom);$i++){
				$oDomain->Open($dom[$i]);
				$oDomain->Delete();
			}

		}

	}

	
	function getdomainitems(){
		$items=array();
		$api=createobject("api");
		$domains=getdomainlist();
		for($i=0;$i<count($domains);$i++){
			
			if($domains[$i]['DOMAIN']){
				$item["name"]=(string)$domains[$i]["DOMAIN"];
				$item["urlname"]=(string)urlencode($domains[$i]["DOMAIN"]);
				$item["desc"]=(string)$domains[$i]["DESC"];
				$item["even"]=(string)$i%2;
				$items[]=$item;
			}

		}

		unset($api);
		return$items;
	}

	
	function processdomains(){
		global$skin_dir,$alang;
		checkdomainobjects();
		$result=processdomaincommands();
		$items=getdomainitems();
		$skindata["domain"]=$alang["TFrameAccounts_OptionsDomainBox"];
		$skindata["newdomainl"]=$alang["TConfigForm_NewDomainItem"];
		$skindata["desc"]=$alang["TWebHostForm_DescL"];
		$skindata['result']=$result;
		$skindata['edit']=$alang["TConfigForm_ServiceEditButton"];
		$skindata['delete']=$alang["TConfigForm_DeleteItem"];
		$skindata['refresh']=$alang["TStatisticsForm_ChartRefreshButton"];
		$skindata['domains']['num']=$items;
		$skindata['domainadmin']['visible']=$_SESSION["ACCOUNT"]=='DOMAINADMIN';
		$skindata['admin']['visible']=$_SESSION["ACCOUNT"]=='ADMIN';
		$skindata['newdomain']=$_SESSION['ACCOUNT']=='GATEWAY'||$_SESSION['ACCOUNT']=='ADMIN';
		$skindata['deletebutton']=$_SESSION['ACCOUNT']=='GATEWAY'||$_SESSION['ACCOUNT']=='ADMIN';
		$skindata['path']=$skin_dir;
		$skindata['searchl']=$alang["TConfigForm_SearchItem"];
		$skindata['perpagel']=$alang["TStrings_wa_perpagel"];
		$skindata['search']=$_REQUEST['dgs1']?$_REQUEST['dgs1']:
		"";
		$skindata['limit']=$_REQUEST['dgl1']?$_REQUEST['dgl1']:
		20;
		$skindata['searchin']=$_REQUEST['searchin'];
		return template($skin_dir.'domains.tpl',$skindata);
	}

	?>