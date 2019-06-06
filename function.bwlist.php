<?php 
	
	function get_filterpath(){
		global$BW_path,$user,$imap;
		
		if($BW_path)$path=$BW_path;
		elseif(function_exists('getmailboxpath'))$path=@getmailboxpath($user,$imap,false)."filter.dat"; else false;
		mkdirtree(dirname($path));
		return$path;
	}

	
	function get_sfilter($user,$basic=false){
		global$lang,$cantshow;
		
		if(!$path=get_filterpath())return;
		$spam=array();
		
		if(!file_exists($path))return$spam;
		$fp=fopen($path,"rb");
		$iii=$ii=$NOdefault=0;
		while(!feof($fp)){
			$line=trim(fgets($fp,1000));
			
			if(strlen($line)<=0)continue;
			
			if($basic&&!ereg("[0-4]",$line[0])){
				$cantshow=1;
				$ii++;
				continue;
			}

			$spam[$ii]['key']=++$iii;
			
			if(strlen($line)==1&&ereg("[0-9]",$line)){
				$NOdefault=$line;
				$spam[$ii][fd]=1;
				$spam[$ii][fdefa]=$NOdefault;
				$spam[$ii][f]=$lang["SPAM_DEFAULTACTIONFIB"];
				$spam[$ii][n]=$NOdefault;
				$ii++;
				continue;
			}

			$lines=explode("{(0)=",$line);
			$iLog=0;
			foreach($lines as$line){
				
				if(ereg("^[\&\|]}",$line)){
					
					if($line[0]=="&")$spam[$ii][log]="AND"; else $spam[$ii][log]="OR";
					$line=substr($line,2);
				} else $spamlog="";
				$i=0;
				$n1=$NOdefault;
				while(""!=($word=substr($line,$i,1))){
					
					if(ereg("[0-4]",$word)&&!$i){
						$n1=$word;
						$i++;
						continue;
					}

					
					if(strpos($word,"(")>-1&&strpos($line,")")>-1){
						$plusko=strpos($line,")")-$i-1;
						$spam[$ii][ff]=substr($line,$i+1,$plusko);
						$i+=($plusko+2);
						unset($plusko);
						continue;
					}

					
					if(ereg("[A-Z\*]",$word)){
						$f1=$word;
						$i++;
						continue;
					}

					switch($word){
						CASE"!":
							$f4="$word";
							break;
						CASE"~":
							$f3="$word";
							break;
						CASE"&":
							$f3="$word";
							break;
						CASE"^":
							$f3="$word";
							break;
						CASE"{":
							$f3="$word";
							break;
						CASE"}":
							$f3="$word";
							break;
						CASE"=":
							$f3="$word";
							break;
						CASE'$':
							$f2="$word";
							break;
				}

				
				if(ereg("[\{\}\=\^\~\&]",$word)){
					$spam[$ii]["t"]=substr($line,$i+1);
					break;
				}

				$i++;
			}

			
			if(!$f1)$f1="H";
			$spam[$ii]["n"]=@$spam[$ii]["log"]?'T':
			$n1;
			
			if($f3)@$spam[$ii]["f"]=$lang["SPAM_".($f1=="*"?'STAR':
			$f1)].($f1=="*"?'':
			(' '.$lang["SPAM_".$f3].' '.$lang["SPAM_STRING"].' '.($f4=='!'?'NOT':
			'')));
			elseif(!$f3){
				$spam[$ii]["f"]=$lang["SPAM_STANDARD"];
				$spam[$ii]["t"]=eregi_replace("^([0-4]{1}(\(.*\)){0,1}\:)","",$line);
				$spam[$ii]["fd"]=2;
				$f1="";
			}

			@$spam[$ii]["fdef"]=$NOdefault;
			@$spam[$ii]["f1"]=$f1;
			@$spam[$ii]["f2"]=$f2;
			@$spam[$ii]["f3"]=$f3;
			@$spam[$ii]["f4"]=$f4;
			unset($n1,$f1,$f2,$f3,$f4);
			
			if($basic){
				
				if(($spam[$ii]['ff']&&!eregi("C,(I,{0,1}){0,1}(M=(.*),){0,1}(F=([%.]*)){1}",$spam[$ii]['ff']))||$spam[$ii]['fd']==1||$spam[$ii]['fd']=="P"||($spam[$ii]['f3']!=="~"&&$spam[$ii]['f1']!=="G")||!ereg("[\*HAMG]",$spam[$ii]['f1'])||$spam[$ii]['defa']!=""||($spam[$ii]['f1']==="H"&&!eregi("^(Subject\:)|(From\:)|(To\:)",$spam[$ii]['t']))){
					$iix=$ii+1;
					while($iix){
						$iix--;
						
						if(!$spam[$iix]["log"]){
							unset($spam[$iix]);
							break;
						} else unset($spam[$iix]);
					}

					$cantshow=1;
					$ii++;
					break;
				}

			}

			$ii++;
		}

		
		if($spam[$ii-1]["n"]==='T')$spam[$ii-1]["n"]='L';
	}

	fclose($fp);
	return$spam;
}


