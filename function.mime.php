<?php 
	$nonconvert=array("us-ascii","windows-1255","windows-1256","utf-7");
	function getfileheaderitem($filename,$item){
		$result="";
		
		if(!(@$file=fopen($filename,"rb")))continue;
		while(getheaderline($file,$header,$line)){
			
			if(strtolower(trim($header))==strtolower(trim($item))){
				$result=ereg_replace("[\r\n\t]","",$line);
				break;
			}

		}

		return$result;
	}

	
	function getheaderline($file,&$header,&$value){
		while(!feof($file)){
			$line=fgets($file,1024);
			
			if(!trim($line))return false;
			
			if($pos=strpos($line,":")){
				$header=trim(substr($line,0,$pos+1));
				$value=substr($line,$pos+1);
				while(!feof($file)){
					$line=fgets($file,1024);
					
					if((ord($line[0])==9)||(ord($line[0])==32))$value.=rtrim($line); else {
						fseek($file,ftell($file)-strlen($line));
						break;
					}

				}

				$value=decodeMimeHeader($value);
				return true;
			}

		}

		return false;
	}

	
	function decodeMimeHeader($header){
		$converted='';
		foreach(imap_mime_header_decode($header)as$item){
			
			if($item->charset=='default'||$item->charset=='US-ASCII'){
				$converted.=$item->text;
			} else {
				$converted.=iconv($item->charset,'UTF-8//IGNORE',$item->text);
			}

		}

		return(string)$converted;
	}

	
	function getheader($header){
		global$navka;
		$header=rtrim($header);
		$i=0;
		while($pos=strpos($header,"=?")){
			
			if($i>300){
				$header="";
				break;
			}

			$i++;
			$fout.=substr($header,0,$pos);
			$text=strstr($header,"=?");
			
			if(eregi("^=\?([a-z0-9\-]+)\?(Q|B)\?=",substr($text,0,30)))$strt=strpos($text,"?=")+2; else $strt=0;
			
			if($endpos=strpos($text,"?=",$strt)){
				$header=substr($text,$endpos+2);
				$text=substr($text,0,$endpos);
			} else $header="";
			
			if(!eregi("^=\?([a-z0-9\-]+)\?(Q|B)\?(.*)",$text,$arr))break;
			$charset=$arr[1];
			$method=$arr[2];
			$text=$arr[3];
			
			if(strtoupper($method)=="Q"){
				$text=str_replace("_"," ",$text);
				$text=convertquoted($text);
			}

			elseif(strtoupper($method)=="B")$text=base64_decode($text);
			
			if(strcasecmp(mycharset,"us-ascii"))$text=charsetconvert($text,$charset,mycharset);
			$fout.=$text;
		}

		$fout.=$header;
		return$fout;
	}

	
	function convertquoted($string,$trim=true){
		$converted="";
		$i=0;
		while(true){
			
			if($i>=strlen($string))break;
			
			if($string[$i]=="="){
				$ch=chr(hexdec(substr($string,$i+1,2)));
				$converted.=($trim?(trim($ch)):
				($ch));
				$i+=2;
			} else {
				$converted.=$string[$i];
			}

			$i++;
		}

		return$converted;
	}

	
	function charsetconvert($text,$from,$to){
		global$nonconvert;
		
		if($from&&strtolower($from)==strtolower($to))return$text=eregi_replace("\?[a-z0-9\-]*\?Q\?","",$text);
		;
		
		if(strtolower($to)=="windows-1250"){
			$to="iso-8859-2";
			$to2="windows-1250";
		}

		elseif($to=="mycharset")$to=charset;
		
		if(strtolower($from)=="windows-1250"){
			$text=StrTr($text,"\x8A\x8D\x8E\x9A\x9D\x9E","\xA9\xAB\xAE\xB9\xBB\xBE");
			$from="iso-8859-2";
		}

		
		if($text&&$from&&$to&&!in_array(strtolower($to),$nonconvert)&&!in_array(strtolower($from),$nonconvert)){
			$text2=@mb_convert_encoding($text,$to,$from);
			$text=$text2?$text2:
			$text;
		}

		
		if(strtolower($to2)=="windows-1250"){
			$text=StrTr($text,"\xA9\xAB\xAE\xB9\xBB\xBE","\x8A\x8D\x8E\x9A\x9D\x9E");
		}

		$text=eregi_replace("\?[a-z0-9\-]*\?Q\?"," ",$text);
		return$text;
	}

	?>