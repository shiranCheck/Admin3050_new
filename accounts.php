<?php 
	require_once('formvariables.php');
	function processaccountcommands(){
		global$acc;
		global$user,$remote;
		global$skindata,$alang;
		setsystemcookie('SELECTACCOUNTS',$_REQUEST['selectaccounts']);
		setsystemcookie('SEARCHACCOUNT',$_REQUEST['searchaccount']);
		
		if(!$acc)return;
		checkuserobjects();
		
		if($_REQUEST['deleteflag']){
			$radelete=array();
			for($i=0;$i<count($acc);$i++){
				
				if(strpos($acc[$i],"@")){
					$user->Open($acc[$i]);
					
					if(($user->getProperty("u_domainadmin")||$user->getProperty("u_admin"))&&$_SESSION['ACCOUNT']=='DOMAINADMIN')$skindata['error']='<div class="errormessage">'.$alang['TStrings_wa_error_deletedomainadmin'].'</div>'; else $user->Delete();
				} else {
					$radelete[]=intval($acc[$i]);
				}

			}

			
			if(count($radelete)){
				sort($radelete);
				for($i=count($radelete)-1;$i>=0;$i--)$remote->DeleteIndex($radelete[$i]);
			}

		}

	}

	
	function checkaccountlimit($dom){
		
		if($_SESSION["ACCOUNT"]!='DOMAINADMIN'||!$_SESSION["ACCOUNTLIMIT"][$dom])return true;
		$oDomain=createobject("domain");
		$oDomain->Open($dom);
		$count=$oDomain->GetAccountCount();
		
		if($count>=$_SESSION["ACCOUNTLIMIT"][$dom])return false;
		return true;
	}

	
	function checkdomainaccess($dom){
		global$formobject,$formvalue;
		$result=true;
		
		if($_SESSION["ACCOUNT"]=='ADMIN')return true;
		
		if($dom=='group_member'&&isuserright("G"))return true;
		
		if($dom=='mail_member'&&isuserright("M"))return true;
		
		if($dom=='blacklist'||$dom=='whitelist')return isuserright("Q");
		
		if($_REQUEST['fileid']=='remote_domains'||$_REQUEST['fileid']=='remote_routing'||$_REQUEST['fileid']=='remote_headers'||$_REQUEST['cfileid']=='ra_schedule')return isuserright("R");
		
		if($formobject=="remote")return isuserright("R");
		
		if($formobject=='blacklist'||$formobject=='whitelist')return isuserright("Q");
		
		if(in_array($dom,$_SESSION['ACCESS'][$_SESSION['ACCOUNT']])){
			return true;
		} else {
			$accounts=array('catalog','domain','exec','group','list','listserv','notify','remote','resource','route','user');
			
			if(in_array($dom,$accounts)){
				return isuserright("U");
			}

		}

		$dom=getdomainvalue($dom);
		$api=createobject('api');
		$dom=$api->IDNToUTF8($dom);
		$domains=getdomainlist();
		for($i=0;$i<count($domains);$i++)
		if($domains[$i]["DOMAIN"]==$dom)return$result;
		return false;
	}

	
	function checkuserobjects(){
		global$user,$remote;
		
		if(!$user){
			$user=createobject("account");
			$remote=createobject("remoteaccount");
		}

	}

	
	function getaccounttype($type){
		switch($type){
			case 0:
				return"user";
				case 1:
					return"list";
					case 2:
						return"exec";
						case 3:
							return"notify";
							case 4:
								return"route";
								case 5:
									return"catalog";
									case 6:
										return"listserv";
										case 7:
											return"group";
											case 8:
												return"resource";
										}

									}

									
									function getobjectaccounttype($object){
										switch($object){
											case"user":
												return 0;
												case"list":
													return 1;
													case"exec":
														return 2;
														case"notify":
															return 3;
															case"route":
																return 4;
																case"catalog":
																	return 5;
																	case"listserv":
																		return 6;
																		case"group":
																			return 7;
																			case"resource":
																				return 8;
																		}

																	}

																	
																	function accountssort($a,$b){
																		return strcasecmp($a["SORT"],$b["SORT"]);
																	}

																	
																	function checkaccount($type){
																		$result=true;
																		@$accounts=$_COOKIE["SELECTACCOUNTS"];
																		
																		if($accounts)
																		if(!($type==$accounts))$result=false;
																		return$result;
																	}

																	
																	function checkaccountright($type,$report=false){
																		$result=false;
																		
																		if($_SESSION["ACCOUNT"]=='ADMIN')return true;
																		
																		if(in_array($type,$_SESSION['ACCESS'][$_SESSION['ACCOUNT']])){
																			return true;
																		}

																		
																		if($type=="user")$result=isuserright("U"); else
																		if($type=="resource")$result=isuserright("O"); else
																		if($type=="group")$result=isuserright("G"); else
																		if($type=="group_member")$result=isuserright("G"); else
																		if($type=="list")$result=isuserright("M"); else
																		if($type=="listserv")$result=isuserright("L"); else
																		if($type=="exec")$result=isuserright("E"); else
																		if($type=="remote")$result=isuserright("R"); else
																		if($type=="ra_schedule")$result=isuserright("R"); else
																		if($type=="route")$result=isuserright("S"); else
																		if($type=="notify")$result=isuserright("N"); else
																		if($type=="catalog")$result=isuserright("C"); else
																		if($type=="domain")$result=true; else
																		if($type=="datagrid")$result=true; else
																		if($type=="spamqueue")$result=true; else
																		if($type=="quarantine")$result=true;
																		
																		if($type=="rules")$result=isuserright("U");
																		
																		if($type=="headerfiles")$result=isuserright("M");
																		
																		if(!$result&&$report){
																			global$alang;
																			popuperror($alang["ACCESS_ERROR"],false);
																			$dieredirect='<body onload="window.open(\'index.html?messageid=TStrings_wa_str6\',\'_top\');"></body>';
																			die($dieredirect);
																		}

																		return$result;
																	}

																	
																	function getaccountlist($search=false,$domain=false){
																		global$api,$user,$sDomain,$remote,$encoding;
																		$count=0;
																		$value='';
																		@$accounts=$_COOKIE['SEARCHACCOUNT'];
																		
																		if($accounts){
																			$search=true;
																		}

																		
																		if(@$_COOKIE["SELECTACCOUNTS"]=="none"&&!$search)return;
																		$domain=$domain?$domain:
																		$sDomain;
																		checkuserobjects();
																		$query="";
																		
																		if(@$search)$value=addslashes(@$accounts);
																		
																		if(@$value){
																			$query="(u_alias like '%".$value."%') or (u_name like '%".$value."%')";
																		}

																		
																		if($_COOKIE["SELECTACCOUNTS"]&&$_COOKIE["SELECTACCOUNTS"]!='none'){
																			
																			if($query)$query.=' and ';
																			$query.='(u_type = '.getobjectaccounttype($_COOKIE["SELECTACCOUNTS"]).')';
																		}

																		$list=array();
																		
																		if($user->FindInitQuery($api->UTF8ToIDN($domain),$query)){
																			While($user->FindNext()){
																				$item["ACCOUNT"]=$user->EmailAddress;
																				$item["SORT"]=$user->GetProperty("u_alias");
																				$label=explode(";",$item["SORT"]);
																				$item["LABEL"]=$label[0]."@".($domain);
																				$item["NAME"]=slashes_js($user->GetProperty("u_name"));
																				$item["TYPE"]=getaccounttype($user->GetProperty("u_type"));
																				
																				if($item["TYPE"]=='group')$item["NAME"]=$item["DESC"]=slashes_js($user->GetProperty("g_description"));
																				$item["ICON"]=$item["TYPE"];
																				
																				if($item["TYPE"]=="user")
																				if($user->GetProperty("u_admin"))$item["ICON"]="admin"; else
																				if($user->GetProperty("u_domainadmin"))$item["ICON"]="domainadmin";
																				
																				if(!checkaccount($item["TYPE"]))continue;
																				
																				if(!checkaccountright($item["TYPE"]))continue;
																				$item["TYPELABEL"]=getobjecttitle(getaccounttype($user->GetProperty("u_type")));
																				$list[]=$item;
																				$count++;
																				
																				if($count>maxaccountlist)break;
																			}

																			$user->FindDone();
																		}

																		$rc=$remote->Count();
																		for($i=0;$i<$rc;$i++){
																			$remote->Open($i);
																			$item["ACCOUNT"]=$i;
																			$item["SORT"]=$remote->GetProperty("ra_username");
																			$item["LABEL"]=$remote->GetProperty("ra_name");
																			$item["NAME"]=$remote->GetProperty("ra_username")."@".$remote->GetProperty("ra_server");
																			$item["ICON"]="remote";
																			$item["TYPE"]=$item["ICON"];
																			$item["TYPELABEL"]=getobjecttitle($item["ICON"]);
																			
																			if(!checkaccount($item["TYPE"]))continue;
																			
																			if(!($domain==$remote->GetProperty("ra_domainstring")))continue;
																			
																			if(!checkaccountright($item["TYPE"]))continue;
																			$list[]=$item;
																			$count++;
																			
																			if($count>maxaccountlist)break;
																		}

																		usort($list,"accountssort");
																		convertaccounts($list);
																		return$list;
																	}

																	
																	function getaccountitems($search){
																		global$user,$sDomain,$api;
																		checkuserobjects();
																		$items=array();
																		$list=getaccountlist($search,$sDomain);
																		for($i=0;$i<count($list);$i++){
																			$item["id"]=(string)getstrvalue($list[$i]["ACCOUNT"]);
																			$item["urlid"]=(string)urlencode($list[$i]["ACCOUNT"]);
																			
																			if(strpos($list[$i]["LABEL"],";")){
																				$alias=explode(";",$list[$i]["LABEL"]);
																				$alias=$alias[0]."@".$sDomain;
																			} else $alias=$list[$i]["LABEL"];
																			$item["label"]=(string)$alias;
																			$item["urllabel"]=(string)urlencode($list[$i]["LABEL"]);
																			$item["type"]=(string)$list[$i]["TYPE"];
																			$item["TYPELABEL"]=(string)getobjecttitle($list[$i]["TYPE"]);
																			$item["icon"]=(string)$list[$i]["ICON"];
																			$item["name"]=(string)$list[$i]["NAME"];
																			$item["desc"]=(string)$list[$i]["DESC"];
																			$item["evenv"]=(string)$i%2;
																			$items[]=$item;
																		}

																		return$items;
																	}

																	
																	function getselectoptions(){
																		global$alang;
																		@$accounts=$_COOKIE["SELECTACCOUNTS"];
																		$admin=$_SESSION["ACCOUNT"]=='ADMIN';
																		$options='';
																		$options.=addselectoption("",$alang['TFrameAccounts_SMSAllRadio'],$accounts);
																		
																		if($admin||isuserright("U"))$options.=addselectoption("user",$alang['TDomainAdminForm_User'],$accounts);
																		
																		if($admin||isuserright("G"))$options.=addselectoption("group",$alang['TDomainAdminForm_Group'],$accounts);
																		
																		if($admin||isuserright("M"))$options.=addselectoption("list",$alang['TDomainAdminForm_List'],$accounts);
																		
																		if($admin||isuserright("L"))$options.=addselectoption("listserv",$alang['TDomainAdminForm_ListServ'],$accounts);
																		
																		if($admin||isuserright("E"))$options.=addselectoption("exec",$alang['TDomainAdminForm_Exec'],$accounts);
																		
																		if($admin||isuserright("R"))$options.=addselectoption("remote",$alang['TDomainAdminForm_Remote'],$accounts);
																		
																		if($admin||isuserright("S"))$options.=addselectoption("route",$alang['TDomainAdminForm_Static'],$accounts);
																		
																		if($admin||isuserright("N"))$options.=addselectoption("notify",$alang['TDomainAdminForm_Notify'],$accounts);
																		
																		if($admin||isuserright("C"))$options.=addselectoption("catalog",$alang['TDomainAdminForm_Catalog'],$accounts);
																		
																		if($admin||isuserright("O"))$options.=addselectoption("resource",$alang['TConfigForm_NewResourceItem'],$accounts);
																		return$options;
																	}

																	
																	function processaccounts(){
																		global$api,$skin_dir,$alang,$sDomain,$post;
																		
																		if($_SESSION["ACCOUNT"]=='DOMAINADMIN'){
																			$olddomain=$sDomain;
																			
																			if(!checkdomainaccess($sDomain)){
																				popuperror($alang["ACCESS_ERROR"]);
																				return;
																			}

																			$sDomain=$olddomain;
																		}

																		
																		if($post)$result=processaccountcommands();
																		checkuserobjects();
																		$oDomain=createobject("domain");
																		$oDomain->Open($api->UTF8ToIDN($sDomain));
																		$sAccountsLimit=$oDomain->GetProperty("D_AccountNumber");
																		$sAccountsLimit=$sAccountsLimit?$sAccountsLimit:
																		$alang["TStrings_cunlimited"];
																		$sAccountsCount=$oDomain->GetAccountCount();
																		$skindata['account_count_label']=$alang["TFrameAccounts_NumberOfUsLabel"];
																		$skindata['account_limit_label']=$alang["TFrameAccounts_AccountNumbLabel"];
																		$skindata['account_count']=$sAccountsCount;
																		$skindata['account_limit']=$sAccountsLimit;
																		$admin=$_SESSION["ACCOUNT"]=='ADMIN';
																		
																		if($admin||isuserright("U"))$menudata['newuser']=$alang["TStrings_wa_str17"];
																		
																		if($admin||isuserright("G"))$menudata['newgroup']=$alang["TStrings_wa_str18"];
																		
																		if($admin||isuserright("O"))$menudata['newresource']=$alang["TStrings_wa_str19"];
																		
																		if($admin||isuserright("M"))$menudata['newlist']=$alang["TStrings_wa_str20"];
																		
																		if($admin||isuserright("L"))$menudata['newlistserver']=$alang["TStrings_wa_str21"];
																		
																		if($admin||isuserright("E"))$menudata['newexecutable']=$alang["TStrings_wa_str22"];
																		
																		if($admin||isuserright("R"))$menudata['newremoteaccount']=$alang["TStrings_wa_str24"];
																		
																		if($admin||isuserright("S"))$menudata['newstaticroute']=$alang["TStrings_wa_str25"];
																		
																		if($admin||isuserright("N"))$menudata['newnotification']=$alang["TStrings_wa_str23"];
																		
																		if($admin||isuserright("C"))$menudata['newcatalog']=$alang["TStrings_wa_str26"];
																		$menudata['domains']=$alang["TConfigForm_LimitsSheet"];
																		$menudata['logout']=$alang["LOGOUT"];
																		
																		if(@!$sDomain)$sDomain=$_COOKIE['domainv'];
																		
																		if(@!$sDomain){
																			$doms=getdomainlist();
																			$sDomain=$doms[0]["DOMAIN"];
																		}

																		$menudata['value']=getstrvalue($sDomain);
																		$menudata['amp']='&amp;';
																		$menudata['nbsp']='&nbsp;';
																		$menu=template($skin_dir.'menubuttons.tpl',$menudata);
																		$skindata['menu']=$menu;
																		$items=getaccountitems(@$_REQUEST['searchaccount']);
																		$skindata['account']=$alang["TAccessControlListForm_AccountIL"];
																		$skindata['name']=$alang["TCatalogItemForm_NameL"];
																		$skindata['type']=$alang["TFrameAccounts_AccountTypeL"];
																		$skindata['desc']=$alang["TCommentForm_CommentL"];
																		$skindata['domainname']=$sDomain;
																		$skindata['accounts']['num']=$items;
																		$skindata['accounts']['visible']=count($items);
																		@$skindata['result']=$result;
																		$skindata['statistics']=$alang["TConfigForm_ServiceStatisticsItem"];
																		$skindata['delete']=$alang["TConfigForm_DeleteItem"];
																		$skindata['searchfor']=$alang["TAccountSearchForm_FindUsrLab"];
																		$skindata['display']=$alang["TFrameAccounts_FilterBox"];
																		$skindata['refresh']=$alang["TStatisticsForm_ChartRefreshButton"];
																		$skindata['selectaccounts']=$alang["TAccountSearchForm_AccountTypeL"];
																		$skindata['settings']=$alang["TConfigForm_LogODBCButton"];
																		$skindata['accountoptions']=getselectoptions();
																		$skindata['domainadmin']['visible']=$_SESSION["ACCOUNT"]=='DOMAINADMIN';
																		$skindata['admin']['visible']=$_SESSION["ACCOUNT"]=='ADMIN';
																		$skindata['path']=$skin_dir;
																		$skindata['searchl']=$alang["TConfigForm_SearchItem"];
																		$skindata['perpagel']=$alang["TStrings_wa_perpagel"];
																		$skindata['search']=$_REQUEST['dgs1']?$_REQUEST['dgs1']:
																		"";
																		$skindata['searchin']=$_REQUEST['searchin'];
																		$skindata['limit']=$_REQUEST['dgl1']?$_REQUEST['dgl1']:
																		20;
																		$search="";
																		
																		if(@$_COOKIE['SEARCHACCOUNT'])@$search=$_COOKIE['SEARCHACCOUNT'];
																		$skindata['searchaccount']=$search;
																		return template($skin_dir.'accounts.tpl',$skindata);
																	}

																	?>