function deleterow($user,$num){
	
	if(!$path=get_filterpath())return;
	
	if(file_exists($path)){
		$fp=fopen($path,"rb");
		$i=1;
		while(!feof($fp)){
			$line=trim(fgets($fp,1000));
			
			if(strlen($line)<=0)continue;
			
			if($i!=$num)$data.=$line."\r\n";
			$i++;
		}

		fclose($fp);
		$fp=fopen($path,"wb");
		fputs($fp,$data);
		fclose($fp);
	}

}


function addsfilter($user,$Raction,&$def,$defa,$R1,$Do1x,$Do2x,$Cnotx,$Tstringx,$Ccasex,$logx,$Tflag,$no="",$basic=false){
	
	if(!$path=get_filterpath())return;
	
	if($def==""&&!$Tflag)$def=0;
	
	if(!$no&&$defa)$def=$defa;
	foreach($logx as$key=>$val){
		$Do1=$Do1x[$key];
		$Do2=$Do2x[$key];
		$Cnot=$Cnotx[$key];
		$Tstring=$Tstringx[$key];
		$Ccase=$Ccasex[$key];
		$log=$logx[$key];
		
		if(!$log)continue;
		elseif($log!=1)$inline.='{(0)='.($log=='AND'?'&':
		'|').'}';
		
		if($Do1==="")$Do2=$Cnot=$Ccase=""; else {
			switch($Do1){
				CASE'G':
					$Do2="~";
					break;
				CASE'L':
					$Do2="~";
					break;
				CASE'T':
					$Do2="~";
					$Tstring="";
					break;
				CASE'*':
					$Cnot=$Ccase=$Tstring="";
					$Do2="~";
					break;
				CASE'M':
					$Cnot=$Ccase=$Tstring="";
					$Do2="~";
					break;
				CASE'A':
					$Ccase="";
					break;
				CASE'HS':
					$Do1="";
					$Tstring="Subject: $Tstring";
					break;
				CASE'HT':
					$Do1="";
					$Tstring="To: $Tstring";
					break;
				CASE'HF':
					$Do1="";
					$Tstring="From: $Tstring";
					break;
				CASE'H':
					$Do1="";
					break;
		}

	}

	
	if($R1){
		
		if($def==$Raction&&!$basic&&!$Tflag)$inline.=$Do1.$Ccase.$Cnot.$Do2.$Tstring; else $inline.=($log=='1'?($Raction.(trim($Tflag)!=''?'('.trim($Tflag).')':
		'').":"):
		'').$Do1.$Ccase.$Cnot.$Do2.$Tstring;
	} else {
		$inline.=$Raction;
		$def=$Raction;
		break;
	}

}


if(file_exists($path)){
	$fp=fopen($path,"rb");
	$i=1;
	while(!feof($fp)){
		$line=trim(fgets($fp,1000));
		
		if(strlen($line)<=0)continue; else $last=$line;
		
		if($i!=$no)$data.=$line."\r\n"; else {
			$nox=$i;
			$data.="$inline\r\n";
		}

		$i++;
	}

	fclose($fp);
}


if($last!=$inline){
	
	if(!$no){
		$nox=$i;
		$data.="$inline\r\n";
	}

	
	if(!is_dir(dirname($path)))mkdir(dirname($path));
	$fp=fopen($path,"wb");
	fputs($fp,$data);
	fclose($fp);
}

return$nox;
}


function supdown($user,$no,$up){
	
	if(!$path=get_filterpath())return;
	
	if(file_exists($path)){
		
		if(($up&&$no!=1)||!$up){
			$fp=fopen($path,"rb");
			$i=1;
			while(!feof($fp)){
				$line=trim(fgets($fp,1000));
				
				if(strlen($line)<=0)continue;
				$xdata[$i]=$line."\r\n";
				$i++;
			}

			fclose($fp);
			
			if($up){
				$trans=$xdata[$no-1];
				$xdata[$no-1]=$xdata[$no];
				$xdata[$no]=$trans;
				$no-=1;
			}

			elseif($xdata[$no+1]){
				$trans=$xdata[$no+1];
				$xdata[$no+1]=$xdata[$no];
				$xdata[$no]=$trans;
				$no+=1;
			}

			
			if($trans){
				$xval=implode("",$xdata);
				$fp=fopen($path,"wb");
				fputs($fp,$xval);
				fclose($fp);
			}

		}

	}

	return$no;
}

?>