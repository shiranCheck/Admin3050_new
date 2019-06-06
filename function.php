<?php 
	define('SESSIONTIMEOUT',240);
	define('DEFAULTRIGHTS',"UMLG");
	function checksession(){
		global$alang;
		$sess=true;
		
		if(!$_SESSION["EMAIL"])$sess=false; else
		if($_SESSION["TIMESTAMP"]+mktime(SESSIONTIMEOUT/60)<time())$sess=false;
		
		if(!$sess){
			$dieredirect='<body onload="window.open(\'index.html?messageid=TStrings_wa_str6\',\'_top\');"></body>';
			die($dieredirect);
		}

	}

	
	function checkadminaccess($param=false){
		
		if($_SESSION["ACCOUNT"]!='ADMIN'){
			
			if($_SESSION['ACCOUNT']=='GATEWAY'){
				$allowed=array('license','logs','messagequeue','services','sessions','stats');
				
				if(in_array($param,$allowed)){
					return true;
				}

			}

			header("Location: index.html?messageid=TStrings_wa_str6");
			die();
		}

	}

	
	function getstrvalue($value){
		$value=str_replace("'","\'",$value);
		
		if($value)return$value; else return"";
	}

	
	function getlanguagelist(){
		global$lang_settings,$language;
		while(list($index,$value)=each($lang_settings))@$langs.=addselectoption($index,$value[0],$language);
		return$langs;
	}

	
	function getskinlist(){
		global$layout_settings,$layout;
		while(list($index,$value)=each($layout_settings))@$skins.=addselectoption($index,$value[0],$layout);
		return$skins;
	}

	
	function addselectoption($value,$label,$selected){
		$option='<option value="'.$value.'" '.($selected==$value?"selected":
		"").'>'.$label.'</option>';
		return$option;
	}

	
	function getusermailboxpath($mailpath,$usermailboxpath){
		return getfilevarpath($usermailboxpath,$mailpath);
	}

	
	function getdomainvalue($value,$isremote=0){
		$pos=strpos($value,"@");
		
		if($pos)$value=substr($value,$pos+1,strlen($value));
		return$value;
	}

	
	function getaliasvalue($value){
		$pos=strpos($value,"@");
		
		if($pos)$value=substr($value,0,$pos);
		return$value;
	}

	
	function mkdirtree($fpath){
		
		if($fpath!=""&&!file_exists($fpath)){
			$fpath2=str_replace("\\","/",$fpath);
			$exparr=explode("/",$fpath2);
			for($i=0;$i<count($exparr);$i++){
				$dirline.=$exparr[$i]."/";
				
				if(!file_exists($dirline))@mkdir($dirline,0750);
			}

		}

		return true;
	}

	
	function checkapiobject(){
		global$api;
		
		if(!$api)$api=createobject("api");
	}

	
	function isuserright($right){
		
		if($_SESSION['ACCOUNT']=='USER')return false;
		
		if($_SESSION['ACCOUNT']=='GATEWAY'){
			return strtoupper($right)=='D'?true:
			false;
		}

		
		if($_SESSION["RIGHTS"]=="*")return true;
		return(strpos($_SESSION["RIGHTS"],$right)===false)?false:
		true;
	}

	
	function getuserrightsfile(){
		return getusermailboxpath($_SESSION["MAILPATH"],$_SESSION["USERPATH"])."domain.dat";
	}

	
	function parsedomainfile($file,&$rights,&$optionlimits){
		
		if(file_exists($file)){
			$list=file($file);
			
			if(eregi("RIGHTS=(.*)",$list[0],$arr))$rights=strtoupper($arr[1]);
			for($i=0;$i<count($list);$i++){
				
				if(eregi("OPTION=(.*)[:](.*)",$list[$i],$option))$optionlimits[strtolower($option[1])]=$option[2];
			}

		}

	}

	
	function getuserrights(&$rights,&$optionlimits){
		
		if($_SESSION["ACCOUNT"]=='ADMIN')return"*";
		$rights=DEFAULTRIGHTS;
		$optionlimits=array();
		parsedomainfile($_SESSION["CONFIGPATH"]."domain.dat",$rights,$optionlimits);
		parsedomainfile(getuserrightsfile(),$rights,$optionlimits);
	}

	
	function setsystemcookie($cookie,$value){
		setcookie($cookie,$value,time()+60*60*24*7);
		$_COOKIE[$cookie]=$value;
	}

	
	function getfilevarpath($filename,$relative){
		
		if(!($filename[1]==":")&&!($filename[0]=="\\")&&!($filename[0]=="/"))return$relative.$filename; else return$filename;
	}

	
	function getdomainslimits(){
		global$oDomain;
		$oDomain=createobject("domain");
		$limits=array();
		$domains=getdomainlist();
		for($i=0;$i<count($domains);$i++){
			$dom=$domains[$i]["DOMAIN"];
			$oDomain->Open($dom);
			$limit=$oDomain->GetProperty("d_accountnumber");
			
			if($limit)$limits[$dom]=$limit;
		}

		return$limits;
	}

	
	function logingatewayuser($sid,$hash,&$message=''){
		
		if(session_id()!=$_REQUEST['sid']){
			session_destroy();
			session_id($_REQUEST['sid']);
			session_start();
		}

		$auth=($_SESSION['hash']==$_REQUEST['hash']);
		unset($_SESSION['hash']);
		$email=$_SESSION['EMAIL'];
		$language=$_SESSION['LANGUAGE'];
		$account='USER';
		$interface='user';
		logoutuser();
		session_destroy();
		session_id(uniqid());
		session_start();
		setsystemcookie('LANG',$language);
		setsystemcookie('GUI',@$interface);
		setsystemcookie('SKIN','default');
		
		if($auth&&$account){
			global$api,$domain;
			checkdomainobjects();
			$_SESSION["EMAIL"]=$email;
			$_SESSION["DOMAIN"]=substr($email,strpos($email,'@')+1);
			$_SESSION["ACCOUNT"]=$account;
			$_SESSION["TIMESTAMP"]=time();
			$_SESSION["INSTALLPATH"]=$api->GetProperty("c_installpath");
			$_SESSION["SPAMPATH"]=$api->GetProperty("c_spampath");
			
			if(!$_SESSION['SPAMPATH']){
				$_SESSION['SPAMPATH']=$_SESSION["INSTALLPATH"].'spam/';
			}

			$_SESSION['CALENDARPATH']=$api->GetProperty("c_calendarpath");
			
			if(!$_SESSION['CALENDARPATH']){
				$_SESSION['CALENDARPATH']=$_SESSION["INSTALLPATH"].'calendar/';
			}

			$_SESSION["GUI"]=$interface;
			$_SESSION["TEMPPATH"]=getfilevarpath($api->GetProperty("C_System_Storage_Dir_TempPath"),$_SESSION["INSTALLPATH"]);
			$_SESSION["MAILPATH"]=getfilevarpath($api->GetProperty("C_System_Storage_Dir_MailPath"),$_SESSION["INSTALLPATH"]);
			$_SESSION["CONFIGPATH"]=$api->GetProperty("c_configpath");
			$_SESSION['RIGHTS']='Q';
			getAccessRights();
		}

		return$auth;
	}

	
	function sendloginlink($email,$language,&$msg,&$refill){
		global$alang;
		$captcha=$_REQUEST['captcha_words'];
		$captcha=strtolower(str_replace(' ','',$captcha));
		$captchas=strtolower(str_replace(' ','',$_SESSION['captcha']));
		
		if($captcha!=$captchas){
			$msg='TStrings_wa_gateway_captcha_invalid';
			$refill=true;
			return false;
		}

		$_SESSION['captcha']='';
		$domain=substr($email,strpos($email,'@')+1);
		$hash=uniqid();
		$skindata['intro']=$alang['gateway_login_intro'];
		$uri=($_SERVER['HTTPS']=='ON'?'https://':
		'http://').$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$param='?sid='.session_id().'&gauth=1&glogin=1&hash='.$hash;
		$_SESSION['hash']=$hash;
		$_SESSION['EMAIL']=$email;
		$_SESSION['LANGUAGE']=$language;
		$link=$uri.$param;
		$message=$alang['TStrings_wa_gateway_login_body']."\n".$link."\n";
		$msgid=sprintf("Message-ID: <%s@%s>%s",uniqid(),substr($email,strrpos($email,'@')+1),"\n");
		$mime="MIME-Version:1.0\n";
		$encoding="Content-Transfer-Encoding:7bit\n";
		$api=createobject("api");
		$account=createobject("account");
		$dom=createobject("domain");
		
		if(!$dom->Open($domain)){
			$msg='TStrings_wa_gateway_domain_exist';
			return false;
		}

		
		if($account->Open($email)){
			$msg='TStrings_wa_gateway_user_exist';
			return false;
		}

		$mailer='IceWarp Mailer '.$api->getProperty('C_Version');
		$configPath=$api->GetProperty('C_ConfigPath');
		$configPath=$configPath.'_webmail\/server.xml';
		$xml=simplexml_load_file($configPath);
		$smtp=$xml->item->smtpserver?strval($xml->item->smtpserver):
		'127.0.0.1';
		$port=$xml->item->smtpport?strval($xml->item->smtpport):
		'25';
		require_once(SHAREDLIB_PATH.'system.php');
		slSystem::import('mail/mail');
		$mail=new slMail();
		$mail->Host=$smtp;
		$mail->XMailer=$mailer;
		$mail->Port=$port;
		$mail->setFrom('no-reply@'.$domain);
		$mail->setBody($message);
		$mail->addTo($email);
		$mail->setSubject($alang['TStrings_wa_gateway_login_subject']);
		try{
			$result=$mail->send();
		}

		catch(Exception$e){
		}

		
		if(!$result){
			$msg="TStrings_wa_gateway_message_not_sent";
			return false;
		}

		$msg="TStrings_wa_gateway_login_msg";
		return true;
	}

	
	function loginuser($username,$password,$interface,$method="",&$message){
		global$SERVER_NAME;
		$user=createobject("account");
		$auth=$user->AuthenticateUserHash($username,$password,$SERVER_NAME,$method);
		
		if($auth){
			$auth=$user->ValidateUser();
			
			if(!$auth){
				$message='TStrings_wa_account_disabled';
			}

		}

		
		if($auth){
			$admin=$user->GetProperty("u_admin");
			$domainadmin=$user->GetProperty("u_domainadmin");
			$gatewayadmin=$user->GetProperty("u_gatewayadmin");
			
			if($admin)$account='ADMIN'; else
			if($domainadmin)$account='DOMAINADMIN'; else
			if($gatewayadmin){
				$account='GATEWAY';
				$interface='gateway';
			} else {
				$account='USER';
				$interface='user';
			}

		}

		logoutuser();
		setsystemcookie('LANG',@$_REQUEST['language']);
		setsystemcookie('GUI',@$interface);
		setsystemcookie('CIPHER',@$_REQUEST['xcipher']);
		setsystemcookie('SKIN',@$_REQUEST['skin']);
		
		if($auth&&$account){
			global$api,$domain;
			checkdomainobjects();
			$_SESSION["EMAIL"]=$user->EmailAddress;
			$_SESSION["USERPATH"]=$user->GetProperty("u_mailboxpath");
			$_SESSION["DOMAIN"]=$user->Domain;
			$_SESSION["ACCOUNT"]=$account;
			$_SESSION["TIMESTAMP"]=time();
			$_SESSION["INSTALLPATH"]=$api->GetProperty("c_installpath");
			$_SESSION["SPAMPATH"]=$api->GetProperty("c_spampath");
			
			if(!$_SESSION['SPAMPATH'])$_SESSION['SPAMPATH']=$_SESSION["INSTALLPATH"].'spam/';
			$_SESSION['CALENDARPATH']=$api->GetProperty("c_calendarpath");
			
			if(!$_SESSION['CALENDARPATH'])$_SESSION['CALENDARPATH']=$_SESSION["INSTALLPATH"].'calendar/';
			$_SESSION["GUI"]=$interface;
			$_SESSION["TEMPPATH"]=getfilevarpath($api->GetProperty("C_System_Storage_Dir_TempPath"),$_SESSION[INSTALLPATH]);
			$_SESSION["MAILPATH"]=getfilevarpath($api->GetProperty("C_System_Storage_Dir_MailPath"),$_SESSION[INSTALLPATH]);
			$_SESSION["CONFIGPATH"]=$api->GetProperty("c_configpath");
			
			if($account=='DOMAINADMIN'){
				getuserrights($rights,$optionlimits);
				$_SESSION["RIGHTS"]=$rights;
				$_SESSION["LIMIT"]=$optionlimits;
				$_SESSION["ACCOUNTLIMIT"]=getdomainslimits();
			}

			getAccessRights();
		}

		return$auth;
	}

	
	function logoutuser(){
		session_unset();
	}

	
	function checkuid($uid){
		
		if(time()>substr($uid,13)+300)return 0;
		return 1;
	}

	
	function popuperror($error,$die=true){
		echo'<script>alert("'.htmlspecialchars($error).'")</script>';
		
		if($die)die();
	}

	
	function parseTime($val,$bSet=false){
		
		if($bSet){
			
			if((($pos=strpos($val,":"))!==false)){
				$hours=substr($val,0,$pos);
				$minutes=substr($val,$pos+1);
				$val=$hours*3600+$minutes*60;
			} else {
				$val=$val*60;
			}

		} else {
			$arr['hours']=floor($val/3600);
			$val-=$arr['hours']*3600;
			$arr['minutes']=floor($val/60);
			$val-=$arr['minutes']*60;
			$arr['sec']=$val;
			
			if($arr['sec']==59){
				$arr['sec']=0;
				$arr['minutes']+=1;
			}

			
			if(isset($arr['hours'])&&isset($arr['minutes'])){
				
				if($arr['hours']<10)$arr['hours']='0'.$arr['hours'];
				
				if($arr['minutes']<10)$arr['minutes']='0'.$arr['minutes'];
				$val=$arr['hours'].':'.$arr['minutes'];
			} else {
				
				if($arr['minutes']){
					$val=$arr['minutes'];
				}

			}

		}

		return$val;
	}

	
	function getOSname(){
		
		if(osvresion==1)$return="linux";
		
		if(osversion==0)$return="windows";
		return$return;
	}

	
	function createobject($object){
		switch(strtolower($object)){
			case"api":
				
				if(!USEAPIEXT)return new COM("IceWarpServer.APIObject"); else {
					
					if($_SESSION&&($email=$_SESSION['EMAIL'])){
						$email.='/WebAdmin';
					} else {
						$email='WebAdmin';
					}

					$api=IcewarpAPI::instance($email);
					return$api;
				}

				case"domain":
					
					if(!USEAPIEXT)return new COM("IceWarpServer.DomainObject"); else return new MerakDomain();
					case"account":
						
						if(!USEAPIEXT)return new COM("IceWarpServer.AccountObject"); else return new MerakAccount();
						case"remoteaccount":
							
							if(!USEAPIEXT)return new COM("IceWarpServer.RemoteAccountObject"); else return new MerakRemoteAccount();
							case"schedule":
								
								if(!USEAPIEXT)return new COM("IceWarpServer.ScheduleObject"); else return new MerakSchedule();
								case"statistics":
									
									if(!USEAPIEXT)return new COM("IceWarpServer.StatisticsObject"); else return new MerakStatistics();
									case"gwapi":
										return new MerakGWAPI();
								}

							}

							
							function securepath($path){
								
								if(ereg("(\.\.)|(:)",$path)||$path[0]=="/"||$path[0]=="\\")$path="";
								
								if(!$path)return false;
								return$path;
							}

							
							function convertaccounts(&$items){
								for($i=0;$i<count($items);$i++){
									$items[$i]["SORT"]=convertitem($items[$i]["SORT"]);
									$items[$i]["LABEL"]=convertitem($items[$i]["LABEL"]);
									$items[$i]["NAME"]=convertitem($items[$i]["NAME"]);
								}

							}

							
							function convertdomains(&$items){
								for($i=0;$i<count($items);$i++){
									$items[$i]["DESC"]=convertitem($items[$i]["DESC"]);
									$items[$i]["DOMAIN"]=convertitem($items[$i]["DOMAIN"]);
								}

							}

							
							function convertitem($item){
								global$encoding;
								return str_replace("'","\'",$item);
							}

							
							function checkwizardaccessrights($wizard){
								$DAWizards=array(0=>'wizard.account.php');
								switch($_SESSION['ACCOUNT']){
									case"ADMIN":
										return$wizard;
										break;
									case"DOMAINADMIN":
										
										if(!in_array(strtolower($wizard),$DAWizards))return false;
										switch($wizard){
											case'wizard.account.php':
												
												if(!isuserright("U"))return false;
												break;
										}

										return$wizard;
										break;
								}

								return false;
							}

							
							function getIMAPMailboxes($server,$port,$username,$password){
								$attr='';
								
								if($port==993)$attr.='/ssl/novalidate-cert';
								$port=$port?(':'.$port):
								'';
								
								if($port==':110')$port.='/pop3/notls';
								$server='{'.$server.$port.$attr.'}';
								$imap=@imap_open($this->server,$username,$password);
								
								if($imap===false)return false;
								$list=imap_getmailboxes($imap,$server,'INBOX');
								return$list;
							}

							
							function getMailboxes($formapi){
								global$mailboxPath;
								$path=$formapi->getProperty("U_MailBoxPath");
								$mailboxes=array();
								$api=createobject("API");
								$mailboxPath=$api->GetProperty("C_System_Storage_Dir_MailPath").$path;
								$itm->name="INBOX";
								$itm->delimiter='/';
								$itm->rights=0;
								$mailboxes[]=$itm;
								$mailboxes=array_merge($mailboxes,mailboxDirectories($mailboxPath));
								return$mailboxes;
							}

							
							function mailboxDirectories($path){
								global$mailboxPath;
								$mailboxes=array();
								$items=glob($path."*",GLOB_ONLYDIR);
								
								if($items)foreach($items as$item){
									$itempath=$item.'/';
									$firstChar=$item{
										strlen($path)}

									;
									$item=substr($item,strlen($mailboxPath));
									
									if($firstChar!='~'&&strtolower($item)!='inbox'){
										unset($itm);
										$itm->name=$item;
										$itm->delimiter='/';
										$itm->rights=255;
										$mailboxes[]=$itm;
									}

									
									if(!is_array($mailboxes))$mailboxes=array();
									
									if(!strpos($itempath,'~'))$mailboxes=array_merge($mailboxes,mailboxDirectories($itempath));
								}

								return$mailboxes;
							}

							
							function explode_j($sep,$string){
								$return=explode($sep,$string);
								unset($return[count($return)-1]);
								return$return;
							}

							define(E_DEFAULT,'');
							define(E_FAILURE,-1);
							define(E_LICENSE,-2);
							define(E_PARAMS,-3);
							define(E_PATH,-4);
							define(E_CONFIG,-5);
							define(E_PASSWORD,-6);
							define(E_CONFLICT,-7);
							define(E_INVALID,-8);
							function getErrorMsg($api){
								global$alang;
								switch($api->LastErr){
									case E_LICENSE:
										$errorMsg=$alang['TStrings_cerrusers'];
										break;
									case E_PARAMS:
										$errorMsg=$alang['TStrings_cbenamealias'];
										break;
									case E_CONFIG:
										$errorMsg=$alang['TStrings_cbenamealias'];
										break;
									case E_PASSWORD:
										$mapi=createobject('api');
										$policy['enable']=$mapi->GetProperty('C_Accounts_Policies_Pass_Enable');
										$policy['minlength']=$mapi->GetProperty('C_Accounts_Policies_Pass_MinLength');
										$policy['digits']=$mapi->GetProperty('C_Accounts_Policies_Pass_Digits');
										$policy['alpha']=$mapi->GetProperty('C_Accounts_Policies_Pass_Alpha');
										$policy['nonalphanum']=$mapi->GetProperty('C_Accounts_Policies_Pass_NonAlphaNum');
										$policy['useralias']=$mapi->GetProperty('C_Accounts_Policies_Pass_UserAlias');
										$message.='<ul>';
										
										if($policy['minlength']){
											$message.='<li>'.sprintf($alang['TActiveSyncPoliciesForm_MinPassLen'].$policy['minlength'])."</li>";
										}

										
										if($policy['digits']){
											$message.='<li>'.sprintf($alang['TConfigForm_SpecLab'].$policy['digits']).'</li>';
										}

										
										if($policy['alpha']){
											$message.='<li>'.sprintf($alang['TConfigForm_AlphaCharsL'].$policy['alpha']).'</li>';
										}

										
										if($policy['nonalphanum']){
											$message.='<li>'.sprintf($alang['TConfigForm_NonAlpha'].$policy['nonalphanum']).'</li>';
										}

										
										if($policy['useralias']){
											$message.='<li>'.$alang['TConfigForm_PassUserAlias'].'</li>';
										}

										$message.='</ul>';
										$msg=explode('|',$alang['TStrings_cmailboxpasswordpolicy']);
										$msg=$msg[0];
										return$msg.'<br/>'.$message;
										break;
									case E_CONFLICT:
										$errorMsg=$alang['TStrings_cmailboxpassword'];
										break;
									case E_INVALID:
										$errorMsg=$alang['TStrings_cinvalidchar'];
										break;
									case E_FAILURE:
										case E_PATH:
											case E_DEFAULT:
												default:
													$errorMsg=$alang['TStrings_cparamserror'];
													break;
										}

										$errorMsg=explode("|",$errorMsg);
										$errorMsg=$errorMsg[0];
										return$errorMsg;
									}

									
									function getAccessMode($service,&$value){
										global$formapi,$api;
										switch(strtolower($service)){
											case'av_general':
												$mode=$api->GetProperty("C_".$service."_ProcessingMode");
												
												if($formapi->EmailAddress)$value=$formapi->GetProperty("U_AVSupport");
												$value=$api->GetProperty("C_AV_General_IntegratedAV")==0?0:
													$value;
													break;
												case'as_challenge':
													$mode=$api->GetProperty("C_".$service."_ProcessingMode");
													$dat=parsedatfile('spam.dat','spam2');
													
													if($formapi->EmailAddress)$value=$formapi->GetProperty("U_QuarantineSupport");
													$value=$dat['SpamChallenge']==0?0:
														$value;
														break;
													case'as_general':
														$mode=$api->GetProperty("C_".$service."_ProcessingMode");
														
														if($formapi->EmailAddress)$value=$formapi->GetProperty("U_ASSupport");
														$value=$api->GetProperty("C_AS_General_Enable")==0?0:
															$value;
															break;
														case'gw_general':
															
															if($formapi->EmailAddress)$value=$formapi->GetProperty("U_GWSupport");
															$mode=$api->GetProperty("C_".str_replace("_general","",$service)."_ProcessingMode");
															$value=!$api->GetProperty("C_".$service."_Disable")==0?0:
																$value;
																break;
															case'im_general':
																
																if($formapi->EmailAddress)$value=$formapi->GetProperty("U_IMSupport");
																$mode=$api->GetProperty("C_".str_replace("_general","",$service)."_ProcessingMode");
																$value=!$api->GetProperty("C_".$service."_Disable")==0?0:
																	$value;
																	break;
																case'syncml':
																	
																	if($formapi->EmailAddress)$value=$formapi->GetProperty("U_SyncMLSupport");
																	$mode=$api->GetProperty("C_SyncML_ProcessingMode");
																	$dat=parsedatfile('syncml/config/settings.xml','syncml');
																	$value=(!$dat['Disabled'])==0?0:
																		$value;
																		break;
																	case'pop3':
																		case'imap':
																			case'smtp':
																				case'webmail':
																					return true;
																					break;
																				case'sip':
																					
																					if($formapi->EmailAddress)$value=$formapi->GetProperty("U_SIPSupport");
																					$mode=$api->GetProperty("C_System_Services_SIP_Mode");
																					$value=$api->GetProperty("C_System_Services_SIP_Enable")==0?0:
																						$value;
																						break;
																					case'sms':
																						
																						if($formapi->EmailAddress)$value=$formapi->GetProperty("U_SMSSupport");
																						$mode=$api->GetProperty("C_SMSService_ProcessingMode");
																						$value=$api->GetProperty("C_SMSService_Active")==0?0:
																							$value;
																							break;
																						case'ftp':
																							
																							if($formapi->EmailAddress)$value=$formapi->GetProperty("U_FTPSupport");
																							$mode=$api->GetProperty("C_FTPService_ProcessingMode");
																							$value=$api->GetProperty("C_FTPService_Active")==0?0:
																								$value;
																								break;
																							case'activesync':
																								
																								if($formapi->EmailAddress)$value=$formapi->GetProperty("U_ActiveSync");
																								$mode=$api->GetProperty("C_ActiveSync_ProcessingMode");
																								break;
																							case'webdav':
																								
																								if($formapi->EmailAddress)$value=$formapi->GetProperty("U_WebDAVSupport");
																								$mode=$api->GetProperty("C_WebDAV_ProcessingMode");
																								break;
																							case'desktop':
																								case'connector':
																									
																									if($formapi->EmailAddress){
																										$dom=createobject('domain');
																										$dom->Open($formapi->Domain);
																										return$dom->GetProperty('D_Client_'.$service);
																									} else {
																										return 1;
																									}

																									break;
																						}

																						switch($mode){
																							case 1:
																								return true;
																								case 0:
																									
																									if(!$formapi->EmailAddress)$value=1;
																									case 3:
																										return false;
																										case 5:
																											
																											if($formapi->EmailAddress)return false; else return true;
																											case 7:
																												break;
																											case 9:
																												
																												if($formapi->EmailAddress)return true; else {
																													$value=true;
																													return false;
																												}

																										}

																										return true;
																									}

																									
																									function getAccessRights(){
																										$cached=array();
																										$list=glob('xml/interface/'.strtolower($_SESSION['GUI']).'/*');
																										foreach($list as$item){
																											
																											if(is_file($item)){
																												$itm=str_replace('xml/interface/'.strtolower($_SESSION['GUI']).'/','',$item);
																												$itm=str_replace('.xml','',$itm);
																												
																												if(!$cached[$itm]){
																													$cached[$itm]=1;
																													$_SESSION['ACCESS'][$_SESSION['ACCOUNT']][]=$itm;
																												}

																											}

																										}

																										$list=glob('xml/interface/'.strtolower($_SESSION['GUI']).'/dialog/*');
																										foreach($list as$item){
																											
																											if(is_file($item)){
																												$itm=str_replace('xml/interface/'.strtolower($_SESSION['GUI']).'/dialog/','',$item);
																												$itm=str_replace('.xml','',$itm);
																												
																												if(!$cached[$itm]){
																													$cached[$itm]=1;
																													$_SESSION['ACCESS'][$_SESSION['ACCOUNT']][]=$itm;
																												}

																											}

																										}

																									}

																									?>