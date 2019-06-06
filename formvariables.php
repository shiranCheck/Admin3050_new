<?php 
	include_once("variablefunctions.php");
	require_once("syntax/clsgridhandler.php");
	require_once("function.php");
	$accountids=array('user','domain','remote','list','listserv','catalog','remote','route','notify','exec','group');
	function checkformaction(&$error){
		global$formtype,$formvalue,$formobject,$formapi;
		$result=true;
		
		if($result&&$formtype=="edit"&&$formobject=="user"&&$_SESSION["ACCOUNT"]=='DOMAINADMIN')
		if(@$formapi->GetProperty("u_admin")){
			$result=false;
			global$alang;
			$error=$alang["ADMINACCOUNT"];
		}

		
		if($result&&$formtype=="add"&&!($formobject=="domain"))
		if(!checkaccountlimit($_REQUEST['account_domain'])){
			$result=false;
			global$alang;
			$error='<div id="err" class="errormessage">'.$alang['TStrings_cmaxexceeded'].'</div>';
		}

		return$result;
	}

	
	function checkformlimit($name){
		$result=true;
		$name=strtolower($name);
		
		if(@$_SESSION["LIMIT"])
		if(isset($_SESSION["LIMIT"][$name]))$result=intval($_SESSION["LIMIT"][$name]);
		return$result;
	}

	
	function checkoption($name,&$type,$disable){
		global$formtype;
		$result=true;
		
		if((($type=="textfile")||($type=="bwfile")||($type=="mailboxview"))&&$formtype=="add")$result=false;
		
		if($name=='account_domain'&&$formtype=='add'){
			$result=1;
			$type='hidden';
		} else {
			
			if($result)$result=checkformlimit($name);
		}

		
		if($result)
		if($disable){
			$l=explode("|",$disable);
			for($i=0;$i<count($l);$i++)$list[$l[$i]]=true;
			
			if(isset($_SESSION["LIMIT"][strtolower($name)])&&$_SESSION["LIMIT"][strtolower($name)]==1){
				return true;
			}

			switch($_SESSION['ACCOUNT']){
				case'ADMIN':
					
					if($list['admin']){
						return false;
					}

					break;
				case'DOMAINADMIN':
					
					if($list['domainadmin']){
						return false;
					}

					break;
				case'GATEWAY':
					
					if($list['gateway']){
						return false;
					}

					break;
		}

		
		if($list['edit'])return!($formtype=="edit");
		
		if($list['add'])return!($formtype=="add");
		
		if($list['all']||$list['true'])return false;
	}

	return$result;
}


function binary2decimal($num){
	$dec=0;
	for($i=0;$i<sizeof($num);$i++){
		$dec+=pow(2,$i)*$num[$i];
	}

	return$dec;
}


function decimal2binary($num,$length=7){
	$i=0;
	while($num!=0){
		$binary[$i++]=$num%2;
		
		if(($num-$num%2)!=0)$num=($num-$num%2)/2; else $num=0;
	}

	while(count($binary)<$length){
		$binary[$i++]=0;
	}

	return$binary;
}


