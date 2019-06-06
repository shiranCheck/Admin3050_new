<?php 
	require_once("include.php");
	require_once("inc/clsparsexml.php");
	require_once("formvariables.php");
	require_once("function.php");
	require_once("syntax/clsgridhandler.php");
	function getobjectxml($object,$value=''){
		global$gObj,$global;
		switch($object){
			case'user':
				case'group':
					case'domain':
						case'list':
							case'listserv':
								case'exec':
									case'remote':
										case'route':
											case'catalog':
												case'notify':
													case'resource':
														$object='accounts/'.$object;
														break;
													case'datagrid':
														$object=$gObj->Dialogname;
														default:
															$object='interface/'.$_SESSION['imode'].'/'.$object;
															break;
												}

												$xmlparse=new ParseXML;
												$xml=$xmlparse->GetXMLTree($global['backpath'].'xml/'.$object.'.xml');
												return$xml["FORM"][0];
											}

											
											function processformaction($type,$object,$value,&$error,&$message){
												global$global,$formapi;
												prepareglobalvariables($type,$object,$value,true);
												$xml=getobjectxml($object,$value);
												@$global["gdat"]=$xml["ATTRIBUTES"]["DATFILE"];
												@$global["gtype"]=$xml["ATTRIBUTES"]["DATTYPE"];
												$array=createformarray($xml);
												$result=checkformaction($error);
												$result=$result&&processformarray($array,$error,$message);
												return$result;
											}

											
											function processformarray($array,&$error,&$message){
												global$alang,$formtype,$formvalue,$formobject,$formapi,$global,$gObj,$account;
												$result=true;
												for($i=0;$i<count($array);$i++){
													for($fi=0;$fi<count($array[$i]);$fi++){
														$item=$array[$i][$fi];
														
														if(!$item["NAME"]&&$item["NAME"]!=0)continue;
														
														if(isset($global["gdat"])){
															$datfile=$global["gdat"];
															$dattype=$global["gtype"];
														} else {
															$datfile=$item["DATFILE"];
															$dattype=$item["DATTYPE"];
														}

														
														if($formobject=="service"){
															
															if($variable=='C_System_Services_#SID#_MaxOutConn'){
																return;
															}

															$variable=str_replace("#SID#",$formvalue,$variable);
														}

														
														if($formobject=="accessmode"||$formobject=="accessmode2"){
															global$access_mode_prefix;
															
															if($variable)$variable=$access_mode_prefix.'_'.$variable;
														}

														$errorMsg='';
														
														if(!$item["NAME"])$item["NAME"]=0;
														
														if(@$item["READONLY"])$itemresult=true; else
														if($item["FUNCTION"]||$item["VARIABLE"]||$item["VARIABLE"]===0){
															$itemresult=setformvalue($item,@$_REQUEST[$item["NAME"]],@$_REQUEST["use_settings".$item["NAME"]],$datfile,$dattype,$message,$errorMsg);
														} else $itemresult=true;
														
														if($errorMsg)$error.=$errorMsg."<br/>";
														$result=$itemresult&&$result;
													}

												}

												
												if($result){
													
													if(@$_SESSION["griddata"]){
														$gObj->LoadData();
														$gObj->Save();
													}

													
													if(@$_SESSION['datdata']){
														$result=savedatfiles();
														unset($_SESSION['datdata']);
													}

													
													if($_REQUEST['object']!="datagrid")unset($_SESSION["griddata"]["grids"][$gObj->PUID]);
													$account=createobject("account");
													
													if($formapi->EmailAddress){
														$acc=$account->Open($formapi->EmailAddress);
													}

													$result=$result&&$formapi->Save();
													$_SESSION['FORMAPI_SAVE_FAILED']=0;
													
													if(!$result)$_SESSION['FORMAPI_SAVE_FAILED']=1;
												} else $_SESSION['FORMAPI_SAVE_FAILED']=0;
												
												if(!$result){
													$errorMsg=getErrorMsg($formapi);
													
													if(!$error)$error.=$errorMsg;
													$_SESSION['FORMAPI_SAVE_FAILED']=1;
												}

												
												if($error)$error='<div id="err" class="errormessage">'.$error.'</div>';
												return$result;
											}

											
											function createformarray($xml){
												global$global,$skindata,$formtype;
												$array=array();
												$skindata['js']='';
												$sections=$xml["SECTION"];
												for($i=0;$i<count($sections);$i++){
													$dis="";
													$dis=strtoupper($sections[$i]["ATTRIBUTES"]["DISABLE"]);
													$section_readonly=false;
													
													if(strpos($dis,'=')!==false){
														$info=explode('=',$dis);
														$dis=$info[0];
														
														if(strtolower($info[1])=='readonly'){
															
															if($dis==$_SESSION['ACCOUNT'])$section_readonly=true;
															$dis="";
														}

													}

													
													if($dis==osversion||$dis==$_SESSION['ACCOUNT']||strtolower($dis)==strtolower($formtype))continue;
													$name=$sections[$i]["ATTRIBUTES"]["NAME"];
													
													if($name)
													if(!checkformlimit($name))continue;
													$group=$sections[$i]["GROUP"];
													for($fi=0;$fi<count($group);$fi++){
														$dis="";
														$dis=strtoupper($group[$fi]["ATTRIBUTES"]["DISABLE"]);
														$group_readonly=false;
														
														if(strpos($dis,'=')!==false){
															$info=explode('=',$dis);
															$dis=$info[0];
															
															if(strtolower($info[1])=='readonly'){
																
																if($dis==$_SESSION['ACCOUNT'])$group_readonly=true;
																$dis="";
															}

														}

														
														if($section_readonly){
															$group_readonly=true;
														}

														
														if($dis==osversion||$dis==$_SESSION['ACCOUNT']||strtolower($dis)==strtolower($formtype))continue;
														
														if(isset($global["gdat"])){
															$datfile=$global["gdat"];
															$dattype=$global["gtype"];
														}

														
														if(isset($group[$fi]["ATTRIBUTES"]["DATFILE"])){
															@$datfile=$group[$fi]["ATTRIBUTES"]["DATFILE"];
															@$dattype=$group[$fi]["ATTRIBUTES"]["DATTYPE"];
														} else {
															unset($datfile);
															unset($dattype);
														}

														$name=$group[$fi]["ATTRIBUTES"]["NAME"];
														
														if($name)
														if(!checkformlimit($name))continue;
														$options=$group[$fi]["OPTION"];
														for($fl=0;$fl<count($options);$fl++){
															$dis="";
															$option=$options[$fl];
															$dis=strtoupper($option["ATTRIBUTES"]["DISABLE"]);
															$readonly=false;
															
															if(strpos($dis,'=')!==false){
																$info=explode('=',$dis);
																$dis=$info[0];
																
																if(strtolower($info[1])=='readonly'){
																	
																	if($dis==$_SESSION['ACCOUNT']){
																		$readonly=true;
																	}

																	$dis="";
																}

															}

															
															if($group_readonly){
																$readonly=true;
															}

															
															if($readonly){
																$option['ATTRIBUTES']['READONLY']=1;
															}

															
															if($option['ATTRIBUTES']['NAME']=='account_domain'&&$formtype=='edit'){
																$option['ATTRIBUTES']['TYPE']='hidden';
																unset($option['ATTRIBUTES']['DISABLE']);
																$dis='';
															}

															
															if($dis==osversion||$dis==$_SESSION['ACCOUNT']||strtolower($dis)==strtolower($formtype))continue;
															
															if($option['ATTRIBUTES']['NAME']=='mailbox_view'&&isuserright("V"))continue;
															$array[]=processformoption($option,false,true,@$datfile,@$dattype);
														}

													}

												}

												return$array;
											}

											
											function processform($type,$object,$value){
												global$global,$skindata,$formtype;
												$skindata['js']='';
												prepareglobalvariables($type,$object,$value);
												$xml=getobjectxml($object,$value);
												@$global["gdat"]=$xml["ATTRIBUTES"]["DATFILE"];
												@$global["gtype"]=$xml["ATTRIBUTES"]["DATTYPE"];
												$skindata['form_onsubmit']=$xml["ATTRIBUTES"]["ONSUBMIT"];
												return createform($xml);
											}

											
											function createform($xml){
												global$alang,$global,$formtype,$resize_available;
												$sections=$xml["SECTION"];
												$result="";
												for($i=0;$i<count($sections);$i++){
													$section_ret='';
													$dis="";
													$dis=strtoupper($sections[$i]["ATTRIBUTES"]["DISABLE"]);
													$section_readonly=false;
													
													if(strpos($dis,'=')!==false){
														$info=explode('=',$dis);
														$dis=$info[0];
														
														if(strtolower($info[1])=='readonly'){
															
															if($dis==$_SESSION['ACCOUNT'])$section_readonly=true;
															$dis="";
														}

													}

													
													if($dis==osversion||$dis==$_SESSION['ACCOUNT']||(strtolower($dis)==strtolower($formtype))&$formtype!="")continue;
													$name=$sections[$i]["ATTRIBUTES"]["NAME"];
													
													if($name)
													if(!checkformlimit($name))continue;
													$helpid=$sections[$i]["ATTRIBUTES"]["HELPID"];
													$sectionHeight=$sections[$i]["ATTRIBUTES"]["HEIGHT"];
													
													if($sectionHeight)$resize_available=true;
													
													if($sectionHeight)$sectionHeight='style="height:'.$sectionHeight.';"';
													$section_ret.='<div '.$sectionHeight.' class="section" id="section_'.$name.'"><div id="sectitle_'.$name.'" class="sectiontitle">'.str_replace(" ","&nbsp;",$alang[$sections[$i]["ATTRIBUTES"]["LABEL"]]).'</div>'.CRLF.'<span class="helpid">'.$helpid."</span>".CRLF;
													$group=$sections[$i]["GROUP"];
													$groupCount=0;
													for($fi=0;$fi<count($group);$fi++){
														$dis="";
														$dis=strtoupper($group[$fi]["ATTRIBUTES"]["DISABLE"]);
														$group_readonly=false;
														
														if(strpos($dis,'=')!==false){
															$info=explode('=',$dis);
															$dis=$info[0];
															
															if(strtolower($info[1])=='readonly'){
																
																if($dis==$_SESSION['ACCOUNT'])$group_readonly=true;
																$dis="";
															}

														}

														
														if($section_readonly){
															$group_readonly=true;
														}

														
														if($dis==osversion||$dis==$_SESSION['ACCOUNT']||strtolower($dis)==strtolower($formtype))continue;
														$name=$group[$fi]["ATTRIBUTES"]["NAME"];
														
														if($name)
														if(!checkformlimit($name))continue;
														$groupret=processgroup($group[$fi],$group_readonly);
														
														if($groupret){
															$section_ret.=$groupret.CRLF;
															$groupCount++;
														}

													}

													
													if($groupCount>0){
														$result.=$section_ret."</div>";
													}

												}

												return$result;
											}

											
											function processgroup($section,$readonly=false){
												global$alang,$groupname,$global,$formtype;
												@$label=$section["ATTRIBUTES"]["LABEL"];
												@$type=$section["ATTRIBUTES"]["TYPE"];
												@$groupHeight=$section["ATTRIBUTES"]["HEIGHT"];
												
												if($groupHeight)$groupHeight='style="height:'.$groupHeight.';"';
												
												if(isset($global["gdat"])){
													$datfile=$global["gdat"];
													$dattype=$global["gtype"];
												}

												
												if(isset($section["ATTRIBUTES"]["DATFILE"])){
													@$datfile=$section["ATTRIBUTES"]["DATFILE"];
													@$dattype=$section["ATTRIBUTES"]["DATTYPE"];
												}

												$name=$section["ATTRIBUTES"]["NAME"];
												$result='<div class="grouptitle">'.$alang[$label].'</div>'.CRLF;
												
												if(strtolower($type)=='propertybox'){
													$pb=true;
													$presult='<tr><th class="tdfix1" colspan="2">'.$alang["TFTPUserForm_OptionsSheet"].'</th>'.CRLF.'<th>'.$alang["TFrameAccounts_SRVl"].'</th></tr>'.CRLF;
												} else $pb=false;
												@$result.='<div '.$groupHeight.' class="group '.$type.'" id="'.$name.'" >'.CRLF.'<table>'.CRLF.$presult;
												$groupname=$label;
												$options=$section["OPTION"];
												$optCount=0;
												for($i=0;$i<count($options);$i++){
													$dis="";
													$option=$options[$i];
													$dis=strtoupper($option["ATTRIBUTES"]["DISABLE"]);
													$item_readonly=false;
													
													if(strpos($dis,'=')!==false){
														$info=explode('=',$dis);
														$dis=$info[0];
														
														if(strtolower($info[1])=='readonly'){
															
															if($dis==$_SESSION['ACCOUNT']){
																$item_readonly=true;
															}

															$dis="";
														}

													}

													
													if($readonly){
														$item_readonly=true;
													}

													
													if($item_readonly){
														$option['ATTRIBUTES']['READONLY']=1;
													}

													
													if($option['ATTRIBUTES']['NAME']=='account_domain'&&$formtype=='edit'){
														$option['ATTRIBUTES']['TYPE']='hidden';
														unset($option['ATTRIBUTES']['DISABLE']);
														$dis='';
													}

													
													if($dis==osversion||$dis==$_SESSION['ACCOUNT'])continue;
													
													if($option['ATTRIBUTES']['NAME']=='mailbox_view'&&isuserright("V"))continue;
													$optret=processformoption($option,false,$array,@$datfile,@$dattype,@$pb);
													
													if($optret){
														$result.=$optret.CRLF;
														$optCount++;
													}

												}

												$result.='</table></div>'.CRLF;
												
												if($optCount==0){
													$result='';
												}

												return$result;
											}

											
											function getfunctionlabels($labels){
												$function=substr($labels,strpos($labels,":")+1,strlen($labels));
												formfunctionvalues($function,$value,true,false,$message);
												return$value;
											}

											
											function processformoption($option,$nohtml,$array,$datfile,$dattype,$propertyb=false,$sub_type=0){
												global$days,$dayslang,$skin_dir,$alang,$formvalue,$formapi,$editform,$formobject,$skindata,$groupname,$defaultvalue,$dgcount,$gObj,$formtype;
												@$label=$option["ATTRIBUTES"]["LABEL"];
												@$label=str_replace("#SID#",strtoupper($formvalue),$label);
												
												if(preg_match('#(.*)\[([0-9]{0,})\]#i',$label,$matches)){
													$label=explode("|",$alang[$matches[1]]);
													$label=$label[$matches[2]];
												} else {
													$label=$alang[$label];
												}

												
												if(!$label)$label=$option["ATTRIBUTES"]["LABEL"];
												@$labels=$option["ATTRIBUTES"]["LABELS"];
												@$values=$option["ATTRIBUTES"]["VALUE"];
												
												if(strpos($labels,":")==false)@$labels=$alang[$labels]; else
												if(!$array){
													$labels=getfunctionlabels($labels,$value);
													$values=$labels;
												}

												@$type=$option["ATTRIBUTES"]["TYPE"];
												@$caption=$option["ATTRIBUTES"]["CAPTION"];
												
												if($alang[$caption])$caption=$alang[$caption];
												@$name=$option["ATTRIBUTES"]["NAME"];
												@$variable=$option["ATTRIBUTES"]["VARIABLE"];
												
												if(isset($option["ATTRIBUTES"]["VARIABLE"])&&$variable=="")$variable=$name;
												@$function=$option["ATTRIBUTES"]["FUNCTION"];
												@$disable=$option["ATTRIBUTES"]["DISABLE"];
												@$defaultvalue=$option["ATTRIBUTES"]["DEFAULT"];
												@$source=$option["ATTRIBUTES"]["SOURCE"];
												@$inverse=$option["ATTRIBUTES"]["INVERSE"];
												@$height=$option["ATTRIBUTES"]["HEIGHT"];
												@$width=$option["ATTRIBUTES"]["WIDTH"];
												@$tooltip=addslashes($alang[$option["ATTRIBUTES"]["TOOLTIP"]]);
												@$tooltipWidth=$option["ATTRIBUTES"]["TOOLTIPWIDTH"];
												
												if($tooltip&&!$tooltipWidth)$tooltipWidth=300;
												@$tooltipHeight=$option["ATTRIBUTES"]["TOOLTIPHEIGHT"];
												
												if($tooltip&&!$tooltipHeight)$tooltipHeight=200;
												$request=false;
												$request=$option["ATTRIBUTES"]["REQUEST"];
												
												if($option["ATTRIBUTES"]["DATFILE"])@$datfile=$option["ATTRIBUTES"]["DATFILE"];
												
												if($option["ATTRIBUTES"]["DATTYPE"])@$dattype=$option["ATTRIBUTES"]["DATTYPE"];
												@$bfile=$option["ATTRIBUTES"]["FILEID"];
												@$btype=$option["ATTRIBUTES"]["BTYPE"];
												@$blabel=$option["ATTRIBUTES"]["BLABEL"];
												
												if($alang[$blabel])@$blabel=$alang[$blabel];
												@$bypass=$option["ATTRIBUTES"]["BFILEID"];
												@$cfile=$option["ATTRIBUTES"]["CFILEID"];
												@$readonly=$option["ATTRIBUTES"]["READONLY"];
												
												if($readonly){
													$read_only=' readonly="1"';
												} else {
													$read_only='';
												}

												
												if(@!$height){
													
													if($btype=='fbutton'){
														$height=90;
													} else {
														$height=480;
													}

												}

												
												if(@!$width){
													
													if($btype=='fbutton'){
														$width=220;
													} else {
														$width=640;
													}

												}

												
												if(@$option["ATTRIBUTES"]["EVENT"]){
													$event=explode("|",$option["ATTRIBUTES"]["EVENT"]);
													$eventtype=$event[0];
													$eventaction=$event[1];
													$event=$eventtype.'="'.$eventaction.'"';
												}

												
												if($propertyb)$event='onchange="document.getElementById("use_settings_'.$name.'").checked = true;';
												
												if($option['ATTRIBUTES']['ONCE']&&$option['ATTRIBUTES']['ONCE']!='false'){
													$onclick='this.disabled = true;';
												}

												
												if(!@$hidden)
												if(@$option["ATTRIBUTES"]["HIDDEN"])$hidden='style="visibility:hidden;"';
												
												if(!@$disabled)
												if(@$option["ATTRIBUTES"]["DISABLED"])$disabled='disabled="disabled"';
												
												if(isset($option["ATTRIBUTES"]["DATFILE"])){
													@$datfile=$option["ATTRIBUTES"]["DATFILE"];
													@$dattype=$option["ATTRIBUTES"]["DATTYPE"];
												}

												
												if(!checkoption($name,$type,$disable))return false;
												
												if($option["ATTRIBUTES"]["UNITS"]){
													$variable='';
													$function=$option["ATTRIBUTES"]["UNITS"];
													$defaultunit=$option["ATTRIBUTES"]["DEFAULTUNIT"];
													$units=formfunctionvalues($function,$value,true,$name,$message,$option);
													setcookie('current_unit_'.$name,$units-1);
													$subitem.='<td><select name="'.$name.'_units" >';
													switch($option["ATTRIBUTES"]["UNITS"]){
														case'u_bytes':
															$i_labels=explode("|",$alang["TConfigForm_LogRotationUnits"]);
															break;
														case'u_time':
															$i_labels=explode("|",$labels);
															break;
												}

												foreach($i_labels as$num=>$i_label){
													$subitem.='<option value="'.$num.'" '.(($num==($units-1)||($value==0&&$defaultunit==($num)))?"selected=\"1\"":
													"").'>'.$i_label.'</option>';
												}

												$subitem.='</select></td>';
											} else $subitem='';
											
											if($formobject=="service"){
												switch(strtolower($formvalue)){
													case'activesync':
														case'anti-spam':
															case'anti-virus':
																case'proxy':
																	case'sms':
																		case'syncml':
																			case'webclient':
																				case'webdav':
																					
																					if($variable=="C_System_Services_#SID#_AccessMode")return false;
																					
																					if($variable=="C_System_Services_#SID#_IPList")return false;
																					
																					if($variable=="C_System_Services_#SID#_Port")return false;
																					
																					if($variable=="C_System_Services_BindIPAddress")return false;
																					
																					if($variable=="C_System_Services_#SID#_Traffic")return false;
																					
																					if($variable=="C_System_Services_Firewall")return false;
																					
																					if($variable=="C_System_Services_#SID#_AltPort")return false;
																					
																					if($variable=="C_System_Services_#SID#_MaxInConn")return false;
																					
																					if($variable=="C_System_Services_#SID#_IPGrant")return false;
																					
																					if($name=="servicebind")return false;
																					case'socks':
																						
																						if($variable=="C_System_Services_#SID#_SSLPort")return false;
																						
																						if($variable=="C_System_Services_#SID#_MonitorConn")return false;
																						
																						if($variable=="C_System_Services_#SID#_MonitorData")return false;
																						
																						if($variable=="C_System_Services_#SID#_ThreadCache")return false;
																						
																						if($variable=="C_System_Services_#SID#_MaxOutConn")return false;
																						
																						if($variable=="C_System_Services_#SID#_Bandwidth")return false;
																						
																						if($variable=="C_System_Services_#SID#_AltPort")return false;
																						
																						if($variable=="C_System_Services_#SID#_Startup")return false;
																						
																						if($variable=="C_System_Services_#SID#_AccessMode")return false;
																						break;
																					case'snmp':
																						case'minger':
																							case'groupware_notification':
																								
																								if($variable=="C_System_Services_#SID#_SSLPort")return false;
																								
																								if($variable=="C_System_Services_#SID#_MonitorConn")return false;
																								
																								if($variable=="C_System_Services_#SID#_MonitorData")return false;
																								
																								if($variable=="C_System_Services_#SID#_ThreadCache")return false;
																								
																								if($variable=="C_System_Services_#SID#_MaxOutConn")return false;
																								
																								if($variable=="C_System_Services_#SID#_Bandwidth")return false;
																								
																								if($variable=="C_System_Services_#SID#_AltPort")return false;
																								
																								if($variable=="C_System_Services_#SID#_AccessMode")return false;
																								
																								if($variable=="C_System_Services_#SID#_IPGrant")return false;
																								
																								if($variable=="C_System_Services_#SID#_IPList")return false;
																								
																								if($variable=="C_System_Services_#SID#_Port")return false;
																								
																								if($variable=="C_System_Services_#SID#_Traffic")return false;
																								
																								if($variable=="C_System_Services_Firewall")return false;
																								
																								if($variable=="C_System_Services_#SID#_MaxInConn")return false;
																								
																								if($variable=="C_System_Services_#SID#_Startup")return false;
																								break;
																							case'sip':
																								
																								if($variable=="C_System_Services_#SID#_MaxInConn")return false;
																								
																								if($variable=="C_System_Services_#SID#_Bandwidth")return false;
																								case'ldap':
																									
																									if($variable=="C_System_Services_#SID#_MaxOutConn")return false;
																									
																									if($variable=="C_System_Services_#SID#_AltPort")return false;
																									
																									if($variable=="C_System_Services_#SID#_IPList")return false;
																									
																									if($variable=="C_System_Services_#SID#_AccessMode")return false;
																									
																									if($variable=="C_System_Services_#SID#_MonitorConn")return false;
																									
																									if($variable=="C_System_Services_#SID#_MonitorData")return false;
																									
																									if($variable=="C_System_Services_#SID#_ThreadCache")return false;
																									
																									if($variable=="C_System_Services_#SID#_Startup")return false;
																									
																									if($variable=="C_System_Services_#SID#_IPGrant")return false;
																									break;
																								case"control":
																									
																									if($variable=="C_System_Services_#SID#_AltPort")return false;
																									
																									if($variable=="C_System_Services_#SID#_MaxOutConn")return false;
																									
																									if($variable=="C_System_Services_#SID#_IPGrant")return false;
																									break;
																								case"ftp":
																									
																									if($variable=="C_System_Services_#SID#_Startup")return false;
																									
																									if($variable=="C_System_Services_#SID#_AltPort")return false;
																									
																									if($variable=="C_System_Services_#SID#_IPGrant")return false;
																									break;
																								case"imap":
																									
																									if($variable=="C_System_Services_#SID#_Startup")return false;
																									
																									if($variable=="C_System_Services_#SID#_IPGrant")return false;
																									
																									if($variable=="C_System_Services_#SID#_AltPort")return false;
																									break;
																								case"gw":
																									
																									if($variable=="C_System_Services_#SID#_SSLPort")return false;
																									case'im':
																										
																										if($variable=="C_System_Services_#SID#_MaxOutConn")return false;
																										default:
																											
																											if($variable=="C_System_Services_#SID#_IPGrant")return false;
																											
																											if(!($formvalue=="SMTP"||$formvalue=="IM"))
																											if($variable=="C_System_Services_#SID#_AltPort")return false;
																											break;
																								}

																								$variable=str_replace('#SID#',$formvalue,$variable);
																							}

																							
																							if($formobject=="accessmode"||$formobject=="accessmode2"){
																								global$access_mode_prefix;
																								
																								if($variable&&strtolower($access_mode_prefix)!='c_system_services_sip_mode')$variable=$access_mode_prefix.'_'.$variable;
																								
																								if(strtolower($access_mode_prefix)=='c_system_services_sip_mode')
																								if(strtolower($variable)=='processingmode')$variable=$access_mode_prefix; else $variable='C_System_Services_SIP_AccessGroup';
																							}

																							
																							if($_REQUEST['editform']&&$_SESSION['FORMAPI_SAVE_FAILED']||$request){
																								
																								if(strtolower($type)=='checkbox')$value=strtolower($_REQUEST[$name])=="on"?1:
																								0; else $value=$_REQUEST[$name];
																								
																								if(!isset($_REQUEST[$name])){
																									$value=$option['ATTRIBUTES']['DEFAULT'];
																								}

																							}

																							elseif(!$array)$value=getformvalue($name,$function,$variable,$datfile,$source,$dattype,$inverse,$request,$option);
																							
																							if($name=='SpamGLMode'){
																								
																								if(!trim($value))$value=0;
																							}

																							
																							if($formtype=='add'&&isset($defaultvalue)&&!isset($value)){
																								$value=$defaultvalue;
																							}

																							
																							if($caption)$caption=' title="'.$caption.'"'; else $caption='';
																							
																							if($tooltip)$caption.=' onmouseover="drawToolTip(\''.$tooltip.'\','.$tooltipWidth.','.$tooltipHeight.',this);" onmouseout="closeToolTip();"';
																							
																							if($option['ATTRIBUTES']['ACCESS'])
																							if(!getAccessMode($option['ATTRIBUTES']['ACCESS'],$value))$disabled='disabled="disabled"';
																							
																							if($type=='edit'&&$name=='domain_name'&&$formtype=='edit'){
																								$type='hidden';
																							}

																							switch(strtolower($type)){
																								case"hidden":
																									
																									if($formtype=='add'&&isset($option['ATTRIBUTES']['DEFAULT'])){
																										$value=$option['ATTRIBUTES']['DEFAULT'];
																									}

																									
																									if(isset($option['ATTRIBUTES']['HIDDEN'])){
																										$value=$option['ATTRIBUTES']['HIDDEN'];
																									}

																									@$itemstr='<input type="hidden" name="'.$name.'" id="input_'.$name.'" value="'.$value.'">';
																									@$item=$itemstr;
																									break;
																								case"info":
																									
																									if($values){
																										$values=array_flip(explode("|",$values));
																										$value=$values[$value];
																									}

																									$name="";
																									
																									if($labels){
																										$labels=explode("|",$labels);
																										$value=$labels[$value];
																									}

																									@$item='<td class="tdfix1">'.$label.'</td>'.'<td>'.$value.'</td>';
																									break;
																								case"label":
																									
																									if($values){
																										$values=array_flip(explode("|",$values));
																										$value=$values[$value];
																									}

																									
																									if($labels){
																										$labels=explode("|",$labels);
																										$value=$labels[$value];
																									}

																									$name="";
																									
																									if(strtolower($option["ATTRIBUTES"]["STYLE"])=='bold')$clsstyle=" bold"; else $clsstyle="";
																									
																									if(!$option["ATTRIBUTES"]["INLINE"]){
																										$colspan='colspan="2" ';
																									} else {
																										$colspan='';
																									}

																									@$item='<td '.$colspan.'class="'.$clsstyle.'" '.$caption.'>'.$label.'</td>';
																									break;
																								case"edit":
																									case"password":
																										
																										if($sub_type==1||$option['ATTRIBUTES']['INLINE'])$class='class="input wid100"'; else $class='class="input wid250"';
																										@$itemstr='<input type="'.(($type=="edit")?"text":
																											"password").'" '.$caption.' '.$class.' '.$disabled.'  '.$event.' autocomplete="off" name="'.$name.'"'.$read_only.' id="input_'.$name.'" value="'.htmlspecialchars($value).'">';
																											
																											if($option["ATTRIBUTES"]["UNITS"])$itemstr=$itemstr.$subitem;
																											
																											if($sub_type==1||$option['ATTRIBUTES']['INLINE'])$class3='rightalign'; else $class3='tdfix1';
																											@$item='<td class="'.$class3.'"><label '.$caption.' for="input_'.$name.'" >'.$label."</label></td>".'<td>'.$itemstr.'</td>';
																											break;
																										case"between":
																											$between=1;
																											$type="checkbox";
																											case"checkbox":
																												case"radio":
																													@$sub=$option["OPTION"];
																													
																													if($sub){
																														foreach($sub as$val){
																															
																															if($val['ATTRIBUTES']['NAME']=='mailbox_view'&&isuserright("V"))continue;
																															
																															if($between){
																																$itemstr=processformoption($val,true,$array,$datfile,$dattype,false,1);
																																$itemstr2.=$itemstr;
																															} else $itemstr=processformoption($val,true,$array,$datfile,$dattype);
																															
																															if($array)$result[]=$itemstr;
																														}

																														
																														if($between)$itemstr=$itemstr2;
																													}

																													$fchecked="";
																													
																													if(isset($values)){
																														
																														if(intval(@$value)==intval(@$values))$fchecked="checked";
																														$fchecked.=' value="'.$values.'"';
																													} else
																													if(@$value!=0)$fchecked="checked";
																													
																													if(!$option["ATTRIBUTES"]["INLINE"]){
																														$colspan='colspan="2" ';
																													} else {
																														$colspan='';
																													}

																													@$item='<td '.(!$itemstr?$colspan:
																														"class=\"tdfix1\"").' ><input type='.($type=="checkbox"?"checkbox":
																															"radio").$read_only.' id="input_'.$name.$option['ATTRIBUTES']['VALUE'].'" name="'.$name.'" '.$disabled.' '.$caption.' '.$event.' '.$fchecked.'><label for="input_'.$name.$option['ATTRIBUTES']['VALUE'].'">'.$label.'</label></td>'.($itemstr?"<td>".$itemstr."</td>":
																																"");
																																break;
																															case"textarea":
																																$rows=$option['ATTRIBUTES']['ROWS']?' rows="'.$option['ATTRIBUTES']['ROWS'].'"':
																																	'';
																																	$cols=$option['ATTRIBUTES']['COLS']?' cols="'.$option['ATTRIBUTES']['COLS'].'"':
																																		'';
																																		@$itemstr='<textarea'.$rows.$cols.' name="'.$name.'" class="input" '.$disabled.'  '.$event.' id="input_'.$name.'"'.$read_only.' '.$caption.' style="height:'.$height.' important!;" >'.$value.'</textarea>';
																																		@$item='<td class="tdfix1">'.$label."</td>".'<td>'.$itemstr.'</td>';
																																		break;
																																	case"download":
																																		@$itemstr='<a href="functionbutton.html?fileid='.urlencode($bfile).'&param='.$btype.'" '.$caption.' class="button" style="color:#000;padding:1px;cursor:default;">&nbsp;&nbsp;'.$blabel.'&nbsp;&nbsp;</a>';
																																		@$item='<td class="tdfix1">'.$label."</td>".'<td>'.$itemstr.'</td>';
																																		break;
																																	case"upload":
																																		
																																		if($option['ATTRIBUTES']['BLABEL']){
																																			
																																			if($option['ATTRIBUTES']['NEWLINE']){
																																				$sButton='<tr><td><button type="submit">'.$blabel.'</button></td></tr>';
																																			} else {
																																				$sButton='<button type="submit">'.$blabel.'</button>';
																																			}

																																		} else $sButton='';
																																		@$itemstr='<input id="input_'.$name.'" '.$caption.' type="file" name="'.$name.'"/>'.$sButton;
																																		@$item='<td class="tdfix1">'.$label."</td>".'<td>'.$itemstr.'</td>';
																																		break;
																																	case"listbox":
																																		
																																		if($option['ATTRIBUTES']['INLINE'])$class=' wid100"'; else {
																																			$class.='"';
																																		}

																																		
																																		if($readonly){
																																			$read_only=' disabled="disabled"';
																																		} else {
																																			$read_only='';
																																		}

																																		$list=explode("|",$labels);
																																		$list_count=count($list);
																																		
																																		if($option['ATTRIBUTES']['LABELS']=='TContentHeader_ContainListBox'&&strpos($gObj->Location,'_rules')!==false){
																																			unset($list[1]);
																																			$list_count+=1;
																																			$unsetBlankSpace=true;
																																		} else {
																																			$unsetBlankSpace=false;
																																		}

																																		
																																		if(!$values)for($i=0;$i<$list_count;$i++)$values.=($i?"|":
																																			"").$i;
																																			$listb=explode("|",$values);
																																			for($i=0;$i<$list_count;$i++){
																																				
																																				if(!$unsetBlankSpace||isset($list[$i])){
																																					@$options.='<option value="'.$listb[$i].'" '.((trim($value)===trim($listb[$i])||intval($value)===$listb[$i])?"selected":
																																						"").'>'.$list[$i]."</option>";
																																					}

																																				}

																																				@$itemstr='<select class="input '.$class.$read_only.' id="input_'.$name.'" '.$caption.' name="'.$name.'" '.$disabled.'  '.$event.' >'.$options.'</select>';
																																				
																																				if($sub_type==1||$option['ATTRIBUTES']['INLINE'])$class3='rightalign'; else $class3='tdfix1';
																																				@$item='<td class="'.$class3.'"><label for="input_'.$name.'"  '.$caption.' >'.$label.'</label></td>'.'<td>'.$itemstr.'</td>';
																																				break;
																																			case"radiolist":
																																				$list=explode("|",$labels);
																																				
																																				if(!$values)for($i=0;$i<count($list);$i++)$values.=($i?"|":
																																					"").$i;
																																					$listb=explode("|",$values);
																																					for($i=0;$i<count($list);$i++)@$itemstr.='<input type="radio"'.$read_only.' id="input_'.$name.'" '.$caption.' name="'.$name.'" value="'.$listb[$i].'" '.$disabled.'  '.$event.' '.(trim($value)==trim($listb[$i])?"checked":
																																						"").'>'.$list[$i].'&nbsp;';
																																						@$item='<td class="tdfix1">'.$label."</td>".'<td>'.$itemstr.'</td>';
																																						break;
																																					case"textfile":
																																						$skindata['buttons']=1;
																																						
																																						if($formvalue)$pomocna=$formvalue; else $pomocna=$_REQUEST['fileid'];
																																						
																																						if($formobject=='remote'){
																																							$pomocna=$formvalue;
																																						}

																																						@$item='<td class="tdfix1">&nbsp;</td>'.'<td><button type="submit" name="textedit" id="input_'.$name.'" '.$disabled.' '.$caption.' '.$event.' onclick="textfileedit(\''.urlencode($pomocna).'\',\''.urlencode($values).'\'); return false;">'.$label.'</button></td>';
																																						break;
																																					case"bwfile":
																																						$skindata['buttons']=1;
																																						@$item='</tr></table>'.'<iframe id="iframe_'.$name.'" class="iframe" frameborder="0" '.$caption.' style="height:70%;" src="bwlist.html?object='.urlencode($formvalue).'&fileid='.urlencode($values).'"></iframe></td><tr><table>';
																																						break;
																																					case"mailboxview":
																																						$skindata['buttons']=1;
																																						@$item='<td class="tdfix1">&nbsp;</td>'.'<td><button type=submit name="mailbox" id="input_'.$name.'" '.$caption.' '.$disabled.'  '.$event.' onclick="mailboxview(\''.urlencode($formvalue).'\',\''.urlencode($values).'\'); return false;">'.$label.'</button></td>';
																																						break;
																																					case"daysofweek":
																																						@$itemstr='';
																																						@$daya=decimal2binary($value);
																																						for($i=0;$i<7;$i++){
																																							
																																							if(!$daya[$i])$ch='checked'; else $ch='';
																																							@$itemstr.='<input type="checkbox" id="input_'.$name.$i.'" '.$caption.' name="'.$name.$i.'" '.$disabled.'  '.$event.'  '.$ch.'/><label for="input_'.$name.'">'.$alang[$dayslang[$i]].'</label>';
																																						}

																																						@$item='<td class="tdfix1">'.$label.'</td>
          <td>'.$itemstr.'</td>';
																																						break;
																																					case"button":
																																						$skindata['buttons']=1;
																																						
																																						if($btype=='xmledit'){
																																							$obj=$bfile;
																																							
																																							if(strtolower($bfile)=='schedule'){
																																								$cfile=$name;
																																							}

																																							switch(strtolower($name)){
																																								case"ra_schedule":
																																									case"user_message_file":
																																										case"list_headerfiles":
																																											case"group_members":
																																												case"d_headerfiles":
																																													$bfile=$_REQUEST['value'];
																																													break;
																																												case'remotewatchdogschedule':
																																													$cfile=$name;
																																													$bfile=$gObj->getGridItemData(8)."|".$gObj->Location."|".$gObj->PUID."|7";
																																													break;
																																												case"taskeventschedule":
																																													$cfile=$name;
																																													$bfile=$gObj->getGridItemData(3)."|".$gObj->Location."|".$gObj->PUID."|2";
																																													break;
																																												case'odbc_mlist':
																																													$cfile=$formvalue;
																																													break;
																																											}

																																										} else $obj=$formvalue;
																																										
																																										if(strtolower($btype)=='fbutton'){
																																											$val=$formapi->getProperty("C_System_Tools_Migration_Active");
																																											
																																											if(strtolower($bfile)=='migration_start')
																																											if($val)$disabled=' disabled="disabled"';
																																											
																																											if(strtolower($bfile)=='migration_stop')
																																											if(!$val)$disabled=' disabled="disabled"';
																																											
																																											if(strtolower($bfile)=='migration_finish')
																																											if(!(!$val&&file_exists($_SESSION['CONFIGPATH'].'migrate.dat')))$disabled=' disabled="disabled"';
																																											
																																											if(strtolower($bfile)=='testsql')$cfileid=$formvalue;
																																											
																																											if(strtolower($bfile)=='remoteaccountnow')$cfileid=$formvalue;
																																										}

																																										
																																										if($option['ATTRIBUTES']['INLINE']){
																																											
																																											if(!$iwidth=$option['ATTRIBUTES']['IWIDTH']){
																																												$iwidth='150';
																																											}

																																											$class='class="input" style="padding:0;margin:0;"';
																																										}

																																										
																																										if($sub_type==1||$option['ATTRIBUTES']['INLINE'])$class3='rightalign'; else $class3='tdfix1';
																																										
																																										if(strtolower($btype)!='linkbutton'){
																																											$bfile=urlencode($bfile);
																																										}

																																										@$itemstr='<button type=button '.$class.' id="input_'.$name.'" '.$caption.' '.$disabled.'  onclick="buttonedit(\''.$btype.'\',\''.urlencode($obj).'\',\''.$bfile.'\',\''.$cfile.'\','.$width.','.$height.');'.$onclick.' return false;">'.$blabel.'</button>';
																																										
																																										if($btype=='bwfile')@$itemstr='<iframe name="'.$name.'" src="bwlist.html?object='.urlencode($obj).'&fileid='.urlencode($bfile).'&comment='.urlencode($cfile).'"></iframe>';
																																										
																																										if($sub_type==1||$option['ATTRIBUTES']['INLINE'])$class='class="input wid100"'; else $class='class="input wid250"';
																																										@$item=($label?('<td class="'.$class3.'">'.$label.'</td>'):
																																										'').'
           <td>'.$itemstr.'</td>';
																																										
																																										if($bypass!=''){
																																											@$itemstr2='<button type="button" '.$caption.' onclick="buttonedit(\'textfile\',\''.urlencode($formvalue).'\',\''.urlencode($bypass).'\',\'examples/bypass.dat\','.$width.','.$height.');'.$onclick.'">B</button>';
																																										}

																																										@$item=($label?('<td class="'.$class3.'">'.$label.'</td>'):
																																										'').'
  		       <td>'.$itemstr.$itemstr2.'</td>';
																																										break;
																																										case"iframe":
																																											@$item='</tr></table>'.'<iframe frameborder="0" style="height:70%;width:100%;" id="iframe_'.$name.'" name="'.$name.'" src="iframe.html?fileid='.urlencode($bfile).'"></iframe></td><tr><table>';
																																											break;
																																										case"datagrid":
																																											$skindata['datagrid']=1;
																																											@$files=$option["SOURCE"][0]["FILE"][0]["ATTRIBUTES"];
																																											@$sfile=$files["SYNTAX"];
																																											@$file=$files["NAME"];
																																											@$formid="lister".$dgcount;
																																											@$schedule_type=$_REQUEST['cfileid'];
																																											
																																											if($name=="schedule")$name=$schedule_type;
																																											@$gtype=$option["ATTRIBUTES"]["GTYPE"];
																																											
																																											if(isset($_REQUEST['uid'])){
																																												$uid=$_REQUEST['uid'];
																																												$gObj->PUID=$uid;
																																											} else
																																											if(isset($gObj->PUID)){
																																												$uid=$gObj->PUID;
																																											} else {
																																												$uid=time().rand(0,1000);
																																												$gObj->PUID=$uid;
																																											}

																																											$gObj->AddGrid($gtype,$name,$option,$array,++$dgcount,$uid);
																																											$aDataGridValue=explode('|',$gObj->Location);
																																											$aDatagridFixedValue=array();
																																											foreach($aDataGridValue as$val){
																																												$aDatagridFixedValue[]=$val.$uid;
																																											}

																																											$sDataGridValue=implode(';',$aDatagridFixedValue);
																																											$skindata['puid']=$uid;
																																											$datagrid['dgcount']=$dgcount;
																																											$datagrid['limit']=$option["ATTRIBUTES"]["LIMIT"]?$option["ATTRIBUTES"]["LIMIT"]:
																																												20;
																																												$datagrid['srcdivid']='div_'.$name;
																																												$datagrid['heading']['num']=$gObj->Grids[$name]->getGridHead($option,$formid,$dgcount,$selector);
																																												$datagrid['body']=$gObj->Grids[$name]->getGridBody($option,$selector);
																																												$datagrid['buttons']['num']=$gObj->Grids[$name]->getGridButtons($option,$_REQUEST['object']);
																																												$datagrid['selector']=$selector;
																																												$datagrid['datagrid_value']=$sDataGridValue;
																																												$gheight=$option["ATTRIBUTES"]["GHEIGHT"];
																																												
																																												if(@!$gheight)$gheight="300px";
																																												$height=$gheight;
																																												@$item='</tr></table>'.CRLF.'<input id="input_'.$option['ATTRIBUTES']['ID'].'" name="'.$name.'" type="hidden" value="'.$sDataGridValue.'" /><div style="height:'.$gheight.'" name="'.$name.'" id="div_'.$name.'" '.$disabled.'  '.$event.' ></div>'.CRLF.'<table><tr>';
																																												$skindata['js'].=template($skin_dir."dgridjs.tpl",$datagrid);
																																												break;
																																											case"account_select":
																																												global$sDomain;
																																												$a=createobject("api");
																																												$olddomain=$sDomain;
																																												$domains=getdomainlist();
																																												foreach($domains as$dom){
																																													$dm=$a->UTF8ToIDN($dom["DOMAIN"]);
																																													$acount=0;
																																													
																																													if(!$firstdomain)$firstdomain=$dom["DOMAIN"];
																																													$dlist.='<option value="'.$dm.'">'.$dom["DOMAIN"].'</option>';
																																													$sDom=$dom["DOMAIN"];
																																													$state=$_COOKIE["SELECTACCOUNTS"];
																																													$_COOKIE["SELECTACCOUNTS"]=false;
																																													$accounts=getaccountlist('',$sDom);
																																													$_COOKIE["SELECTACCOUNTS"]=$state;
																																													$alist.='accounts[\''.$dm.'\']= new Array();'."\r\n";
																																													foreach($accounts as$acc){
																																														$aname=getstrvalue($acc["LABEL"]);
																																														
																																														if($acc["ICON"]=="remote")continue;
																																														$alist.='accounts[\''.$dm.'\']['.$acount++.']= \''.getstrvalue($acc["ACCOUNT"]).'\';'."\r\n";
																																													}

																																												}

																																												$sDomain=$olddomain;
																																												$target="input_".$option["ATTRIBUTES"]["TARGET"];
																																												$append=$option["ATTRIBUTES"]["APPEND"];
																																												$brackets=!$option["ATTRIBUTES"]["NOBRACKETS"];
																																												@$item='<td>
            <script type="text/javascript">
                  var accounts = new Array();
                  '.$alist.'
            </script>
            <select id="domain_select_list" onChange="FillAccounts(accounts,this.value);" class="input" style="width:200px;">'.$dlist.'</select>';
																																												
																																												if(strtolower($option["ATTRIBUTES"]["DOMAINSELECT"])!='false')$item.='<button onclick="SelectDomain(\''.$append.'\',\''.$target.'\',\''.$brackets.'\');return false;">'.str_replace("&amp;","",$alang["TAccountSelectForm_SelectDomain"]).'</button>';
																																												
																																												if(strtolower($option["ATTRIBUTES"]["ACCOUNTSELECT"])!='false')$item.='<br/><select id="account_select_list" class="input" style="width:200px;" ></select>
            <button onclick="SelectAccount(\''.$append.'\',\''.$target.'\');return false;">'.str_replace("&amp;","",$alang["TAccountSelectForm_SelectAccountButton"]).'</button>       
            </td>
            <script type="text/javascript">
              FillAccounts(accounts,"'.$firstdomain.'");
            </script>';
																																												unset($a);
																																												break;
																																											case"slider":
																																												$skindata['slider']=1;
																																												$value=str_replace(",",".",$value);
																																												$value=$value*(1/$option["ATTRIBUTES"]["DECIMAL"]);
																																												
																																												if($option["ATTRIBUTES"]["HIDEINPUT"]==1)$itype="hidden"; else $itype="edit";
																																												$llabel=$alang[$option["ATTRIBUTES"]["LLABEL"]]?$alang[$option["ATTRIBUTES"]["LLABEL"]]:
																																													$option["ATTRIBUTES"]["LLABEL"];
																																													$rlabel=$alang[$option["ATTRIBUTES"]["RLABEL"]]?$alang[$option["ATTRIBUTES"]["RLABEL"]]:
																																														$option["ATTRIBUTES"]["RLABEL"];
																																														$itemstr='<table><tr><td><div class="dynamic-slider" id="slider-'.$name.'" tabIndex="1"><span id="slider-left-label">'.$llabel.'</span><span id="slider-right-label">'.$rlabel.'</span></div></td>
                 <td><input type="'.$itype.'" onkeydown="return false;" class="dynamic-slider-input" id="input_'.$name.'" name="'.$name.'" /></td></tr></table>
                 <script type="text/javascript">
                  var s = new Slider(document.getElementById("slider-'.$name.'"),
                    document.getElementById("input_'.$name.'"));
                  s.setMinimum('.$option["ATTRIBUTES"]["MIN"].');
                  s.setMaximum('.$option["ATTRIBUTES"]["MAX"].');
                  s.setIncrement('.$option["ATTRIBUTES"]["STEP"].');
                  s.setUnits('.$option["ATTRIBUTES"]["DECIMAL"].');
                  s.setValue('.$value.');
                </script>';
																																														$item='<td class="tdfix1">'.$label.'</td><td>'.$itemstr.'</td>';
																																														break;
																																													case"folder_select":
																																														$skindata['folder_select']=1;
																																														$account=new MerakAccount();
																																														$account->open($_SESSION['EMAIL']);
																																														$folders=getMailboxes($account);
																																														$target=$option["ATTRIBUTES"]["TARGET"];
																																														foreach($folders as$folder){
																																															$fdr["root"]=$fdr["name"]=$folder->name;
																																															$fdr["ico"]='images/tree/folder2.gif';
																																															$fdr["js"]='selectFolder("'.$target.'","'.$folder->name.'");';
																																															$fdrs[]=$fdr;
																																														}

																																														$tpldata['lmenu']['num']=$fdrs;
																																														$tpldata['helper1']='{';
																																														$tpldata['helper2']='}';
																																														$tpldata['path']=$skin_dir;
																																														$data=template($skin_dir.'menudata.tpl',$tpldata);
																																														@$item='<td style="width:100%;" colspan="2"><div id="id1" style="height:150px;overflow:scroll;font-size:1.1em;">&nbsp;</div></td>
          <script type="text/javascript">'.$data.'loadmenu(data,1);</script>';
																																														break;
																																											}

																																											
																																											if($array){
																																												
																																												if($name||$name=='0'){
																																													$item=array();
																																													$item["NAME"]=$name;
																																													$item["FILEID"]=$bfile;
																																													$item["VALUES"]=$values;
																																													$item["INVERSE"]=$inverse;
																																													$item["SOURCE"]=$source;
																																													$item["TYPE"]=$type;
																																													$item["LABEL"]=$label;
																																													$item["VARIABLE"]=$variable;
																																													$item["FUNCTION"]=$function;
																																													$item["DATFILE"]=$datfile;
																																													$item["DATTYPE"]=$dattype;
																																													$item["READONLY"]=$readonly;
																																													$item["PROPERTY"]=$propertyb;
																																													$item["SAVEUNIT"]=$option["ATTRIBUTES"]["SAVEUNIT"];
																																													$item["ERROR"]=$option["ATTRIBUTES"]["ERROR"];
																																													;
																																													
																																													if(!$nohtml)$result[]=$item; else $result=$item;
																																													return$result;
																																												}

																																												return false;
																																											}

																																											
																																											if($propertyb){
																																												
																																												if($_REQUEST['editform']){
																																													
																																													if($_REQUEST["use_settings_".$name])$ch='checked="checked"'; else $ch="";
																																												} else {
																																													
																																													if(isset($_SESSION['datdata'][$datfile]["data"][$name]))$ch='checked="checked"'; else $ch="";
																																												}

																																												$item='<td style="width:15px !important;"><input type="checkbox" '.$ch.' id="use_settings_'.$name.'" name="use_settings_'.$name.'" /></td>'.$item;
																																											}

																																											
																																											if($nohtml)return$itemstr;
																																											
																																											if($height)$height='style="height:'.$height.' important;width:100%;" ';
																																											
																																											if(!$option['ATTRIBUTES']['INLINE'])@$result='<tr '.$height.'id="'.$name.'" '.$hidden.' >';
																																											$result.=$item;
																																											
																																											if(!$option['ATTRIBUTES']['INLINE'])$result.="</tr>";
																																											return$result;
																																										}

																																										
																																										function switchval(&$val1,&$val2){
																																											$pom=$val1;
																																											$val1=$val2;
																																											$val2=$pom;
																																										}

																																										?>