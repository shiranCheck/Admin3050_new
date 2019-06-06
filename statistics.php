<?php 
	
	function processstatistics($value,&$tcount,&$tamount){
		global$api;
		global$alang;
		global$skindata;
		checkdomainobjects();
		
		if(!checkdomainaccess($value)){
			popuperror($alang["ACCESS_ERROR"],false);
			$dieredirect='<body onload="window.open(\'index.html?messageid=TStrings_wa_str6\',\'_top\');"></body>';
			die($dieredirect);
		}

		
		if($selection=$_REQUEST['selection']){
			$days=array(1=>'30',2=>'60',3=>'90',4=>'180',5=>'270',6=>'365');
			$delta=$days[$selection];
			$from=date("Y/m/d",time()-86400*$delta);
			$to=date("Y/m/d",time());
		} else {
			$from=$_REQUEST['sfrom'];
			$to=$_REQUEST['sto'];
		}

		
		if(!$from){
			$from=date("Y/m/01",time());
		}

		
		if(!$to){
			$to=date("Y/m/d",time());
		}

		$str=$api->GetUserStatistics($from,$to,$value);
		$lines=explode(CRLF,$str);
		$items=array();
		$tcount=$tamount=0;
		$line=explode(",",$lines[0]);
		$aLanguage=explode("|",$alang["TStatisticsForm_UserStatList"]);
		foreach($aLanguage as$key=>$val){
			$aLanguage[$key]=addslashes($val);
		}

		$aTrans=array(1=>0,2=>7,3=>8,4=>9,5=>10,6=>11,7=>12,8=>-1,9=>-1,10=>1,11=>4,12=>5,13=>13,14=>2,15=>6);
		for($i=1;$i<16;$i++){
			$skindata['label'.$i]='<span title="'.$aLanguage[$aTrans[$i]].'">'.$aLanguage[$aTrans[$i]].'</span>';
		}

		for($i=1;$i<count($lines)-1;$i++){
			$line=explode(",",$lines[$i]);
			
			if($line[1]=='*')$res['i1']="*"; else $res['i1']=!isuserright("V")?sprintf("<a href=\"mailbox.html?object=%2\$s@%1\$s&fileid=mailbox_view\" target=\"_blank\">%2\$s</a>",$value,str_replace(CRLF," ",$line[1])):
			sprintf("%2\$s",$value,str_replace(CRLF," ",$line[1]));
			
			if($line[1]=="*"){
				$tcount=$line[11];
				$tamount=$line[12];
			}

			foreach($line as$key=>$val){
				
				if($key==3||$key==5||$key==9||$key==12){
					
					if(!$val)$val=0;
					$res['i'.$key]=(int)$val;
				} else
				if($key!=1)$res['i'.$key]=$val;
			}

			$res['i1']=str_replace("'","\'",$res["i1"]);
			$items[]=$res;
		}

		return$items;
	}

	?>