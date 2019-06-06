<?php
	require_once'cmtreenode.php';
	require_once'inc/clsparsexml.php';
	require_once'function.php';
	class cMTree{
		var$Root;
		var$Imode;
		var$CurrentPath;
		function cMTree($mode='basic'){
			$this->Root=new cMTreeNode(NULL,'ROOT',$mode);
			$this->Imode=$mode;
			$this->MTLoad();
		}

		
		function MTAddNode(&$node,$val,$key){
			$node->Childs[$key]=new cMTreeNode($val,$key);
			$node->Childs[$key]->Location=$node->Location.$node->mtnId;
		}

		
		function MTLoad(){
			$parser=new ParseXML();
			$file='xml/interface/'.$this->Imode.'.xml';
			
			if(!file_exists($file)){
				$file='xml/interface/basic.xml';
				$this->Imode='basic';
			}

			
			if($_SESSION["ACCOUNT"]=='DOMAINADMIN'){
				$file='xml/interface/domainadmin.xml';
				$this->Imode='domainadmin';
			}

			
			if($_SESSION["ACCOUNT"]=='GATEWAY'){
				$file='xml/interface/gateway.xml';
				$this->Imode='gateway';
			}

			
			if($_SESSION["ACCOUNT"]=='USER'){
				$file='xml/interface/user.xml';
				$this->Imode='user';
			}

			$_SESSION['imode']=$this->Imode;
			$MXML=@$parser->GetXMLTree($file);
			
			if($MXML['MENU'][0]['PARAM'])foreach($MXML['MENU'][0]['PARAM']as$Mkey=>$Mval){
				$this->MTAddNode($this->Root,$Mval,$Mval['ATTRIBUTES']['ID']);
				$this->MTHandleXMLNode($this->Root->Childs[$Mval['ATTRIBUTES']['ID']],$Mval);
			}

		}

		
		function MTPrint($id,&$idval){
			$mtNodeCount=0;
			foreach($this->Root->Childs as$node_key=>$node_val){
				$node_val->MTNPrint($node_val->mtnID,@$node_val->pData["FOLDER"],$mtNodeCount,$inarray);
				
				if($node_val->pData['ID']==$id)$idval='sLI'.$inarray[$mtNodeCount]['root'];
			}

			$_ret=$inarray;
			return$_ret;
		}

		
		function MTHandleXMLNode(&$node,$xml){
			
			if(isset($xml['PARAM'][0]))foreach($xml['PARAM']as$node_key=>$node_val){
				
				if($node_val["ATTRIBUTES"]["ID"]=='SPAMQUEUE'&&$_SESSION['ACCOUNT']=='DOMAINADMIN'&&!isuserright("Q"))continue;
				$this->MTAddNode($node,$node_val,$node_val['ATTRIBUTES']['ID']);
				$this->MTHandleXMLNode($node->Childs[$node_val['ATTRIBUTES']['ID']],$node_val);
			}

		}

		
		function MTGetNode($nodeid,$rootnode){
			foreach($rootnode->Childs as$key=>$val){
				
				if($val->pData['ID']==$nodeid){
					$ret=$val;
					$this->CurrentPath=$nodeid.$this->CurrentPath;
					break;
				}

				
				if(isset($val->Childs))$ret=$this->MTGetNode($nodeid,$val);
				
				if(@$ret){
					break;
				}

			}

			
			if(@$ret&&$rootnode->pData['ID']!="")$this->CurrentPath=$rootnode->pData['ID'].'/'.$this->CurrentPath;
			return@$ret;
		}

		
		function MTPrintMenu($node){
			global$alang,$skin_dir;
			$ret='';
			
			if(isset($node->Childs))foreach($node->Childs as$key=>$val){
				
				if(@$val->pData["DISABLE"]!=$_SESSION["ACCOUNT"]){
					
					if($val->pData['FILE']=='mainframe.html')$href='mainframe.html?treenode='.$val->pData['ID']; else $href='mainframe.html?file='.$val->pData['FILE'];
					
					if($val->pData['ID']=='DOMAINS')$href.='&dac=1';
					$ret.='<div class="odkaz"><a onclick="setLastNode(\''.$this->CurrentPath.'/'.$val->pData['ID'].'\');setInfoValue(\''.$skin_dir.'/images/tree/large/'.$val->pData['ICON'].'\',\''.$alang[$val->pData['LABEL']].'\');" href="'.$href.'"><center><img src="'.$skin_dir.'/images/tree/large/'.$val->pData['ICON'].'" /><div>'.$alang[$val->pData['LABEL']].'</div></center></a></div>';
				}

			}

			return$ret;
		}

	}

	?>