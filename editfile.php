<?php 
	require_once("formvariables.php");
	function getfileidpath($object,$fileid,&$filename,&$commentfile,&$content=false,&$savetoapi=false){
		global$user,$oDomain,$remote,$cpath,$alang,$formapi;
		checkfileobjects();
		$api=createobject("api");
		$sInstall=$api->getProperty("C_InstallPath");
		
		if(strpos($fileid,$sInstall)!==false){
			$filename=str_replace($sInstall,"",$fileid);
			return$filename;
		}

		$savetoapi=false;
		
		if($_SESSION["ACCOUNT"]=='DOMAINADMIN')
		if(!checkdomainaccess($object)){
			popuperror($alang["ACCESS_ERROR"],false);
			$dieredirect='<body onload="window.open(\'index.html?messageid=TStrings_wa_str6\',\'_top\');"></body>';
			die($dieredirect);
			return;
		}

		switch($fileid){
			case"user_rules":
				$user->Open($object);
				$filename=$user->getProperty("U_fullmailboxpath").'filter.dat';
				break;
			case"domain_rules":
				$oDomain->open($object);
				$filename=$_SESSION['CONFIGPATH'].$oDomain->Name.'/filter.dat';
				break;
			case"remote_rules":
				$remote->open($object);
				$filename=$_SESSION['CONFIGPATH'].$remote->getProperty('RA_LeaveMessageFile').'.rules.dat';
				break;
			case"user_domainrights":
				$user->Open($object);
				$filename=getusermailboxpath($_SESSION["MAILPATH"],$user->GetProperty("u_mailboxpath"))."domain.dat";
				$commentfile=$_SESSION["INSTALLPATH"]."examples/domain.dat";
				break;
			case'spambox_view':
				case'quarantine_view':
					case"mailbox_view":
						
						if($user->Open($object))$filename=getusermailboxpath($_SESSION["MAILPATH"],$user->GetProperty("u_mailboxpath")); else $filename=$_SESSION["MAILPATH"].substr($object,strpos($object,'@')+1).'/';
						break;
					case"server_in":
						$user->Open($object);
						$filename=$_SESSION["MAILPATH"].'_incoming\\';
						break;
					case"user_message_file":
						$user=new MerakAccount();
						$user->Open($object);
						$content=$user->GetProperty("u_respondercontent");
						$commentfile=$_SESSION["INSTALLPATH"]."examples/variables.dat";
						$savetoapi=true;
						break;
					case"server_out":
						$user->Open($object);
						$filename=$_SESSION["MAILPATH"].'_outgoing\\';
						break;
					case"server_outretry":
						$user->Open($object);
						$filename=$_SESSION["MAILPATH"].'_outgoing\\retry\\';
						break;
					case"server_sms":
						$user->Open($object);
						$filename=$_SESSION["MAILPATH"].'_sms\\';
						break;
					case"user_noresponder":
						$user->Open($object);
						$filename=getusermailboxpath($_SESSION["MAILPATH"],$user->GetProperty("u_mailboxpath"))."norespond.dat";
						break;
					case"user_responder":
						$user->Open($object);
						$value=$user->GetProperty("u_respondwith");
						
						if(!$value){
							$filename=getusermailboxpath($_SESSION["MAILPATH"],$user->GetProperty("u_mailboxpath"))."responder.dat";
						} else {
							$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"].$user->Domain."/autoresp/");
						}

						$commentfile=$_SESSION["INSTALLPATH"]."examples/variables.dat";
						break;
					case"user_spammailbox":
						$user->Open($object);
						$filename=getusermailboxpath($_SESSION["MAILPATH"],$user->GetProperty("u_mailboxpath"))."spamadmin.dat";
						$commentfile=$_SESSION["INSTALLPATH"]."examples/spamadmin.dat";
						break;
					case"user_notification":
						$user->Open($object);
						$value=$user->GetProperty("u_validreport");
						
						if(!$value){
							$value=getaliasvalue($user->GetProperty("u_alias")).".txt";
							$user->SetProperty("u_validreport",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"].$user->Domain."/notify/");
						break;
					case"catalog_file":
						$user->Open($object);
						$value=$user->GetProperty("t_catalogfile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".clg";
							$user->SetProperty("t_catalogfile",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"notify_file":
						$user->Open($object);
						$value=$user->GetProperty("n_file");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias"))."/notify.txt";
							$user->SetProperty("n_file",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"list_file":
						$user->Open($object);
						$value=$user->GetProperty("M_ListFile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".txt";
							$user->SetProperty("M_ListFile",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"list_subscribers":
						$user->Open($object);
						$value=$user->GetProperty("M_AllowSubscribers");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".allow.txt";
							$user->SetProperty("M_AllowSubscribers",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"list_leave":
						$user->Open($object);
						$value=$user->GetProperty("M_LeaveFile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".leave.txt";
							$user->SetProperty("M_LeaveFile",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"list_join":
						$user->Open($object);
						$value=$user->GetProperty("M_SubListFile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".join.txt";
							$user->SetProperty("M_SubListFile",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"list_footer":
						$user->Open($object);
						$value=$user->GetProperty("M_footerfile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".footer.txt";
							$user->SetProperty("M_footerfile",$value);
							$user->Save();
						} else {
							$value=explode(";",$value);
							$value=$value[0];
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						$commentfile=$_SESSION["INSTALLPATH"]."examples/header.dat";
						break;
					case"list_footer_html":
						$user->Open($object);
						$value=$user->GetProperty("M_footerfile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".footer.txt";
							$user->SetProperty("M_footerfile",$value);
							$user->Save();
						} else {
							$value=explode(";",$value);
							$value=$value[1];
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						$commentfile=$_SESSION["INSTALLPATH"]."examples/header.dat";
						break;
					case"list_header":
						$user->Open($object);
						$value=$user->GetProperty("m_headerfile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".header.txt";
							$user->SetProperty("m_headerfile",$value);
							$user->Save();
						} else {
							$value=explode(";",$value);
							$value=$value[0];
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						$commentfile=$_SESSION["INSTALLPATH"]."examples/header.dat";
						break;
					case"list_header_html":
						$user->Open($object);
						$value=$user->GetProperty("m_headerfile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".header.txt";
							$user->SetProperty("m_headerfile",$value);
							$user->Save();
						} else {
							$value=explode(";",$value);
							$value=$value[1];
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						$commentfile=$_SESSION["INSTALLPATH"]."examples/header.dat";
						break;
					case"listserv_help":
						$user->Open($object);
						$value=$user->GetProperty("M_helpfile");
						
						if(!$value){
							$value=$user->Domain."/".getaliasvalue($user->GetProperty("u_alias")).".help.txt";
							$user->SetProperty("M_helpfile",$value);
							$user->Save();
						}

						$filename=getfilevarpath($value,$_SESSION["CONFIGPATH"]);
						break;
					case"account_bw":
						$user->Open($object);
						
						if(!$user->GetProperty("u_type"))$filename=getusermailboxpath($_SESSION["MAILPATH"],$user->GetProperty("u_mailboxpath"))."filter.dat"; else $filename=getusermailboxpath($_SESSION["MAILPATH"],$user->Domain."/".$user->GetProperty("u_alias")."/filter.dat");
						$commentfile=$_SESSION["INSTALLPATH"]."examples/filter.dat";
						break;
					case"domain_bw":
						$oDomain->Open($object);
						$filename=$_SESSION["CONFIGPATH"].$oDomain->Name."/filter.dat";
						$commentfile=$_SESSION["INSTALLPATH"]."examples/filter.dat";
						break;
					case"pokus_bw":
						$filename=$_SESSION["CONFIGPATH"]."filter.dat";
						$commentfile=$_SESSION["INSTALLPATH"]."examples/filter.dat";
						break;
					case"domain_headerfooter":
						$oDomain->Open($object);
						$filename=$_SESSION["CONFIGPATH"].$oDomain->Name."/tags.dat";
						$commentfile=$_SESSION["INSTALLPATH"]."examples/tags.dat";
						break;
					case"domain_accountdefaults":
						$oDomain->Open($object);
						$filename=$_SESSION["CONFIGPATH"].$oDomain->Name."/default.ini";
						$commentfile=$_SESSION["INSTALLPATH"]."examples/default.ini";
						break;
					case"remote_headers":
						$remote->Open($object);
						$value=$remote->GetProperty("RA_LeaveMessageFile");
						$filename=getfilevarpath($value.".hdr",$_SESSION["CONFIGPATH"]);
						break;
					case"remote_domains":
						$remote->Open($object);
						$value=$remote->GetProperty("RA_LeaveMessageFile");
						$filename=getfilevarpath($value.".dom",$_SESSION["CONFIGPATH"]);
						break;
					case"remote_routing":
						$remote->Open($object);
						$value=$remote->GetProperty("RA_LeaveMessageFile");
						$filename=getfilevarpath($value.".rt",$_SESSION["CONFIGPATH"]);
						$commentfile=$_SESSION["INSTALLPATH"]."examples/redirect.dat";
						break;
					case'domain_header':
						$oDomain->Open($object);
						$filename=$oDomain->GetProperty('D_HeaderFile');
						break;
					case'domain_footer':
						$oDomain->Open($object);
						$filename=$oDomain->GetProperty('D_FooterFile');
						break;
					case'domain_header_html':
						$oDomain->Open($object);
						$filename=$oDomain->GetProperty('D_HeaderHTMLFile');
						break;
					case'domain_footer_html':
						$oDomain->Open($object);
						$filename=$oDomain->GetProperty('D_FooterHTMLFile');
						break;
					default:
						
						if(substr($fileid,0,5)=='spam/')$filename=$_SESSION['SPAMPATH'].substr($fileid,5,strlen($fileid)-5); else
						if(substr($fileid,0,7)=='config/'){
							$filename=$_SESSION['CONFIGPATH'].substr($fileid,7,strlen($fileid)-7);
						} else {
							$filename=$_SESSION["INSTALLPATH"].securepath($fileid);
						}

						$commentfile=$_SESSION["INSTALLPATH"].$commentfile;
						break;
			}

		}

		
		function checkfileobjects(){
			global$user,$oDomain,$remote;
			
			if(!$user){
				$user=createobject("account");
				$remote=createobject("remoteaccount");
				$oDomain=createobject("domain");
			}

		}

		
		function processeditfile($object,$fileid,&$content,&$comment){
			$content="";
			$commentFile=$comment;
			$comment="";
			getfileidpath($object,$fileid,$filename,$commentFile,$content,$fromapi);
			
			if(!$content&&file_exists(trim($filename))){
				@$content=file_get_contents(trim($filename));
				
				if(substr($filename,strrpos($filename,'.')+1)=='xml'||strpos($filename,'webserver.dat')!==false){
					$content=htmlspecialchars($content);
				}

			}

			
			if($commentFile){
				$commentFile.=".html";
				
				if(file_exists($commentFile))$comment=trim(file_get_contents($commentFile));
				
				if(strpos($comment,'<pre>')!==false){
					$comment=nl2br(str_replace(array('<pre>','</pre>'),'',$comment));
				}

			}

			return$filename;
		}

		
		function processeditaction($object,$fileid,$content){
			$apicontent=$content;
			
			if(!file_exists($fileid))getfileidpath($object,$fileid,$filename,$commentfile,$content,$savetoapi); else $filename=$fileid;
			
			if($filename)mkdirtree(dirname($filename));
			
			if($savetoapi){
				$account=new MerakAccount();
				$account->Open($object);
				$account->SetProperty("u_respondercontent",$apicontent);
				$account->Save();
			} else {
				$fp=fopen($filename,"wb+");
				
				if($fp){
					
					if($content!="")fwrite($fp,$content."\r\n"); else fwrite($fp,"");
					fclose($fp);
				}

			}

			return$filename;
		}

		?>