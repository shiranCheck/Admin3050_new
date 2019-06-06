<?php
	
	class ParseXML{
		function GetChildren($vals,&$i){
			$children=array();
			
			if(isset($vals[$i]['value'])){
				$children['VALUE']=$vals[$i]['value'];
			}

			while(++$i<count($vals)){
				switch($vals[$i]['type']){
					case'cdata':
						
						if(isset($children['VALUE'])){
							$children['VALUE'].=$vals[$i]['value'];
						} else {
							$children['VALUE']=$vals[$i]['value'];
						}

						break;
					case'complete':
						
						if(isset($vals[$i]['attributes'])){
							$children[$vals[$i]['tag']][]['ATTRIBUTES']=$vals[$i]['attributes'];
							$index=count($children[$vals[$i]['tag']])-1;
							
							if(isset($vals[$i]['value'])){
								$children[$vals[$i]['tag']][$index]['VALUE']=$vals[$i]['value'];
							} else {
								$children[$vals[$i]['tag']][$index]['VALUE']='';
							}

						} else {
							
							if(isset($vals[$i]['value'])){
								$children[$vals[$i]['tag']][]['VALUE']=$vals[$i]['value'];
							} else {
								$children[$vals[$i]['tag']][]['VALUE']='';
							}

						}

						break;
					case'open':
						
						if(isset($vals[$i]['attributes'])){
							$children[$vals[$i]['tag']][]['ATTRIBUTES']=$vals[$i]['attributes'];
							$index=count($children[$vals[$i]['tag']])-1;
							$children[$vals[$i]['tag']][$index]=array_merge($children[$vals[$i]['tag']][$index],$this->GetChildren($vals,$i));
						} else {
							$children[$vals[$i]['tag']][]=$this->GetChildren($vals,$i);
						}

						break;
					case'close':
						return$children;
				}

			}

		}

		
		function GetXMLTree($xmlloc){
			
			if(file_exists($xmlloc)){
				$data=implode('',file($xmlloc));
			} else {
				echo'FILE NO EXIST :'.$xmlloc;
			}

			$parser=xml_parser_create('UTF-8');
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$vals,$index);
			xml_parser_free($parser);
			$tree=array();
			$i=0;
			
			if(isset($vals[$i]['attributes'])){
				$tree[$vals[$i]['tag']][]['ATTRIBUTES']=$vals[$i]['attributes'];
				$index=count($tree[$vals[$i]['tag']])-1;
				$tree[$vals[$i]['tag']][$index]=array_merge($tree[$vals[$i]['tag']][$index],$this->GetChildren($vals,$i));
			} else {
				$tree[$vals[$i]['tag']][]=$this->GetChildren($vals,$i);
			}

			return$tree;
		}

		
		function GetXMLTreeFromString($data){
			$parser=xml_parser_create('UTF-8');
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$vals,$index);
			xml_parser_free($parser);
			$tree=array();
			$i=0;
			
			if(isset($vals[$i]['attributes'])){
				$tree[$vals[$i]['tag']][]['ATTRIBUTES']=$vals[$i]['attributes'];
				$index=count($tree[$vals[$i]['tag']])-1;
				$tree[$vals[$i]['tag']][$index]=array_merge($tree[$vals[$i]['tag']][$index],$this->GetChildren($vals,$i));
			} else {
				$tree[$vals[$i]['tag']][]=$this->GetChildren($vals,$i);
			}

			return$tree;
		}

		
		function Array2XML($arr,$br=0,$upper=false){
			
			if(!is_array($arr))return;
			foreach($arr as$k0=>$v0){
				
				if($upper)$k0=strtoupper($k0);
				
				if(!is_array($v0))continue;
				foreach($v0 as$k1=>$v1){
					$out.=($br?"\r\n":
					'')."<$k0";
					
					if(is_array($v1['ATTRIBUTES'])){
						foreach($v1['ATTRIBUTES']as$kAttr=>$vAttr)$out.=' '.($upper?strtoupper($kAttr):
						$kAttr).'="'.$vAttr.'"';
						unset($v1['ATTRIBUTES']);
					}

					
					if(!count($v1)||(count($v1)==1&&isset($v1['VALUE'])&&$v1['VALUE']==="")){
						$out.='/>';
						continue;
					} else $out.='>';
					
					if(is_array($v1)&&isset($v1['VALUE'])){
						$out.=$v1['VALUE'];
						unset($v1['VALUE']);
					}

					
					if(count($v1))foreach($v1 as$kChild=>$vChild){
						$tmparr[$kChild]=$vChild;
						$out.=ParseXML::Array2XML($tmparr,$br,$upper);
						unset($tmparr);
					}

					$out.="</$k0>";
				}

				return$out;
			}

		}

	}