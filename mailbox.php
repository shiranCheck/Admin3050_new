<?php 
	require_once("function.mime.php");
	define("mycharset",'charset');
	define("maxheader",0x20);
	define("maxmessagelist",2000);
	function getfileheader($file,$header,$blank=true){
		$text=trim(getfileheaderitem($file,$header));
		
		if(!$text)
		if(!$blank)$text.="*";
		$text=str_replace("\\","\\\\",$text);
		$text=str_replace("'","\'",$text);
		return$text;
	}

	
	function getfullpath($path,$u_spamfolder=false,$u_spamfoldervalue=false){
		global$user;
		
		if($u_spamfolder)$path.='~spam/';
		
		if($u_spamfolder===2)$path.='~quarantine/';
		
		if($u_spamfoldervalue)$path.=$u_spamfoldervalue.'/';
		$type=$user->GetProperty("u_accounttype");
		
		if(($type==1||$type==2)&&!$u_spamfolder){
			$path.="inbox/";
		}

		return$path;
	}

	
	function getfilelist($path,$sms=false){
		global$alang;
		$files=array();
		
		if(strpos($path,'outgoing')!==false){
			$type=(strpos($path,'retry')!==false)?$alang["TStrings_queuestatusretry"]:
			$alang["TStrings_queuestatussending"];
		}

		
		if(strpos($path,'incoming')!==false){
			$type=$alang["TStrings_queuestatusreceiving"];
		}

		
		if(!@$dp=opendir($path))return$files;
		while(@$file=readdir($dp))
		if($file!="."&&$file!=".."&&$file[0]!=".")
		if(!is_dir($path."/".$file))
		if(strpos($file,".tm$")||strpos($file,".imap")||strpos($file,".tmp")){
			$ffile=$path."/".$file;
			$item[id]=urlencode($file);
			
			if($sms){
				$info=file($ffile);
				$item[subject]='';
				$item[from]=trim(htmlspecialchars($info[3]));
				$item[to]=trim(htmlspecialchars($info[2]));
				$item[time]=date("Y-m-d H:i",filectime($ffile));
				$item[size]=(int)round(filesize($ffile)/1024);
			} else {
				$item[subject]=htmlspecialchars(getfileheader($ffile,"Subject:",false));
				$item[from]=htmlspecialchars(getfileheader($ffile,"From:"));
				$item[to]=htmlspecialchars(getfileheader($ffile,"To:"));
				$item[time]=date("Y-m-d H:i",filectime($ffile));
				$item[size]=(int)round(filesize($ffile)/1024);
			}

			$item['status']=$type;
			$files[]=$item;
			$count++;
			
			if($count>maxmessagelist)break;
		}

		@closedir($dp);
		return$files;
	}

	
	function processmailbox($object,$fileid,$u_spamfolder=false){
		global$user;
		checkMailboxView();
		checkuserobjects();
		getfileidpath($object,$fileid,$path,$comment);
		$path=getfullpath($path,$u_spamfolder);
		
		if($fileid=='server_sms'){
			$items=getfilelist($path,true);
		} else {
			$items=getfilelist($path);
		}

		return$items;
	}

	
	function checkMailboxView(){
		global$alang;
		
		if(isuserright("V")&&$_SESSION['ACCOUNT']=='DOMAINADMIN'){
			popuperror($alang["ACCESS_ERROR"],false);
			$dieredirect='<body onload="window.open(\'index.html?messageid=TStrings_wa_str6\',\'_top\');"></body>';
			die($dieredirect);
			return;
		}

	}

	
	function processmailboxaction($object,$fileid,$u_spamfolder=false){
		global$user,$itm;
		$api=createobject("API");
		checkMailboxView();
		checkuserobjects();
		getfileidpath($object,$fileid,$path,$comment);
		$path=getfullpath($path,$u_spamfolder);
		
		if($_REQUEST['deleteflag'])for($i=0;$i<count($itm);$i++)unlink($path.basename($itm[$i]));
		
		if($_REQUEST['actionflag']=="WHITELIST"){
			for($i=0;$i<count($itm);$i++){
				$file=$path.basename($itm[$i]);
				$sender=getfileheaderitem($file,"From:");
				
				if($pos=strpos($sender,"<")){
					$pos=$pos+1;
					$sender=substr($sender,$pos,strpos($sender,">")-$pos);
				}

				$list=$api->QuarantineList(''," WHERE SndOwner = '".$object."' AND SndEmail='".$sender."'",0);
				
				if(!$list)$api->QuarantineAdd($object,$sender,1); else $api->QuarantineSet($object,$sender,1);
			}

		}

		
		if($_REQUEST['actionflag']=="BLACKLIST"){
			for($i=0;$i<count($itm);$i++){
				$file=$path.basename($itm[$i]);
				$sender=getfileheaderitem($file,"From:");
				
				if($pos=strpos($sender,"<")){
					$pos=$pos+1;
					$sender=substr($sender,$pos,strpos($sender,">")-$pos);
				}

				$list=$api->QuarantineList(''," WHERE SndOwner = '".$object."' AND SndEmail='".$sender."'",0);
				
				if(!$list)$api->QuarantineAdd($object,$sender,0); else $api->QuarantineSet($object,$sender,0);
			}

		}

	}

	?>