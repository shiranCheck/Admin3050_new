<?php 
	@require_once'inc/syntax/cfvariablefunctions.php';
	function formfunctionvalues($function,&$value,$get,$name,&$message,$option=false,&$error=false){
		global$formapi,$formtype,$formobject,$formvalue,$alang,$gObj,$av,$datdata,$datfile;
		$result=true;
		switch(strtolower($function)){
			case"learning_rule_process_as":
				$arr=explode("|",$alang['TASQueueItemForm_QueueList']);
				
				if($option['ATTRIBUTES']['VALUE']){
					$values=explode("|",$option['ATTRIBUTES']['VALUE']);
					$values=array_flip($values);
					$value=$values[$value];
				}

				$value=$arr[$value];
				break;
			case"domain_name":
				
				if($get)$value=$formvalue; else {
					$api=createobject("API");
					
					if($api->UTF8ToIDN($value)!=$formapi->Name){
						$api->renameDomain($formapi->Name,$value);
						
						if($_COOKIE['domainv']==$formapi->Name){
							SetCookie('domainv',$value);
						}

						$result=$api->Save();
					} else {
						$result=1;
					}

				}

				break;
			case"domain_accounts":
				
				if($get)$value=$formapi->GetAccountCount();
				break;
			case"account_domain":
				
				if($get)$value=getdomainvalue($formvalue);
				
				if(!$value)$value=$_COOKIE['domainv'];
				break;
			case"remote_realname":
				
				if($get)$value=!$formapi->GetProperty("ra_nonames"); else $formapi->SetProperty("ra_nonames",!$value);
				break;
			case"domain_list":
				
				if($get){
					$list=getdomainlist();
					for($i=0;$i<count($list);$i++)$value.=($i?"|":
						"").$list[$i][DOMAIN];
					}

					break;
				case"domain_ipbinding":
					
					if($get)$value=$formapi->IPAddress; else $formapi->IPAddress=$value;
					break;
				case"user_password":
					
					if($get){
						$value=false;
						
						if($formtype!='add')$value=$formapi->GetProperty("u_password");
					} else {
						$result=$_REQUEST["user_confirmpassword"]==$_REQUEST["user_password"];
						
						if($result&&$value!="*")$result=$formapi->SetProperty("u_password",$value);
						
						if(!$result&&$name=='user_password')$error=$alang["TStrings_ccheckconfirm"];
					}

					break;
				case"user_permissions":
					
					if($get){
						$value=0;
						
						if($formapi->GetProperty("u_admin"))$value=2; else
						if($formapi->GetProperty("u_domainadmin"))$value=1;
					} else {
						$formapi->SetProperty("u_admin",0);
						$formapi->SetProperty("u_domainadmin",0);
						
						if($value==2)$formapi->SetProperty("u_admin",1); else
						if($value==1)$formapi->SetProperty("u_domainadmin",1);
					}

					break;
				case"user_expireson":
					
					if($get){
						$value=$formapi->GetProperty("u_accountvalidtill");
						$value=datetostr($value);
					} else {
						$value=strtodate($value);
						$formapi->SetProperty("u_accountvalidtill",$value);
					}

					break;
				case"user_mailboxsize":
					
					if($get)$value=$formapi->GetProperty("u_maxboxsize")/1024; else $formapi->SetProperty("u_maxboxsize",$value*1024);
					break;
				case"u_accounttype":
					$type=$formapi->GetProperty("u_accounttype");
					$api=createobject("api");
					
					if($get)$value=$type; else {
						$result=$result&&$formapi->SetProperty("u_accounttype",$value);
					}

					break;
				case"domain_expireson":
					
					if($get){
						$value=$formapi->GetProperty("d_expireson");
						$value=datetostr($value);
					} else {
						$value=strtodate($value);
						$formapi->SetProperty("d_expireson",$value);
					}

					break;
				case"domain_type":
					
					if($get){
						$value=$formapi->GetProperty("d_type");
					} else {
						$formapi->SetProperty("d_type",$value);
					}

					break;
				case"domain_diskquota":
					
					if($get)$value=$formapi->GetProperty("d_diskquota")/1024; else $formapi->SetProperty("d_diskquota",$value*1024);
					break;
				case"domain_mailboxsize":
					
					if($get)$value=$formapi->GetProperty("d_usermailbox")/1024; else $formapi->SetProperty("d_usermailbox",$value*1024);
					break;
				case"list_rights":
					$rights=Array(0=>$alang["TStrings_mdefault"],1=>$alang["TStrings_mread"]." ".$alang["TStrings_mpost"],2=>$alang["TStrings_mread"]." ".$alang["TStrings_mpost"]." ".$alang["TStrings_mdigest"],3=>$alang["TStrings_mread"],4=>$alang["TStrings_mread"]." ".$alang["TStrings_mdigest"],5=>$alang["TStrings_mpost"],''=>$alang["TStrings_mdefault"]);
					
					if($get){
						
						if($value==0){
							$value=$alang["TStrings_mdefault"];
						} else {
							$val=decimal2binary($value,3);
							$value=($val[0]?$alang["TStrings_mread"]:
								'').($val[1]?' '.$alang["TStrings_mpost"]:
									'').($val[2]?' '.$alang["TStrings_mdigest"]:
										'');
									}

								}

								break;
							case"list_passprot":
								
								if($get){
									$value=0;
									$pass=$formapi->GetProperty("m_moderated");
									
									if($pass){
										++$value;
										$server=$formapi->GetProperty("m_servermoderated");
										
										if($server)++$value;
									}

								} else {
									$formapi->SetProperty("m_moderated",false);
									$formapi->SetProperty("m_servermoderated",false);
									switch($value){
										case 2:
											$formapi->SetProperty("m_servermoderated",true);
											case 1:
												$formapi->SetProperty("m_moderated",true);
												break;
										}

									}

									break;
									case"list_fromaction":
										case"list_toaction":
											case"list_fromvalue":
												case"list_tovalue":
													
													if($get){
														getmaillistheaders($formapi,$fromaction,$fromvalue,$toaction,$tovalue);
														switch($function){
															case"list_fromaction":
																$value=$fromaction;
																break;
															case"list_toaction":
																$value=$toaction;
																break;
															case"list_fromvalue":
																$value=$fromvalue;
																break;
															case"list_tovalue":
																$value=$tovalue;
																break;
														}

													} else {
														global$listfromaction,$listfromvalue,$listtoaction,$listtovalue;
														switch($function){
															case"list_fromaction":
																$listfromaction=$value;
																break;
															case"list_toaction":
																$listtoaction=$value;
																break;
															case"list_fromvalue":
																$listfromvalue=$value;
																break;
															case"list_tovalue":
																$listtovalue=$value;
																setmaillistheaders($formapi,$listfromaction,$listfromvalue,$listtoaction,$listtovalue);
																break;
														}

													}

													break;
													case"c_system_conn":
														$value=0;
														break;
													case"c_system_conn_labels":
														$value=$alang[TConfigForm_NetworkConnect]."|".$alang[TConfigForm_DialOnDemand]."|".$alang[TConfigForm_RASConnect];
														break;
													case"av_time":
														
														if($get){
															$val=$formapi->GetProperty("C_AV_General_UpdateTime");
															$valHour=floor($val/256);
															$valMin=$val-($valHour*256);
															
															if($valMin>59){
																$valHour+=floor($valMin/60);
																$valMin=$valMin % 60;
															}

															$valHour %=24;
															
															if($valMin<10)$valMin="0".$valMin;
															$value=$valHour.":".$valMin;
														} else {
															$dotIndex=strpos($value,":");
															
															if($dotIndex===false)$dotIndex=strlen($value);
															$valHour=substr($value,0,$dotIndex);
															$valMin=substr($value,$dotIndex+1);
															
															if($valMin>59){
																$valHour+=floor($valMin/60);
																$valMin=$valMin % 60;
															}

															$valHour %=24;
															$value=$valHour*256+$valMin;
															$formapi->SetProperty("C_AV_General_UpdateTime",$value);
														}

														break;
													case"odbc_mlist":
														case"odbc_dbm_source":
															case"odbc_dbm_dest":
																case"odbc_gw":
																	case"odbc_as":
																		case"odbc_cr":
																			case"odbc_storage":
																				case"odbc_logging":
																					case"odbc_backup_as":
																						case"odbc_backup_gw":
																							case"odbc_backup_accounts":
																								case"odbc_migrate_source":
																									case"odbc_migrate_dest":
																										switch(strtolower($function)){
																											case'odbc_storage':
																												$varname="C_System_Storage_Accounts_ODBCConnString";
																												$source="API";
																												$service=0;
																												break;
																											case'odbc_mlist':
																												$varname="m_odbc";
																												$source="API";
																												break;
																											case'odbc_dbm_source':
																												$varname="C_System_Storage_Accounts_ODBCConnString";
																												$source="API";
																												break;
																											case'odbc_dbm_dest':
																												$varname="C_System_Storage_Accounts_ODBCConnString";
																												$source="API";
																												break;
																											case'odbc_logging':
																												$service=1;
																												$varname="C_System_Logging_General_ODBCLogConn";
																												$source="API";
																												break;
																											case'odbc_gw':
																												$service=2;
																												$varname="ConnectionString";
																												$file="calendar.dat";
																												$source="FILE";
																												break;
																											case'odbc_as':
																												$varname="SpamChallengeConnection";
																												$file="spam.dat";
																												$source="FILE";
																												$service=3;
																												break;
																											case'odbc_backup_as':
																												$varname="C_System_Tools_Backup_DB_AS";
																												$source="API";
																												break;
																											case'odbc_backup_gw':
																												$varname="C_System_Tools_Backup_DB_GW";
																												$source="API";
																												break;
																											case'odbc_backup_accounts':
																												$varname="C_System_Tools_Backup_DB_Accounts";
																												$source="API";
																												break;
																											case'odbc_backup_accounts':
																												$varname="C_System_Tools_Backup_DB_Accounts";
																												$source="API";
																												break;
																											case'odbc_backup_accounts':
																												$varname="C_System_Tools_Backup_DB_Accounts";
																												$source="API";
																												break;
																										}

																										
																										if($get){
																											
																											if(!$av[strtolower($function)]){
																												
																												if($source=="API")$val=$formapi->GetProperty($varname); else $val=$_SESSION['datdata'][$file]['data'][$varname];
																												$val=explode(";",$val);
																												$val_backup=$val[count($val)-1];
																												$pom=explode("|",$val_backup,2);
																												$val_backup=$pom[1];
																												$val[count($val)-1]=$pom[0];
																												$val_backup=explode("|",$val_backup);
																												$api=createobject("api");
																												$val[2]=$api->ManageConfig('dbpassword','0',$val[2]);
																												$val_backup[2]=$api->ManageConfig('dbpassword','0',$val_backup[2]);
																												$av[strtolower($function)]['primary']=$val;
																												$av[strtolower($function)]['secondary']=$val_backup;
																												$av[strtolower($function)]['service']=$service;
																											}

																											
																											if(substr_count(strtolower($name),"b")>0){
																												$dest='secondary';
																												$name=str_replace("b",'',strtolower($name));
																											} else $dest='primary';
																											$value=$av[strtolower($function)][$dest][$name];
																											
																											if($name==5){
																												
																												if($value)$value=$value-1;
																											}

																											
																											if($name=='service')$value=$service;
																										} else {
																											
																											if(substr_count(strtolower($name),"b")>0){
																												$dest='secondary';
																												$name=str_replace("b",'',strtolower($name));
																											} else $dest='primary';
																											
																											if($name==5){
																												
																												if($value<4)$value=$value+1;
																											}

																											$av[strtolower($function)][$dest][$name]=$value;
																											
																											if(count($av[strtolower($function)]['primary'])==6&&count($av[strtolower($function)]['secondary'])==4){
																												$values=array(0=>0,3=>1,1=>2,2=>3,4=>4,5=>5);
																												$val_primary=$val_secondary='';
																												$c=0;
																												$api=createobject("api");
																												$av[strtolower($function)]['primary'][2]=$api->ManageConfig('dbpassword','1',$av[strtolower($function)]['primary'][2]);
																												$av[strtolower($function)]['secondary'][2]=$api->ManageConfig('dbpassword','1',$av[strtolower($function)]['secondary'][2]);
																												foreach($av[strtolower($function)]['primary']as$key=>$var){
																													$val_primary.=$av[strtolower($function)]['primary'][$values[$key]].($c++==5?"":
																													";");
																												}

																												$c=0;
																												foreach($av[strtolower($function)]['secondary']as$key=>$var)$val_secondary.=$av[strtolower($function)]['secondary'][$values[$key]].($c++==3?"":
																												"|");
																												$val_secondary.=$av[strtolower($function)]['primary'][6];
																												
																												if($source=="API")$formapi->setProperty($varname,$val_primary."|".$val_secondary); else $val=$_SESSION['datdata'][$file]['data'][$varname];
																												$_SESSION['datdata'][$file]['data'][$varname]=$val_primary."|".$val_secondary;
																											}

																											
																											if(count($av[strtolower($function)]['primary'])==6){
																												
																												if($function=='odbc_backup_gw'||$function=='odbc_backup_as'||$function=='odbc_backup_accounts'){
																													$values=array(0=>0,3=>1,1=>2,2=>3,4=>4,5=>5);
																													$c=0;
																													foreach($av[strtolower($function)]['primary']as$key=>$var){
																														$val_primary.=$av[strtolower($function)]['primary'][$values[$key]].($c++==5?"":
																														";");
																													}

																													
																													if($source=="API")$formapi->setProperty($varname,$val_primary); else $val=$_SESSION['datdata'][$file]['data'][$varname];
																													$_SESSION['datdata'][$file]['data'][$varname]=$val_primary;
																												}

																											}

																										}

																										break;
																										case"smtp_maxmsgsize":
																											
																											if($get)$value=floor($formapi->GetProperty("C_Mail_SMTP_Delivery_MaxMsgSize")/1024); else $formapi->SetProperty("C_Mail_SMTP_Delivery_MaxMsgSize",$value*1024);
																											break;
																										case"av_updatesize":
																											
																											if($get)$value=floor($formapi->GetProperty("C_AV_Info_UpdateSize"));
																											break;
																										case"urlencoded":
																											
																											if($get){
																												$value=urldecode($gObj->Base[$name]);
																											} else {
																												$result=true;
																												$gObj->Base[$name]=urlencode($value);
																											}

																											break;
																										case"watchdog":
																											
																											if($get)$value=explode(",",urldecode($gObj->Base[8])); else {
																												$result=true;
																												$gObj->Base[$name]=urlencode($value);
																											}

																											break;
																										case"headerfile":
																											case"footerfile":
																												
																												if($get){
																													$pom=$gObj->Base[$function];
																													$pom=explode(";",$pom);
																													
																													if($name==$function."_txt")$value=$pom[0];
																													
																													if($name==$function."_html")$value=$pom[1];
																												} else {
																													$av[$function][$name]=$value;
																													
																													if(count($av[$function])==2){
																														$gObj->Base[$function]=$av[$function][$function.'_txt'].";".$av[$function][$function.'_html'];
																													}

																												}

																												break;
																											case"m_headerfile":
																												case"m_footerfile":
																													
																													if($get){
																														$pom=$formapi->getProperty($function);
																														$pom=explode(";",$pom);
																														
																														if($name==$function."_txt")$value=$pom[0];
																														
																														if($name==$function."_html")$value=$pom[1];
																													} else {
																														$av[$function][$name]=$value;
																														
																														if(count($av[$function])==2){
																															$formapi->setProperty($function,$av[$function][$function.'_txt'].";".$av[$function][$function.'_html']);
																														}

																													}

																													break;
																												case"taskevents_messageorfile":
																													
																													if($get){
																														
																														if($name==7)$value=$gObj->Base[7]; else
																														if($name==8&&!$gObj->Base[7])$value=$gObj->Base[8]; else
																														if($name=='message_file'&&$gObj->Base[7]){
																															$value=$gObj->Base[8];
																														} else $value='';
																													} else {
																														
																														if($name==7)$av['taskevents_messageorfile'][7]=$value; else
																														if($name==8)$av['taskevents_messageorfile'][8]=$value; else
																														if($name=='message_file')$av['taskevents_messageorfile']['message_file']=$value;
																														
																														if(count($av['taskevents_messageorfile'])==3){
																															$gObj->Base[7]=$av['taskevents_messageorfile'][7];
																															
																															if(!$gObj->Base[7])$gObj->Base[8]=$av['taskevents_messageorfile'][8]; else $gObj->Base[8]=$av['taskevents_messageorfile']['message_file'];
																														}

																													}

																													break;
																												case"bitvalue":
																													$aInfo=explode('_',$name);
																													$bit=$aInfo[1];
																													$sname=$aInfo[0];
																													$lastbit=$option["ATTRIBUTES"]["LASTBIT"]?true:
																														false;
																														$val=$gObj->Base[is_numeric($sname)?($sname-1):
																															$sname];
																															
																															if($get){
																																$value=$val&pow(2,$bit-1);
																															} else {
																																
																																if($bit===1){
																																	$av['bits'.$sname]=($value)?1:
																																		0;
																																	}

																																	$av['bits'.$sname]+=($value*pow(2,$bit-1));
																																	$gObj->Base[is_numeric($sname)?($sname-1):
																																		$sname]=$av['bits'.$sname];
																																	}

																																	break;
																																case"b2mb":
																																	
																																	if($get)$value=$formapi->GetProperty($name)/1024/1024; else {
																																		$result=$formapi->SetProperty($name,$value*1024*1024);
																																	}

																																	break;
																																case"b2kb":
																																	
																																	if($get)$value=$formapi->GetProperty($name)/1024; else {
																																		$result=$formapi->SetProperty($name,$value*1024);
																																	}

																																	break;
																																case"kb2b":
																																	
																																	if($get)$value=$formapi->GetProperty($name)*1024; else $result=$formapi->SetProperty($name,$value/1024);
																																	break;
																																case"jakubovy_procenta":
																																	
																																	if($get)$value=$formapi->GetProperty($name)/100; else {
																																		$result=$formapi->SetProperty($name,$value*100);
																																	}

																																	break;
																																break;
																																case"cfard":
																																	
																																	if($get){
																																		
																																		if($gObj->Base["reject"]==1)$value=1; else
																																		if($gObj->Base["accept"]==1)$value=2; else
																																		if($gObj->Base["delete"]==1)$value=3; else
																																		if($gObj->Base["markspam"]==1)$value=4; else
																																		if($gObj->Base["markspam"]==2)$value=5;
																																	} else {
																																		$result=true;
																																		
																																		if($value==1){
																																			$gObj->Base["reject"]=1;
																																			$gObj->Base["accept"]=0;
																																			$gObj->Base["delete"]=0;
																																			$gObj->Base["markspam"]=0;
																																		} else
																																		if($value==2){
																																			$gObj->Base["reject"]=0;
																																			$gObj->Base["accept"]=1;
																																			$gObj->Base["delete"]=0;
																																			$gObj->Base["markspam"]=0;
																																		} else
																																		if($value==3){
																																			$gObj->Base["reject"]=0;
																																			$gObj->Base["accept"]=0;
																																			$gObj->Base["delete"]=1;
																																			$gObj->Base["markspam"]=0;
																																		} else
																																		if($value==4){
																																			$gObj->Base["reject"]=0;
																																			$gObj->Base["accept"]=0;
																																			$gObj->Base["delete"]=0;
																																			$gObj->Base["markspam"]=1;
																																		} else
																																		if($value==5){
																																			$gObj->Base["reject"]=0;
																																			$gObj->Base["accept"]=0;
																																			$gObj->Base["delete"]=0;
																																			$gObj->Base["markspam"]=2;
																																		}

																																	}

																																	break;
																																case"cfmarkgs":
																																	
																																	if($get){
																																		
																																		if($gObj->Base["markspam"]==1)$value=0; else
																																		if($gObj->Base["markgenuine"]==1)$value=1;
																																	} else {
																																		$result=true;
																																		
																																		if($value==0){
																																			$gObj->Base["markspam"]=1;
																																			$gObj->Base["markgenuine"]=0;
																																		} else
																																		if($value==1){
																																			$gObj->Base["markspam"]=0;
																																			$gObj->Base["markgenuine"]=1;
																																		}

																																	}

																																	break;
																																case"cf_containtype":
																																	$FunctionArray=array(8=>0,9=>0,4=>1,5=>1,10=>2,11=>2,2=>3,6=>3,3=>4,7=>4,0=>5,1=>5,12=>6,13=>6);
																																	$NotArray=array(8=>0,9=>1,4=>0,5=>1,10=>0,11=>1,2=>0,6=>1,3=>0,7=>1,0=>0,1=>1,13=>1,12=>0);
																																	
																																	if($get){
																																		
																																		if($name=="not")$value=$NotArray[$gObj->Base["containtype"]]; else {
																																			$value=$FunctionArray[intval($gObj->Base["containtype"])];
																																		}

																																	} else {
																																		
																																		if($name=="not")$av['cf_headertype']['not']=$value;
																																		
																																		if($name=="containtype")$av['cf_headertype']['containtype']=$value;
																																		
																																		if(count($av['cf_headertype'])==2){
																																			$not=$av['cf_headertype']['not'];
																																			switch($av['cf_headertype']['containtype']){
																																				case 0:
																																					$ret=$not?9:
																																						8;
																																						break;
																																					case 1:
																																						$ret=$not?5:
																																							4;
																																							break;
																																						case 2:
																																							$ret=$not?11:
																																								10;
																																								break;
																																							case 3:
																																								$ret=$not?6:
																																									2;
																																									break;
																																								case 4:
																																									$ret=$not?7:
																																										3;
																																										break;
																																									case 5:
																																										$ret=$not?1:
																																											0;
																																											break;
																																										case 6:
																																											$ret=$not?13:
																																												12;
																																												break;
																																										}

																																										$gObj->Base['containtype']=$ret;
																																									}

																																								}

																																								break;
																																								case"cf_remotelocal":
																																									$srArray=array(0=>0,1=>1,2=>0,3=>1,12=>0,13=>1,5=>1,4=>0);
																																									$rlArray=array(0=>0,1=>0,2=>1,3=>1,12=>0,13=>0,5=>0,4=>0);
																																									$accArray=array(0=>0,1=>0,2=>0,3=>0,12=>1,13=>1,5=>2,4=>2);
																																									$backArray=array("000"=>0,"100"=>1,"010"=>2,"110"=>3,"001"=>12,"101"=>13,"102"=>5,"002"=>4);
																																									
																																									if($get){
																																										
																																										if($name=="senderrecipient")$value=$srArray[$gObj->Base['messagesize']];
																																										
																																										if($name=="remotelocal")$value=$rlArray[$gObj->Base['messagesize']];
																																										
																																										if($name=="account")$value=$accArray[$gObj->Base['messagesize']];
																																									} else {
																																										
																																										if($name=="senderrecipient")$av['messagesize']['sr']=$value;
																																										
																																										if($name=="remotelocal")$av['messagesize']['rl']=$value;
																																										
																																										if($name=="account")$av['messagesize']['acc']=$value;
																																										
																																										if(count($av['messagesize'])==3){
																																											
																																											if($av['messagesize']['rl'])$av['messagesize']['acc']=0;
																																											$value=$av['messagesize']['sr'].$av['messagesize']['rl'].$av['messagesize']['acc'];
																																											$gObj->Base['messagesize']=$backArray[$value];
																																										}

																																									}

																																									break;
																																								case"cf_rfc":
																																									
																																									if($get){
																																										$value=decimal2binary($gObj->Base['case']);
																																										
																																										if($name=="blf")$value=$value[0]; else
																																										if($name=="eof")$value=$value[1]; else
																																										if($name=="crlf")$value=$value[2]; else
																																										if($name=="oxoo")$value=$value[3];
																																									} else {
																																										
																																										if($name=="blf")$av['rfc822']['blf']=$value; else
																																										if($name=="eof")$av['rfc822']['eof']=$value; else
																																										if($name=="crlf")$av['rfc822']['crlf']=$value; else
																																										if($name=="oxoo")$av['rfc822']['oxoo']=$value;
																																										
																																										if(count($av['rfc822'])==4)$gObj->Base['case']=$av['rfc822']['blf']*1+$av['rfc822']['eof']*2+$av['rfc822']['crlf']*4+$av['rfc822']['oxoo']*8;
																																									}

																																									break;
																																								case"cf_antivirus":
																																									
																																									if($get){
																																										$value=decimal2binary($gObj->Base['case']);
																																										
																																										if($name=="virus")$value=$value[1]; else
																																										if($name=="pwd")$value=$value[2]; else
																																										if($name=="novirus")$value=$value[3];
																																									} else {
																																										
																																										if($name=="virus")$av['cf_antivirus']['virus']=$value; else
																																										if($name=="pwd")$av['cf_antivirus']['pwd']=$value; else
																																										if($name=="novirus")$av['cf_antivirus']['novirus']=$value;
																																										
																																										if(count($av['cf_antivirus'])==3)$gObj->Base['case']=$av['cf_antivirus']['virus']*2+$av['cf_antivirus']['pwd']*4+$av['cf_antivirus']['novirus']*8;
																																									}

																																									break;
																																								case"cf_timecriteria":
																																									$BIN=base64_decode($gObj->Base['contain']);
																																									break;
																																								case"cf_headerfooter":
																																									
																																									if($get){
																																										$header=explode(";",$gObj->Base["headerfile"]);
																																										$footer=explode(";",$gObj->Base["footerfile"]);
																																										
																																										if($name=="htf")$value=$header[0];
																																										
																																										if($name=="hhf")$value=$header[1];
																																										
																																										if($name=="ftf")$value=$footer[0];
																																										
																																										if($name=="fhf")$value=$footer[1];
																																									} else {
																																										
																																										if($name=="htf")$av['cf_headerfooter']['htf']=$value;
																																										
																																										if($name=="hhf")$av['cf_headerfooter']['hhf']=$value;
																																										
																																										if($name=="ftf")$av['cf_headerfooter']['ftf']=$value;
																																										
																																										if($name=="fhf")$av['cf_headerfooter']['fhf']=$value;
																																										
																																										if(count($av['cf_headerfooter'])==4){
																																											$header=$av['cf_headerfooter']['htf'].";".$av['cf_headerfooter']['hhf'];
																																											$footer=$av['cf_headerfooter']['ftf'].";".$av['cf_headerfooter']['fhf'];
																																											$gObj->Base["headerfile"]=$header;
																																											$gObj->Base["footerfile"]=$footer;
																																										}

																																									}

																																									break;
																																								case"cf_extract":
																																									case"cf_casexml":
																																										
																																										if(strtolower($function)=='cf_extract')$index='extractpackage'; else $index='case';
																																										$v=intval($gObj->Base[$index]);
																																										
																																										if($get){
																																											$r[0]=($v==0)?0:
																																												($v%2);
																																												$r[1]=($v<2)?0:
																																													1;
																																													
																																													if($name=="case")$value=$r[0];
																																													
																																													if($name=="par1")$value=$r[1];
																																												} else {
																																													$av[$function][$name]=$value;
																																													
																																													if(count($av[$function])==2){
																																														$ret=$av[$function]["case"]*1+$av[$function]["par1"]*2;
																																														$gObj->Base[$index]=$ret;
																																													}

																																												}

																																												break;
																																											case"cf_case":
																																												
																																												if($get){
																																													$val=decimal2binary($gObj->Base['case'],3);
																																													switch($name){
																																														case'case_sensitive':
																																															$value=$val[0];
																																															break;
																																														case'match_whole_words':
																																															$value=$val[2];
																																															break;
																																													}

																																												} else {
																																													$av[$function][$name]=intval($value);
																																													
																																													if(count($av[$function])==2){
																																														$ret=$av[$function]["case_sensitive"]*1+$av[$function]["match_whole_words"]*4;
																																														$gObj->Base['case']=$ret;
																																													}

																																												}

																																												break;
																																												case"cf_case_match":
																																													
																																													if($get){
																																														$val=decimal2binary($gObj->Base['case'],5);
																																														switch($name){
																																															case'case_sensitive':
																																																$value=$val[0];
																																																break;
																																															case'match_whole_words':
																																																$value=$val[2];
																																																break;
																																															case'match':
																																																
																																																if($val[4]){
																																																	$value=2;
																																																} else
																																																if($val[3]){
																																																	$value=1;
																																																} else {
																																																	$value=0;
																																																}

																																																break;
																																														}

																																													} else {
																																														$av[$function][$name]=intval($value);
																																														
																																														if(count($av[$function])==3){
																																															switch($av[$function]["match"]){
																																																case'0':
																																																	$bits=0;
																																																	break;
																																																case'1':
																																																	$bits=8;
																																																	break;
																																																case'2':
																																																	$bits=16;
																																																	break;
																																															}

																																															$ret=$av[$function]["case_sensitive"]*1+$av[$function]["match_whole_words"]*4+$bits;
																																															$gObj->Base['case']=$ret;
																																														}

																																													}

																																													break;
																																													case"cf_messageorfile":
																																														
																																														if($get){
																																															
																																															if($name=='type')$value=$gObj->Base['type']; else
																																															if($name=='msg'&&!$gObj->Base["type"])$value=$gObj->Base["msg"]; else
																																															if($name=='message_file'&&$gObj->Base["type"]){
																																																$value=$gObj->Base["msg"];
																																															} else $value='';
																																														} else {
																																															
																																															if($name=='type')$av['cf_messageorfile']['type']=$value; else
																																															if($name=='msg')$av['cf_messageorfile']['msg']=$value; else
																																															if($name=='message_file')$av['cf_messageorfile']['message_file']=$value;
																																															
																																															if(count($av['cf_messageorfile'])==3){
																																																$gObj->Base['type']=$av['cf_messageorfile']['type'];
																																																
																																																if(!$gObj->Base['type'])$gObj->Base['msg']=$av['cf_messageorfile']['msg']; else $gObj->Base['msg']=$av['cf_messageorfile']['message_file'];
																																															}

																																														}

																																														break;
																																													case'cf_messagesize':
																																														
																																														if($get){
																																															$value=$gObj->Base["messagesize"];
																																														} else {
																																															$gObj->Base["contain"]=$value;
																																															$gObj->Base["messagesize"]=$value;
																																														}

																																														break;
																																													case"cf_priority":
																																														
																																														if($get)$value=$gObj->Base+1; else $gObj->Base=$value-1;
																																														break;
																																													case"cf_flags":
																																														
																																														if($get){
																																															$arr=decimal2binary($gObj->Base["flags"],10);
																																															$value=$arr[$name];
																																														} else {
																																															$av['cf_flags'][$name]=$value;
																																															
																																															if(count($av['cf_flags'])==10)$gObj->Base['flags']=binary2decimal($av["cf_flags"]);
																																														}

																																														break;
																																													case"m_listfile":
																																														$type=$formapi->GetProperty("M_SendAllLists");
																																														
																																														if($type==0){
																																															
																																															if($get){
																																																$value=$formapi->GetProperty("m_listfile");
																																															} else
																																															if(!($formtype=="add"&&$formobject=="list")){
																																																$result=$formapi->SetProperty("m_listfile",$value);
																																															}

																																														}

																																														break;
																																													case"m_sql":
																																														$type=$formapi->GetProperty("M_SendAllLists");
																																														
																																														if($type==5){
																																															
																																															if($get){
																																																$value=$formapi->GetProperty("m_sql");
																																															} else
																																															if(!($formtype=="add"&&$formobject=="list")){
																																																$result=$formapi->SetProperty("m_sql",$value);
																																															}

																																														}

																																														break;
																																													case"m_odbc":
																																														$type=$formapi->GetProperty("M_SendAllLists");
																																														
																																														if($type==5){
																																															$name=strtolower($name);
																																															
																																															if($get){
																																																$odbc=$formapi->GetProperty("M_ODBC");
																																																$arr=explode(",",$odbc);
																																																
																																																if($name=="m_odbc_primary")$value=$arr[0];
																																																
																																																if($name=="sql_statement")$value=$arr[3];
																																															} else {
																																																
																																																if($name=="m_odbc_primary")$av['m_odbc']['primary']=$value;
																																																
																																																if($name=="sql_statement")$av['m_odbc']['secondary']=$value;
																																																
																																																if(count($av['m_odbc'])==2){
																																																	$res=$av['m_odbc']['primary'].",,,".$av['m_odbc']['secondary'];
																																																	$result=$formapi->SetProperty("M_ODBC",$res);
																																																}

																																															}

																																														}

																																														break;
																																													case"get_gateways_list":
																																														$i=0;
																																														$result="";
																																														
																																														if($_SESSION["griddata"]["grids"]["sip_gateways"])foreach($_SESSION["griddata"]["grids"]["sip_gateways"]as$gw)$value.=($i++?"|":
																																															"").$gw["contact"];
																																															break;
																																														case"createpublicfolder":
																																															$gwapi=createobject("gwapi");
																																															$gwapi->user=$formapi->EmailAddress;
																																															$gwapi->Login();
																																															
																																															if($get){
																																																$r[0]=($v==0)?0:
																																																	($v%2);
																																																	$r[1]=($v<2)?0:
																																																		1;
																																																		
																																																		if($name=="rights")$value=$r[0];
																																																		
																																																		if($name=="par1")$value=$r[1];
																																																	} else {
																																																		$av[$function][$name]=$value;
																																																		
																																																		if(count($av[$function])==2){
																																																			$ret=$av[$function]["case"]*1+$av[$function]["par1"]*2;
																																																			$gObj->Base["case"]=$ret;
																																																		}

																																																	}

																																																	break;
																																																case"proxy_users":
																																																	
																																																	if($get){
																																																		$value=str_replace(";",CRLF,$_SESSION['datdata']['proxy.dat']['data']['Users']);
																																																	} else {
																																																		$_SESSION['datdata']['proxy.dat']['data']['Users']=str_replace(CRLF,";",$value);
																																																	}

																																																	break;
																																																case"u_remote_address":
																																																	
																																																	if($get){
																																																		
																																																		if($formapi->GetProperty("u_null")==true)$value=2;
																																																		elseif($formapi->GetProperty("U_UseRemoteAddress")==false){
																																																			$value=0;
																																																		} else $value=1;
																																																	} else {
																																																		
																																																		if($value==2)$formapi->SetProperty("u_null",1); else {
																																																			$formapi->SetProperty("u_null",0);
																																																			
																																																			if($value==1){
																																																				$formapi->SetProperty("U_UseRemoteAddress",1);
																																																			} else $formapi->SetProperty("U_UseRemoteAddress",0);
																																																		}

																																																		$formapi->Save();
																																																	}

																																																	break;
																																																case'c_accounts_policies_login_loginsettings':
																																																	
																																																	if($get)$value=$formapi->GetProperty("c_accounts_policies_login_loginsettings"); else {
																																																		$formapi->SetProperty("c_accounts_policies_login_loginsettings",$value);
																																																		
																																																		if($value==1){
																																																			$_SESSION['datdata']['config/_webmail/server.xml']['data']['logging_type']=1;
																																																		}

																																																	}

																																																	break;
																																																case"c_system_tools_autobackup_backupto":
																																																	
																																																	if($get)$value=$formapi->GetProperty("c_system_tools_autobackup_backupto"); else $formapi->SetProperty("c_system_tools_autobackup_backupto",$value);
																																																	break;
																																																case"c_system_services_sip_rtpmax":
																																																	
																																																	if($get){
																																																		$value=$formapi->GetProperty("C_System_Services_SIP_RTPMax")+$formapi->GetProperty("C_System_Services_SIP_RTPStart");
																																																	} else {
																																																		$formapi->SetProperty("C_System_Services_SIP_RTPMax",$value-$formapi->GetProperty("C_System_Services_SIP_RTPStart"));
																																																	}

																																																	break;
																																																case"cf_spamscore2":
																																																	
																																																	if($get)$value=$gObj->Base["score"]/100; else {
																																																		$gObj->Base["score"]=$value*100;
																																																	}

																																																	break;
																																																case"cf_spamscore":
																																																	
																																																	if($get)$value=$gObj->Base["messagesize"]/100; else {
																																																		$gObj->Base["messagesize"]=$value*100;
																																																	}

																																																	break;
																																																case"score":
																																																	
																																																	if($get){
																																																		$value=$formapi->GetProperty($name)/1024;
																																																		$value=round($value,2);
																																																	} else {
																																																		$formapi->SetProperty($name,(String)floor($value*1024));
																																																	}

																																																	break;
																																																case'u_bytes':
																																																	
																																																	if($get){
																																																		
																																																		if(strtolower($name)=='ignoremessageslarger')$value=$_SESSION['datdata']['spam.dat']['data'][$name];
																																																		elseif(strtolower($name)=='messagesize')$value=$gObj->Base["messagesize"];
																																																		elseif(strtolower($name)=='esize'){
																																																			$value=round($value,2);
																																																		} else $value=$formapi->GetProperty($name);
																																																		$multi=pow(1024,$option["ATTRIBUTES"]["SAVEUNIT"]);
																																																		$value=$value*$multi;
																																																		
																																																		if(($value %(1024*1024*1024))==0&&($value/(1024*1024*1024))>=1){
																																																			$value=$value/(1024*1024*1024);
																																																			$result=3;
																																																		} else
																																																		if(($value %(1024*1024))==0&&($value/(1024*1024))>=1){
																																																			$value=$value/(1024*1024);
																																																			$result=2;
																																																		} else {
																																																			$value=($value/1024);
																																																			$result=1;
																																																		}

																																																	} else {
																																																		$units=$_REQUEST[$name.'_units'];
																																																		$api_units=$option["ATTRIBUTES"]["SAVEUNIT"];
																																																		
																																																		if(!$api_units)$api_units=$option["SAVEUNIT"];
																																																		$api_multiplicator=pow(1024,($api_units));
																																																		$multiplicator=pow(1024,($units+1));
																																																		$value=$value*$multiplicator;
																																																		$value=$value/$api_multiplicator;
																																																		
																																																		if(strtolower($name)=='ignoremessageslarger')$result=setdatvalue('IgnoreMessagesLarger',$value,'spam.dat','spam2'); else
																																																		if(strtolower($name)=='messagesize'){
																																																			$gObj->Base["contain"]=$value;
																																																			$gObj->Base["messagesize"]=$value;
																																																		} else $result=$formapi->SetProperty($name,(string)$value);
																																																	}

																																																	break;
																																																case'unix2string':
																																																	
																																																	if($get){
																																																		
																																																		if(!$value){
																																																			
																																																			if($formapi){
																																																				$value=$formapi->getProperty($name);
																																																			}

																																																			
																																																			if(!$value){
																																																				$value=$gObj->base[$name];
																																																			}

																																																		}

																																																		
																																																		if($value){
																																																			$val=$value-$formapi->getProperty('c_timezone');
																																																			$value=date("d.m.y H:i:s",$val);
																																																		}

																																																	}

																																																	break;
																																																case'u_time':
																																																	$t=array(0=>'sec',1=>'min',2=>'hour',3=>'day');
																																																	$multi=array('sec'=>1,'min'=>60,'hour'=>3600,'day'=>3600*24,''=>1);
																																																	
																																																	if($get){
																																																		$value=$formapi->GetProperty($name);
																																																		$api_units=$option["ATTRIBUTES"]["SAVEUNIT"];
																																																		$m=$multi[$api_units];
																																																		$value=$value*$m;
																																																		
																																																		if(($value %(60*60*24))==0&&($value/(60*60*24))>=1){
																																																			$value=$value/(60*60*24);
																																																			$result=3;
																																																		} else
																																																		if(($value %(60*60))==0&&($value/(60*60))>=1){
																																																			$value=$value/(60*60);
																																																			$result=2;
																																																		} else
																																																		if(($value %(60))==0&&($value/(60))>=1){
																																																			$value=$value/60;
																																																			$result=1;
																																																		}

																																																	} else {
																																																		$units=$multi[$t[$_REQUEST[$name.'_units']+1]];
																																																		$value=($units*$value)/$multi[$option['SAVEUNIT']];
																																																		$result=$formapi->SetProperty($name,(string)$value);
																																																	}

																																																	break;
																																																case'use_spf_level':
																																																	
																																																	if(!$get){
																																																		$_SESSION['datdata']['rules/local.cf']['data']['spf_level']=$_REQUEST['use_spf_level'];
																																																		switch($_REQUEST['use_spf_level']){
																																																			case 0:
																																																				$value='0,10';
																																																				break;
																																																			case 1:
																																																				$value='0,50';
																																																				break;
																																																			case 2:
																																																				$value='5,00';
																																																				break;
																																																		}

																																																		global$rawdata;
																																																		foreach($rawdata as$rkey=>$rline){
																																																			
																																																			if(preg_match("#score SPF_SOFTFAIL ([0-9,^s]{0,})#si",$rline,$matches)){
																																																				$rawdata[$rkey]="score SPF_SOFTFAIL ".$value;
																																																			}

																																																		}

																																																	} else {
																																																		$value=$_SESSION['datdata']['rules/local.cf']['data']['spf_level'];
																																																	}

																																																	break;
																																																	case'mail_member_param':
																																																		
																																																		if($get){
																																																			
																																																			if($gObj->Base['param'])$value=explode("&",$gObj->Base['param']);
																																																			@$value=implode(CRLF,$value);
																																																		} else {
																																																			
																																																			if(!$value)$gObj->Base['param']=''; else {
																																																				$value=explode(CRLF,$value);
																																																				
																																																				if($value)foreach($value as$key=>$val)
																																																				if(!$val)unset($value[$key]);
																																																				
																																																				if(is_array($value))@$gObj->Base['param']=implode("&",$value);
																																																			}

																																																		}

																																																		break;
																																																	case"u_send_message":
																																																		
																																																		if($get){
																																																			$value=$formapi->GetProperty("u_respondercontent");
																																																		}

																																																		break;
																																																	case'message_quarantine':
																																																		
																																																		if($get){
																																																			@$data=file_get_contents($_SESSION['SPAMPATH'].'challenge.txt');
																																																			$msg=explode("\n",$data,2);
																																																			
																																																			if($name=='from')$value=$_SESSION['datdata']['spam.dat']['data']['SpamChallengeMailFrom'];
																																																			
																																																			if($name=='body')$value=ltrim($msg[1]);
																																																			
																																																			if($name=='subject')$value=rtrim($msg[0]);
																																																		} else {
																																																			
																																																			if($name=='from'){
																																																				
																																																				if(!$_SESSION['datdata']['spam.dat']){
																																																					$_SESSION['datdata']['spam.dat']['data']=parsedatfile('spam.dat','spam2');
																																																					$_SESSION['datdata']['spam.dat']['type']='spam2';
																																																				}

																																																				$_SESSION['datdata']['spam.dat']['data']['SpamChallengeMailFrom']=$value;
																																																				$result=true;
																																																			} else {
																																																				$av['message_quarantine'][$name]=$value;
																																																				$result=true;
																																																			}

																																																			
																																																			if(count($av['message_quarantine']==2)){
																																																				$msg=$av['message_quarantine']['subject']."\r\n".$av['message_quarantine']['body'];
																																																				@file_put_contents($_SESSION['SPAMPATH'].'challenge.txt',$msg);
																																																			}

																																																		}

																																																		break;
																																																	case"bool2string":
																																																		$value=$value?$alang['TAccountDeleteForm_YesButton']:
																																																			$alang['TAccountDeleteForm_NoButton'];
																																																			break;
																																																		case"bool2stringnot":
																																																			$value=$value?$alang['TAccountDeleteForm_NoButton']:
																																																				$alang['TAccountDeleteForm_YesButton'];
																																																				break;
																																																			case"urldecode":
																																																				
																																																				if($get){
																																																					$value=urldecode($value);
																																																				} else {
																																																					$value=urlencode($value);
																																																				}

																																																				break;
																																																			case"listbox":
																																																				
																																																				if(!$labels=$alang[$option['ATTRIBUTES']['LABELS']])$labels=$option['ATTRIBUTES']['LABELS'];
																																																				
																																																				if($option['ATTRIBUTES']['VALUE']){
																																																					$vals=array_flip(explode("|",$option['ATTRIBUTES']['VALUE']));
																																																					$value=$vals[$value];
																																																				}

																																																				$labels=explode("|",$labels);
																																																				$label=$labels[$value];
																																																				$value=$label;
																																																				break;
																																																			case"redirect_flags":
																																																				$langinfo=explode("|",$alang["TRouteForm_FlagsEdit"]);
																																																				$value=$langinfo[$value];
																																																				break;
																																																			case"webdeny":
																																																				$value=$value?$alang['TStrings_webipgrant']:
																																																					$alang['TStrings_webipdeny'];
																																																					break;
																																																				case"xmppserver":
																																																					
																																																					if($get){
																																																						$server=$_SESSION['datdata']['config/_webmail/server.xml']['data']['xmppserver'];
																																																						
																																																						if($port=$_SESSION['datdata']['config/_webmail/server.xml']['data']['xmppport'])
																																																						if($port!=5222)$server.=":".$port;
																																																						$value=$server;
																																																					} else {
																																																						
																																																						if(strpos($value,":")!==false){
																																																							$val=explode(":",$value);
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['xmppserver']=$val[0];
																																																							
																																																							if(!$val[1])$val[1]=25;
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['xmppport']=$val[1];
																																																						} else {
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['xmppserver']=$value;
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['xmppport']='';
																																																						}

																																																					}

																																																					break;
																																																				case"smtpserver":
																																																					
																																																					if($get){
																																																						$server=$_SESSION['datdata']['config/_webmail/server.xml']['data']['smtpserver'];
																																																						
																																																						if($port=$_SESSION['datdata']['config/_webmail/server.xml']['data']['smtpport'])
																																																						if($port!=25)$server.=":".$port;
																																																						$value=$server;
																																																					} else {
																																																						
																																																						if(strpos($value,":")!==false){
																																																							$val=explode(":",$value);
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['smtpserver']=$val[0];
																																																							
																																																							if(!$val[1])$val[1]=25;
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['smtpport']=$val[1];
																																																						} else {
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['smtpserver']=$value;
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['smtpport']='';
																																																						}

																																																					}

																																																					break;
																																																				case"imapserver":
																																																					
																																																					if($get){
																																																						$server=$_SESSION['datdata']['config/_webmail/server.xml']['data']['imapserver'];
																																																						
																																																						if($port=$_SESSION['datdata']['config/_webmail/server.xml']['data']['imapport'])
																																																						if($port!=143)$server.=":".$port;
																																																						$value=$server;
																																																					} else {
																																																						
																																																						if(strpos($value,":")!==false){
																																																							$val=explode(":",$value);
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['imapserver']=$val[0];
																																																							
																																																							if(!$val[1])$val[1]=143;
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['imapport']=$val[1];
																																																						} else {
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['imapserver']=$value;
																																																							$_SESSION['datdata']['config/_webmail/server.xml']['data']['imapport']='';
																																																						}

																																																					}

																																																					break;
																																																				case'grid_delphitime':
																																																					
																																																					if($get){
																																																						
																																																						if(!$value){
																																																							$value=$gObj->Base[$name];
																																																						}

																																																						
																																																						if($value){
																																																							$value=convertdelphidatetotime($value);
																																																							$value=date("Y/m/d",$value);
																																																						}

																																																					} else {
																																																						
																																																						if($value){
																																																							$info=explode("/",$value);
																																																							$value=mktime(0,0,0,$info[1],$info[2],$info[0]);
																																																							$value=ceil(converttimetodelphi($value));
																																																							$gObj->Base[$name]=$value;
																																																						}

																																																					}

																																																					break;
																																																				case'defaultscripthost':
																																																					
																																																					if($get){
																																																						$value=$gObj->Grids['webservice']->Head[0]['DEFAULTHOST'][0]['VALUE'];
																																																					} else {
																																																						$gObj->Grids['webservice']->Head[0]['DEFAULTHOST'][0]['VALUE']=$value;
																																																					}

																																																					break;
																																																				case'autodiscover':
																																																					$base=$_SESSION['datdata']['autodiscover.dat']['data'];
																																																					
																																																					if($get){
																																																						$smtp=$formapi->GetProperty('C_Mail_SMTP_General_HostName');
																																																						
																																																						if($name=='SMTP'){
																																																							$value=$smtp;
																																																						} else {
																																																							$value=$base[$name];
																																																							
																																																							if(!$value){
																																																								$value=$smtp;
																																																							}

																																																						}

																																																					} else {
																																																						$av[$function][$name]=$value;
																																																						
																																																						if(count($av[$function])==10){
																																																							foreach($av[$function]as$n=>$v){
																																																								
																																																								if($n=='SMTP'){
																																																									$formapi->SetProperty('C_Mail_SMTP_General_HostName',$v);
																																																								} else {
																																																									
																																																									if($v!=$av[$function]['SMTP']){
																																																										
																																																										if(strpos($n,'Type')==true&&$v==0){
																																																											unset($_SESSION['datdata']['autodiscover.dat']['data'][$n]);
																																																										} else {
																																																											$_SESSION['datdata']['autodiscover.dat']['data'][$n]=$v;
																																																										}

																																																									} else {
																																																										unset($_SESSION['datdata']['autodiscover.dat']['data'][$n]);
																																																									}

																																																								}

																																																							}

																																																						}

																																																					}

																																																					break;
																																																				case'mailing_rights':
																																																					
																																																					if($get){
																																																						$value=$gObj->Base['rights'];
																																																						$val=decimal2binary($value,3);
																																																						switch($name){
																																																							case'default':
																																																								$value=$value==0?1:
																																																									0;
																																																									break;
																																																								case'post':
																																																									$value=$val[1];
																																																									break;
																																																								case'recieve':
																																																									$value=$val[0];
																																																									break;
																																																								case'digest':
																																																									$value=$val[2];
																																																									break;
																																																							}

																																																						} else {
																																																							$av[$function][$name]=$value;
																																																							
																																																							if(count($av[$function])==4){
																																																								
																																																								if($av[$function]['default']){
																																																									$gObj->Base['rights']=0;
																																																								} else {
																																																									$gObj->Base['rights']=$av[$function]['post']*2+$av[$function]['recieve']*1+$av[$function]['digest']*4;
																																																								}

																																																							}

																																																						}

																																																						break;
																																																						case'g_addrootadmin':
																																																							
																																																							if($get){
																																																								$value="";
																																																							} else {
																																																								$formapi->SetProperty("g_addrootadmin",$value);
																																																							}

																																																							break;
																																																						case'd_headerfooterflag':
																																																							
																																																							if($get){
																																																								$value=$formapi->GetProperty($function);
																																																								$val=decimal2binary($value,2);
																																																								switch($name){
																																																									case'local_to_local':
																																																										$value=$val[0];
																																																										break;
																																																									case'local_to_remote':
																																																										$value=$val[1];
																																																										break;
																																																								}

																																																							} else {
																																																								$av[$function][$name]=$value;
																																																								
																																																								if(count($av[$function])==2){
																																																									$formapi->SetProperty($function,$av[$function]['local_to_local']*1+$av[$function]['local_to_remote']*2);
																																																								}

																																																							}

																																																							break;
																																																							case"service_name":
																																																								require_once(get_cfg_var('icewarp_sharedlib_path').'api/services.php');
																																																								$serviceHandler=slServices::instance('WebAdmin/'.$_SESSION['EMAIL']);
																																																								
																																																								if($get){
																																																									$services=$serviceHandler->Services();
																																																									$service=$services[strtolower($formvalue)];
																																																									$value=($service['label']?$service['label']:
																																																										$service['id']).('('.$service['daemon'].')');
																																																									} else {
																																																									}

																																																									break;
																																																								case"service_startup":
																																																									require_once(get_cfg_var('icewarp_sharedlib_path').'api/services.php');
																																																									$serviceHandler=slServices::instance('WebAdmin/'.$_SESSION['EMAIL']);
																																																									
																																																									if($get){
																																																										$services=$serviceHandler->Services();
																																																										$service=$services[strtolower($formvalue)];
																																																										$value=($service['label']?$service['label']:
																																																											$service['id']).('('.$service['daemon'].')');
																																																										} else {
																																																										}

																																																										break;
																																																									case"enginetype":
																																																										
																																																										if($get){
																																																											$val=$formapi->GetProperty('C_AV_Kaspersky')+$formapi->GetProperty('C_AV_Symantec')*2+$formapi->GetProperty('C_AV_Avast')*3+$formapi->GetProperty('C_AV_AVG')*4;
																																																											$arr=array(0=>'',1=>'Kaspersky',2=>'Symantec',3=>'Avast',4=>'AVG');
																																																											$value=$arr[$val];
																																																										} else {
																																																										}

																																																										break;
																																																									case'user_voip':
																																																										
																																																										if($get){
																																																											$val=$formapi->GetProperty('U_SIP_CallTransfer');
																																																											
																																																											if($val){
																																																												$xml=simplexml_load_string($val);
																																																												$data['user_voip_fwd_type']=intval($xml->INACTIVE)?0:
																																																													1;
																																																													$data['user_voip_fwd_after']=strval($xml->FORWARDTIME);
																																																													$data['user_voip_fwd_to']=strval($xml->FORWARDTIMETARGET);
																																																												} else {
																																																													$data['user_voip_fwd_type']=0;
																																																												}

																																																												$value=$data[$name];
																																																											} else {
																																																												$av[$function][$name]=$value;
																																																												
																																																												if(count($av[$function])==3){
																																																													$val=$formapi->GetProperty('U_SIP_CallTransfer');
																																																													
																																																													if($val){
																																																														$xml=simplexml_load_string($val);
																																																													} else {
																																																														$xml=new SimpleXMLElement('<RULE></RULE>');
																																																													}

																																																													
																																																													if($av[$function]['user_voip_fwd_type']){
																																																														$xml->NUMBER=$formapi->EmailAddress;
																																																														$xml->OWNER=$formapi->EmailAddress;
																																																														$xml->FORWARDTIME=$av[$function]['user_voip_fwd_after'];
																																																														$xml->FORWARDTIMETARGET=$av[$function]['user_voip_fwd_to'];
																																																														$xml->INACTIVE=0;
																																																														$value=$xml->asXML();
																																																													} else {
																																																														
																																																														if($val){
																																																															$xml->INACTIVE=1;
																																																															$value=$xml->asXML();
																																																														} else {
																																																															$value='';
																																																														}

																																																													}

																																																													$formapi->SetProperty('U_SIP_CallTransfer',$value);
																																																												}

																																																											}

																																																											break;
																																																										case'commtouchforce':
																																																											
																																																											if($get){
																																																												$sa=$_SESSION['datdata']['spam/spam.dat']['data']['SpamAssasinScoreValue'];
																																																												$clow=$_SESSION['datdata']['spam/spam.dat']['data']['CommTouchLowScore'];
																																																												$value=round(($sa-$clow-0.01));
																																																											}

																																																											break;
																																																								}

																																																								return$result;
																																																							}

																																																							
																																																							function getconnections($connstr){
																																																								
																																																								if(substr_count($connstr,"|")>0)$conns=explode("|",$connstr,2); else {
																																																									$conns[0]=$connstr;
																																																									$conns[1]="";
																																																								}

																																																								return$conns;
																																																							}

																																																							?>