function parsedatfile($file,$type){
	global$rawdata,$rawindex,$formvalue,$formapi;
	$ipath='';
	
	if(strpos($file,'config/')===0){
		$filename=str_replace('config/',$_SESSION['CONFIGPATH'],$file);
	} else {
		$filename=$file;
		$api=createobject("api");
		
		if(!$_SESSION["INSTALLPATH"])$ipath=$api->getProperty("c_installpath"); else $ipath=$_SESSION["INSTALLPATH"];
		
		if($type=='spam'||$type=='spam2')$ipath=$_SESSION['SPAMPATH'];
		
		if($type=='calendar'||$type=='syncml'||$type=='caldav'||$type=='activesync')$ipath=$_SESSION['CALENDARPATH'];
		
		if($type=='proxy'||$type=='smsusers'||$type=='autodiscover')$ipath=$_SESSION['CONFIGPATH'];
	}

	
	if(strtolower($type)=="domainkey")$filename="config/".$formvalue."/domainkey.dat";
	$path=$ipath.$filename;
	
	if($type=='spam2'&&!file_exists($path)){
		
		if(get_class($formapi)!=='MerakAPI'||get_class($formapi)!=='IcewarpAPI'){
			$hack_api=new MerakAPI();
		} else {
			$hack_api=&$formapi;
		}

		$hack_var=$formapi->GetProperty('C_AS_Challenge_ConnectionString');
		$hack_api->SetProperty('C_AS_Challenge_ConnectionString',$hack_var);
	}

	$lines=@file($path);
	
	if($lines)switch(strtolower($type)){
		case'domainkey':
			case'path':
				foreach($lines as$k=>$v)$ret[$k+1]=$v;
				break;
			case'tags':
				$Header=explode(";",$lines[0]);
				$Footer=explode(";",$lines[1]);
				$Bits=$lines[4];
				$ret["TextHeader"]=$Header[0];
				$ret["HTMLHeader"]=$Header[1];
				$ret["TextFooter"]=$Footer[0];
				$ret["HTMLFooter"]=$Footer[1];
				$Bits=decimal2binary($Bits);
				$ret["L2L"]=$Bits[0];
				$ret["R2L"]=$Bits[2];
				$ret["L2R"]=$Bits[1];
				$ret["R2R"]=$Bits[3];
				break;
			case'accdefaults':
				foreach($lines as$v){
					$tmp=explode('=',$v,2);
					$ret[$tmp[0]]=$tmp[1];
				}

				break;
			case'defaultrights':
				$rights=explode("=",$lines[0],2);
				$rights=$rights[1];
				$ret["U"]=substr_count($rights,"U")>0?1:
					0;
					$ret["M"]=substr_count($rights,"M")>0?1:
						0;
						$ret["L"]=substr_count($rights,"L")>0?1:
							0;
							$ret["R"]=substr_count($rights,"R")>0?1:
								0;
								$ret["S"]=substr_count($rights,"S")>0?1:
									0;
									$ret["C"]=substr_count($rights,"C")>0?1:
										0;
										$ret["N"]=substr_count($rights,"N")>0?1:
											0;
											$ret["E"]=substr_count($rights,"E")>0?1:
												0;
												$ret["V"]=substr_count($rights,"V")>0?1:
													0;
													$ret["G"]=substr_count($rights,"G")>0?1:
														0;
														$ret["Q"]=substr_count($rights,"Q")>0?1:
															0;
															for($i=1;$i<count($lines);$i++)$rawdata[$i]=$lines[$i];
															break;
														case'spam':
															$varnames=array(0=>'use_bayes',1=>'use_spf',2=>'use_surbl',3=>'use_domainkeys',4=>'report_safe',5=>'use_rbl',6=>'spf_level',7=>'use_razor2',8=>'required_hits',9=>'bayes_auto_learn',10=>'bayes_auto_learn_threshold_spam',11=>'bayes_auto_learn_threshold_nonspam');
															unset($rawindex);
															unset($rawdata);
															foreach($lines as$lindex=>$line){
																$useable=false;
																$larr=explode(" ",$line);
																
																if(in_array($larr[0],$varnames)){
																	$ret[$larr[0]]=$larr[1];
																	$rawdata[$lindex]=$line;
																	$rawindex[$lindex]=$larr[0];
																	$useable=true;
																}

																$rawdata[$lindex]=$line;
															}

															$_SESSION['rawdata']=$rawdata;
															$_SESSION['rawindex']=$rawindex;
															break;
														case'domainad':
															@$xml=simplexml_load_file($path);
															
															if($xml->DOMAIN)foreach($xml->DOMAIN as$domain){
																
																if($formapi->Name&&(strval($domain->DOMAIN)==$formapi->Name)){
																	foreach($domain->children()as$key=>$val)$ret[$key]=strval($val);
																}

															}

															break;
														case'autodiscover':
															case'activesync':
																case'syncml':
																	case'caldav':
																		@$xml=simplexml_load_file($path);
																		
																		if($xml->children())foreach($xml->children()as$var=>$value){
																			$ret[$var]=strval($value);
																		}

																		break;
																	case'webmail':
																		@$xml=simplexml_load_file($path);
																		
																		if($xml->item->children())foreach($xml->item->children()as$var=>$value)$ret[$var]=trim(strval($value));
																		
																		if(!$ret['logging_type']){
																			$ret['logging_type']=0;
																		}

																		break;
																	case'smsusers':
																		$ret['users']=implode('',$lines);
																		break;
																	case'spam2':
																		case'default':
																			default:
																				foreach($lines as$v){
																					$tmp=explode('=',$v,2);
																					$ret[$tmp[0]]=$tmp[1];
																				}

																				break;
																	}

																	unset($api);
																	return$ret;
																}

																
																function setdatvalue($variable,$value,$datfile,$dattype){
																	
																	if(@!$_SESSION['datdata'][$datfile]){
																		$_SESSION['datdata'][$datfile]["data"]=parsedatfile($datfile,$dattype);
																		$_SESSION['datdata'][$datfile]["type"]=$dattype;
																	}

																	$_SESSION['datdata'][$datfile]["data"][$variable]=$value;
																	return true;
																}

																
																function savedatfiles(){
																	global$rawdata,$rawindex,$formvalue;
																	
																	if($_SESSION['datdata'])foreach($_SESSION['datdata']as$key=>$val){
																		
																		if($key=="domainkey.dat"){
																			$filename=$_SESSION["CONFIGPATH"].$formvalue."/".$key;
																		} else {
																			
																			if(strpos($key,'config/')!==false){
																				$filename=str_replace('config/',$_SESSION['CONFIGPATH'],$key);
																			} else {
																				$filename=$_SESSION["INSTALLPATH"].$key;
																			}

																		}

																		
																		if($val['type']=='spam'||$val['type']=='spam2')$filename=$_SESSION['SPAMPATH'].$key;
																		
																		if($val['type']=='syncml'||$val['type']=='calendar'||$val['type']=='caldav'||$val['type']=='activesync')$filename=$_SESSION['CALENDARPATH'].$key;
																		
																		if($val['type']=='proxy'||$val['type']=='smsusers'||$val['type']=='autodiscover')$filename=$_SESSION['CONFIGPATH'].$key;
																		$ret='';
																		switch(strtolower($val["type"])){
																			case'domainkey':
																				case"path":
																					
																					if($val["data"])foreach($val["data"]as$v)$ret.=trim($v).CRLF;
																					break;
																				case"tags":
																					$v=$val["data"];
																					$ret.=$v["TextHeader"].';'.$v["HTMLHeader"].CRLF;
																					$ret.=$v["TextFooter"].';'.$v["HTMLFooter"].CRLF.CRLF.CRLF;
																					$Bits=$v["L2L"]*1+$v["R2L"]*4+$v["L2R"]*2+$v["R2R"]*8;
																					$ret.=$Bits;
																					break;
																				case"accdefaults":
																					
																					if($val["data"])foreach($val["data"]as$k=>$v)
																					if(@$_REQUEST["use_settings_".$k])$ret.=$k.'='.trim($v).CRLF;
																					break;
																				case"defaultrights":
																					$ret.='RIGHTS=';
																					
																					if($val["data"])foreach($val["data"]as$k=>$v)
																					if($v)$ret.=$k;
																					$ret.=CRLF;
																					
																					if($rawdata)foreach($rawdata as$line)$ret.=trim($line).CRLF;
																					break;
																				case"spam":
																					
																					if($rawdata)foreach($rawdata as$lindex=>$line){
																						$return='';
																						
																						if(isset($rawindex[$lindex])){
																							
																							if($val["data"][strtolower($rawindex[$lindex])])$value=$val["data"][strtolower($rawindex[$lindex])]; else $value=0;
																							$return=$rawindex[$lindex]." ".((!$value)?0:
																								$value);
																							} else $return=$rawdata[$lindex];
																							$ret.=trim($return).CRLF;
																						} else {
																							foreach($val["data"]as$lindex=>$line){
																								
																								if($lindex)$ret.=$lindex.' '.$line.CRLF;
																							}

																						}

																						break;
																					case"logopath":
																						
																						if($val["data"]){
																							foreach($val["data"]as$k=>$v)$ret.=$k.'='.trim(securepath($v)).CRLF;
																						}

																						break;
																					case'spam2':
																						
																						if($val["data"])foreach($val["data"]as$k=>$v){
																							
																							if(!$v)$valll=0; else $valll=$v;
																							
																							if(($k!='BlockKeywords'||$k!='BypassKeywords')&&!$v){
																								$valll='';
																							}

																							
																							if(!$v&&$k=='SpamChallengeSeparateUsers'){
																								$valll=0;
																							}

																							$ret.=$k.'='.trim($valll).CRLF;
																						}

																						break;
																					case'domainad':
																						
																						if($val["data"])foreach($val["data"]as$var=>$val){
																							
																							if($var=='ACTIVE'||$var=='HOSTDOMAINACTIVE')$domain[$var][0]["VALUE"]=$val?1:
																								0; else $domain[$var][0]["VALUE"]=$val;
																							}

																							$xmlparse=new ParseXML;
																							
																							if(file_exists($filename)){
																								$xml=$xmlparse->GetXMLTree($filename);
																								$updated=false;
																								foreach($xml["DOMAINS"][0]["DOMAIN"]as$kd=>$dom){
																									
																									if($_REQUEST['domain_name']&&$dom["DOMAIN"][0]["VALUE"]==$_REQUEST['domain_name']){
																										$xml["DOMAINS"][0]["DOMAIN"][$kd]=$domain;
																										$updated=true;
																									}

																								}

																								
																								if(!$updated){
																									$domain["DOMAIN"][0]["VALUE"]=$_REQUEST['domain_name'];
																									$xml["DOMAINS"][0]["DOMAIN"][]=$domain;
																								}

																							} else {
																								$domain["DOMAIN"][0]["VALUE"]=$_REQUEST['domain_name'];
																								
																								if($domain)$xml["DOMAINS"][0]["DOMAIN"][]=$domain;
																							}

																							
																							if($xml){
																								$ret=$xmlparse->Array2XML($xml);
																							}

																							break;
																						case'autodiscover':
																							case'activesync':
																								case'syncml':
																									case'caldav':
																										
																										if(file_exists($filename))@$xml=simplexml_load_file($filename); else @$xml=simplexml_load_string('<Config></Config>');
																										foreach($val["data"]as$var=>$val)
																										if(strtolower($var)!='config')$xml->$var=trim(strval($val));
																										
																										if($xml)$ret=$xml->asXML();
																										break;
																									case'webmail':
																										
																										if(file_exists($filename))@$xml=simplexml_load_file($filename); else @$xml=simplexml_load_string('<global_settings><item></item></global_settings>');
																										foreach($val["data"]as$var=>$val){
																											switch($var){
																												case'smtp_auth':
																													case'disabled':
																														case'logging_type':
																															case'logs':
																																$xml->item->$var=intval($val);
																																break;
																															default:
																																
																																if(trim(strval($val))){
																																	$xml->item->$var=trim(strval($val));
																																} else {
																																	unset($xml->item->$var);
																																}

																																break;
																														}

																													}

																													
																													if($xml)$ret=$xml->asXML(); else $ret='';
																													break;
																													case'smsusers':
																														$ret=$val["data"]["users"];
																														break;
																													case"default":
																														default:
																															
																															if($val["data"])foreach($val["data"]as$k=>$v)$ret.=$k.'='.trim($v).CRLF;
																															break;
																												}

																												
																												if($filename){
																													
																													if(strlen(trim($ret))==0){
																														@unlink($filename);
																													} else {
																														$dirname=dirname($filename);
																														
																														if(!is_dir($dirname)){
																															mkdirtree($dirname);
																														}

																														$fp2=@fopen($filename,'w+');
																														$bytes=@fwrite($fp2,$ret,strlen($ret));
																														@fclose($fp2);
																													}

																												}

																												unset($_SESSION["datdata"]);
																											}

																											return true;
																										}

																										
																										function getformvalue($name,$function,$variable,$datfile,$source,$dattype,$inverse,$request,$option){
																											global$formapi,$formtype,$defaultvalue,$gObj;
																											$value=false;
																											
																											if($datfile&&$source!='API'){
																												
																												if(@!$_SESSION['datdata'][$datfile]){
																													$_SESSION['datdata'][$datfile]["data"]=parsedatfile($datfile,$dattype);
																													$_SESSION['datdata'][$datfile]["type"]=$dattype;
																												}

																												@$value=$_SESSION['datdata'][$datfile]["data"][$variable];
																											}

																											
																											if($variable&&($variable!="s_weekdays")&&(!$datfile||$source=="API")&&(!$gObj->Multigrid))$value=$formapi->GetProperty($variable);
																											
																											if($function){
																												formfunctionvalues($function,$value,true,$name,$message,$option);
																											}

																											
																											if($gObj->Multigrid&&!$value)$value=$gObj->getGridItemData($variable);
																											
																											if($inverse){
																												
																												if(!$value||$value==0)$value=1; else $value=0;
																											}

																											
																											if($formtype=='add'&&isset($defaultvalue)){
																												
																												if($option['ATTRIBUTES']['TYPE']=='checkbox'||$option['ATTRIBUTES']['TYPE']=='slider')$value=$defaultvalue;
																												
																												if($option['ATTRIBUTES']['TYPE']=='edit')$value=$defaultvalue;
																											}

																											$pos=false;
																											
																											if($variable=='d_description'||$variable=='u_name'||$variable=='g_description')$value=slashes_input($value);
																											return$value;
																										}

																										
																										function setformvalue($item,$value,$use,$datfile,$dattype,&$message,&$error=false){
																											global$formapi,$formtype,$formobject,$formvalue,$gObj,$alang;
																											@$function=$item["FUNCTION"];
																											@$name=$item["NAME"];
																											@$variable=$item["VARIABLE"];
																											@$type=$item["TYPE"];
																											@$inverse=$item["INVERSE"];
																											@$source=$item["SOURCE"];
																											@$property=$item["PROPERTY"];
																											$result=true;
																											$option["ATTRIBUTES"]=$item;
																											
																											if($type=="checkbox"||$type=="between")
																											if($value)$value=true;
																											
																											if($type=="account_select")return true;
																											
																											if($type=="upload"&&$_FILES[$name]['tmp_name']){
																												
																												if($_REQUEST['restore_password'])$param=array($_FILES[$name]['tmp_name'],$_REQUEST['restore_password']); else $param=array($_FILES[$name]['tmp_name']);
																												switch(strtolower($item["FILEID"])){
																													case'restoreconfig':
																														$filename=$param[0];
																														$gwapi=new MerakGWAPI();
																														$gwapi->user=$formapi->GetProperty("C_GW_SuperUser");
																														$gwapi->pass=$formapi->GetProperty("C_GW_SuperPass");
																														$gwapi->Login();
																														$result=$gwapi->FunctionCall("ImportData",$gwapi->sessid,file_get_contents($filename));
																														$rmsg=($result?$alang["TStrings_crestoreok"]:
																															$alang["TStrings_crestoreerror"]);
																															break;
																														case'migration_migratemessagesaccounts':
																															$filename=$param[0];
																															$user=$_REQUEST['TMigrateForm_UL'];
																															$pass=$_REQUEST['TMigrateForm_PL'];
																															$domain=$_REQUEST['TMigrateForm_DL'];
																															$result=$formapi->Migration_MigrateMessages(0,$user,$pass,$domain,file_get_contents($filename));
																															$rmsg=($result?$alang["TStrings_cmigrationsent"]:
																																$alang["TStrings_cmigrationerror"]);
																																break;
																															default:
																																$result=call_user_method_array($item["FILEID"],$formapi,$param);
																																$rmsg=($result?$alang["TStrings_crestoreok"]:
																																	$alang["TStrings_crestoreerror"]);
																																	break;
																														}

																														$message.='<div id="infomessage">'.$rmsg.'</div>';
																													}

																													
																													if($inverse==1){
																														
																														if(!$value)$value=1; else $value=0;
																													}

																													
																													if($type=="daysofweek"){
																														for($i=0;$i<7;$i++)
																														if($_REQUEST[$name.$i]!="on")$binary[$i]=1; else $binary[$i]=0;
																														$value=binary2decimal($binary);
																													}

																													
																													if($gObj->Multigrid&&!$function){
																														$result=$gObj->setGridItemData($variable,$value);
																													} else {
																														
																														if($variable){
																															$save=true;
																															
																															if($property)
																															if(!$use)$save=false;
																															
																															if($datfile&&$source!='API'){
																																$result=setdatvalue($variable,$value,$datfile,$dattype,$save);
																															} else
																															if($save){
																																
																																if($variable=="u_emailalias"||$variable=="u_alias"){
																																	
																																	if($_REQUEST['account_domain'])$_COOKIE['domainv']=$_REQUEST['account_domain'];
																																	
																																	if($formobject=="group"||$formobject=="list"||$formobject=="listserver"){
																																		
																																		if($formobject=="list"&&$formapi->GetProperty("m_listfile")=="")$result=$formapi->SetProperty("m_listfile",$_REQUEST['account_domain'].'/'.$value.'.txt');
																																		
																																		if($formobject=="listserver"&&$formapi->GetProperty("l_listfile")=="")$result=$formapi->SetProperty("m_listfile",$_REQUEST['account_domain'].'/'.$value.'.txt');
																																		
																																		if($formobject=="group"&&$formapi->GetProperty("g_listfile")=="")$result=$result&&$formapi->SetProperty("g_listfile",$_REQUEST['account_domain'].'/'.$value.'.txt');
																																	}

																																}

																																
																																if($value==""&&osversion=="LINUX"&&($variable=='u_respondbetweenfrom'||$variable=='u_respondbetweento'))$value=time();
																																
																																if($variable=='u_emailalias'){
																																	$old=$formapi->GetProperty('u_emailalias');
																																	
																																	if($old!=$value){
																																		$_SESSION['SKIP']['u_mailboxpath']=true;
																																	}

																																}

																																
																																if(!isset($_SESSION['SKIP'][strtolower($variable)])){
																																	$result=$formapi->setProperty($variable,$value);
																																} else {
																																	unset($_SESSION['SKIP'][strtolower($variable)]);
																																}

																															}

																															
																															if(!$result&&$value=='')$result=true;
																														} else
																														if($function)$result=formfunctionvalues($function,$value,false,$name,$message,$item,$error);
																													}

																													
																													if(!$result){
																														
																														if(!$function&&!$error){
																															
																															if($item["ERROR"])$error=$alang[$item["ERROR"]]; else $error=$alang['TStrings_weberror'].':'.$item['NAME'];
																														}

																														return false;
																													}

																													return$result;
																												}

																												
																												function getobjecttitle($object,$label=false,$value=false){
																													global$alang;
																													$result="";
																													switch($object){
																														case"domain":
																															$result=$alang["TFrameAccounts_DomainSheet"];
																															break;
																														case"user":
																															$result=$alang["TDomainAdminForm_User"];
																															break;
																														case"group":
																															$result=$alang["TDomainAdminForm_Group"];
																															break;
																														case"exec":
																															$result=$alang["TDomainAdminForm_Exec"];
																															break;
																														case"notify":
																															$result=$alang["TDomainAdminForm_Notify"];
																															break;
																														case"list":
																															$result=$alang["TDomainAdminForm_List"];
																															break;
																														case"listserv":
																															$result=$alang["TDomainAdminForm_ListServ"];
																															break;
																														case"route":
																															$result=$alang["TDomainAdminForm_Static"];
																															break;
																														case"catalog":
																															$result=$alang["TDomainAdminForm_Catalog"];
																															break;
																														case"remote":
																															$result=$alang["TDomainAdminForm_Remote"];
																															break;
																														default:
																															$result=$object;
																															break;
																												}

																												$result.=($label?" ".$label:
																												" ".$value);
																												return$result;
																											}

																											
																											function prepareglobalvariables($type,$object,$value,$submit=false){
																												global$api,$formtype,$formobject,$formvalue,$formapi,$skindata,$remote,$editform,$alang;
																												
																												if($value=='group_member'){
																													$args=explode('@',$_REQUEST['args']);
																													$domain=$args[1];
																													$name=$args[0];
																													$path=$_SESSION['CONFIGPATH'].$domain;
																													mkdirtree($path);
																													$path.='\/'.$name.".txt";
																													$fp=@fopen($path,'r+');
																													@fclose($fp);
																												}

																												$formtype=$type;
																												
																												if(!$formtype)$formtype='#FORM_TYPE_DEFAULT#';
																												$formobject=$object;
																												$formvalue=$value;
																												
																												if(!$api)$api=createobject("api");
																												
																												if($_SESSION["ACCOUNT"]=='DOMAINADMIN'&&$value)
																												if(!checkdomainaccess($value)){
																													popuperror($alang["ACCESS_ERROR"]);
																													$dieredirect='<body onload="window.open(\'index.html?messageid=TStrings_wa_str6\',\'_top\');"></body>';
																													die($dieredirect);
																													return;
																												}

																												checkaccountright($object,true);
																												switch($object){
																													case"domain":
																														$formapi=createobject("domain");
																														
																														if($type=="add"){
																															
																															if(!$_REQUEST['editform'])unset($_SESSION['datdata']);
																															$val=getstrvalue($api->UTF8ToIDN($_REQUEST["domain_name"]));
																															
																															if(!USEAPIEXT)$formapi->New($val); else $formapi->New_($val);
																															$formapi->ApplyTemplate();
																														}

																														
																														if($type=="edit")$formapi->Open($api->UTF8ToIDN($value));
																														break;
																													case"odbc_mlist":
																														$formapi=createobject("account");
																														$formapi->Open($_REQUEST['cfileid']);
																														break;
																													case"resource":
																														case"group":
																															case"list":
																																case"listserv":
																																	case"route":
																																		case"user":
																																			case"exec":
																																				case"notify":
																																					case"catalog":
																																						$formapi=createobject("account");
																																						
																																						if($type=="add"){
																																							
																																							if(!$_REQUEST["account_domain"])$rdomain=$value; else $rdomain=$_REQUEST["account_domain"];
																																							$raccount=$_REQUEST["account_name"];
																																							
																																							if(!isset($_REQUEST["account_name"]))$raccount=$_REQUEST["group_name"];
																																							
																																							if(!$raccount)$raccount="";
																																							$val=$raccount."@".$api->UTF8ToIDN($rdomain);
																																							
																																							if(!USEAPIEXT)$formapi->New($val); else $formapi->New_($val);
																																							$formapi->SetProperty("u_type",getobjectaccounttype($object));
																																							$formapi->ApplyTemplate();
																																						}

																																						
																																						if($type=="edit")$formapi->Open($value);
																																						break;
																																					case"remote":
																																						$formapi=createobject("remoteaccount");
																																						
																																						if($submit)
																																						if($type=="add"){
																																							
																																							if(!USEAPIEXT)$formapi->New(); else $formapi->New_();
																																							
																																							if(!$_REQUEST["remote_domain"])$_REQUEST["remote_domain"]=$value;
																																							$formapi->SetProperty("RA_DomainString",$_REQUEST["remote_domain"]);
																																							$formapi->SetProperty("RA_LeaveMessageFile",$_REQUEST["remote_name"]."/remote.".$_REQUEST["remote_domain"].".".rand(0,0xFFFF).".dat");
																																							$schedule=$formapi->GetSchedule('ra_schedule');
																																							$schedule->Count=1;
																																							$schedule->SetProperty("s_weekdays_su",true);
																																							$schedule->SetProperty("s_weekdays_mo",true);
																																							$schedule->SetProperty("s_weekdays_tu",true);
																																							$schedule->SetProperty("s_weekdays_we",true);
																																							$schedule->SetProperty("s_weekdays_th",true);
																																							$schedule->SetProperty("s_weekdays_fr",true);
																																							$schedule->SetProperty("s_weekdays_sa",true);
																																							$schedule->SetProperty("s_scheduletype",0);
																																							$schedule->SetProperty("s_every",1200);
																																							$schedule->SetProperty("s_wholeday",true);
																																							$formapi->SetSchedule("ra_schedule",$schedule);
																																							$formapi->ApplyTemplate();
																																						} else {
																																							$value=intval($value);
																																						}

																																						
																																						if($type=="edit"){
																																							$formapi->Open(intval($_REQUEST['value']));
																																						}

																																						break;
																																					case"schedule":
																																						
																																						if($api)$formapi=$api; else $formapi=createobject("api");
																																						
																																						if($_REQUEST['cfileid']=="ra_schedule"){
																																							$remote=createobject("remoteaccount");
																																							$remote->Open($_REQUEST['fileid']);
																																						}

																																						break;
																																					case"accessmode2":
																																						case"accessmode":
																																							
																																							if($api)$formapi=$api; else $formapi=createobject("api");
																																							global$access_mode_prefix;
																																							$access_mode_prefix=$_REQUEST['cfileid'];
																																							break;
																																						case'headerfiles_domain':
																																							$formapi=createobject("domain");
																																							$formapi->Open($_REQUEST['fileid']);
																																							break;
																																						case'headerfiles':
																																							$formapi=createobject("account");
																																							$formapi->Open($_REQUEST['fileid']);
																																							break;
																																						default:
																																							
																																							if($api)$formapi=$api; else $formapi=createobject("api");
																																							break;
																																				}

																																				global$accountids;
																																			}

																																			
																																			function intsetbit($int,$bitmask,$set){
																																				$val=intval($int)&!$bitmask;
																																				
																																				if($set)$val=$val|$bitmask;
																																				return$val;
																																			}

																																			define('date19800101',29221);
																																			define('dayunit',60*60*24);
																																			function convertdelphidatetotime($time){
																																				$fdate=mktime(0,0,0,1,1,1980);
																																				$ftime=$time-date19800101;
																																				return$fdate+(dayunit*$ftime);
																																			}

																																			
																																			function converttimetodelphi($time){
																																				$fdate=($time-mktime(0,0,0,1,1,1980))/dayunit;
																																				return date19800101+$fdate;
																																			}

																																			
																																			function addnull($num){
																																				return($num>=10)?($num):
																																				('0'.$num);
																																			}

																																			
																																			function timetostr($sec){
																																				$s=$sec%60;
																																				$m=(($sec-$s)/60)<60?($sec-$s)/60:
																																				(($sec-$s)/60)%60;
																																				$h=(($sec-$s-$m*60)/3600)<24?($sec-$s-$m*60)/3600:
																																				(($sec-$s-$m*60)/3600)%24;
																																				$d=(($sec-$s-$m*60-$h*3600)/dayunit)<30?($sec-$s-$m*60-$h*3600)/dayunit:
																																				(($sec-$s-$m*60-$h*3600)/dayunit)%dayunit;
																																				$ret=addnull($d).':'.addnull($h).':'.addnull($m).':'.addnull($s);
																																				return$ret;
																																			}

																																			
																																			function strtodate($str){
																																				
																																				if(!$str)return 0;
																																				ereg("([0-9]{4}/([0-9]{1,2})/([0-9]{1,2}))",$str,$regs);
																																				$year=$regs[1];
																																				$month=$regs[2];
																																				$day=$regs[3];
																																				$time=mktime(0,0,0,$month,$day,$year);
																																				return intval(round(converttimetodelphi($time)));
																																			}

																																			
																																			function datetostr($date,$format="Y/m/d"){
																																				
																																				if($date<=0)return"";
																																				$time=convertdelphidatetotime($date);
																																				$value=date($format,$time);
																																				return$value;
																																			}

																																			define('mhsOff',0);
																																			define('mhsOn',1);
																																			define('mhsBoth',2);
																																			define('mhvFrom',0);
																																			define('mhvReplyTo',1);
																																			function getmaillistheaders($user,&$fromaction,&$fromvalue,&$toaction,&$tovalue){
																																				$fromaction=0;
																																				$toaction=0;
																																				$fromvalue="";
																																				$tovalue="";
																																				$setvalue=$user->GetProperty("m_setvalue");
																																				$valuemode=$user->GetProperty("m_valuemode");
																																				$setsender=$user->GetProperty("m_setsender");
																																				$headervalue=$user->GetProperty("m_headervalue");
																																				switch($setvalue){
																																					case mhsOn:
																																						
																																						if($valuemode==mhvFrom){
																																							$fromvalue=$headervalue;
																																							$fromaction=2;
																																						} else {
																																							$tovalue=$headervalue;
																																							$toaction=2;
																																						}

																																						break;
																																					case mhsBoth:
																																						$fromaction=2;
																																						$toaction=2;
																																						$fromvalue=substr($headervalue,0,strpos($headervalue,"|"));
																																						$tovalue=substr($headervalue,strpos($headervalue,"|")+1,strlen($headervalue));
																																						break;
																																			}

																																			switch($setsender){
																																				case mhsOn:
																																					
																																					if($valuemode==mhvFrom)$toaction=1; else $fromaction=1;
																																					break;
																																				case mhsBoth:
																																					$fromaction=1;
																																					$toaction=1;
																																					break;
																																		}

																																	}

																																	
																																	function setmaillistheaders($user,$fromaction,$fromvalue,$toaction,$tovalue){
																																		$setvalue=msOff;
																																		$setsender=mhsOff;
																																		$valuemode=mhvFrom;
																																		$headervalue="";
																																		switch($fromaction){
																																			case 0:
																																				break;
																																			case 1:
																																				$setsender=mhsOn;
																																				$valuemode=mhvReplyTo;
																																				break;
																																			case 2:
																																				$setvalue=mhsOn;
																																				$headervalue=$fromvalue;
																																				break;
																																	}

																																	switch($toaction){
																																		case 0:
																																			break;
																																		case 1:
																																			
																																			if($setsender==mhsOn)$setsender=mhsBoth; else $setsender=mhsOn;
																																			break;
																																		case 2:
																																			
																																			if($setvalue==mhsOn){
																																				$setvalue=mhsBoth;
																																				$fs=$headervalue."|";
																																			} else {
																																				$setvalue=mhsOn;
																																				$fs="";
																																			}

																																			$valuemode=mhvReplyTo;
																																			$headervalue=$fs.$tovalue;
																																			break;
																																}

																																$user->SetProperty("m_setvalue",$setvalue);
																																$user->SetProperty("m_valuemode",$valuemode);
																																$user->SetProperty("m_setsender",$setsender);
																																$user->SetProperty("m_headervalue",$headervalue);
																															}

																															?>