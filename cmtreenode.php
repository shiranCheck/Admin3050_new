<?php
	require_once('inc/wizards.php');
	class cMTreeNode{
		var$pData;
		var$Childs;
		var$mtnCollapse;
		var$mtnID;
		function cMTreeNode($val,$key){
			$this->pData=$val["ATTRIBUTES"];
			
			if(!isset($this->pData["FILE"]))$this->pData["FILE"]='mainframe.html';
			$this->mtnCollapse=0;
			$this->mtnID=$key;
		}

		
		function MTNPrint($location,$directory,&$mtNodeCount,&$inarray){
			global$alang;
			global$skin_dir;
			$mtNodeCount++;
			
			if($this->pData['ALANG']==1)$label=$this->pData['LABEL']; else $label=$alang[$this->pData['LABEL']];
			$item["name"]=$label;
			$item["data"]=count($this->Childs);
			$item["root"]=$location;
			$this->pData['root']=$location;
			
			if($this->pData["FILE"]=='mainframe.html'){
				$item["alink"]=$directory.$this->pData["FILE"].'?treenode='.$this->pData["ID"];
				
				if($this->pData["ID"]=='DOMAINS')$item["alink"].='&dac=1&menulink=1';
			} else @$item["alink"]=$directory.$this->pData["FILE"];
			@$item["target"]=$this->pData["TARGET"]?$this->pData["TARGET"]:
			'main_win';
			@$item["ico"]='images/tree/'.$this->pData["ICON"];
			@$item["modes"]=explode("|",$this->pData["MODE"]);
			
			if($this->pData["ALINK"])$item["alink"]=$this->pData["ALINK"];
			$item['js']='setLastNode(this.id);sethelpid("'.$this->pData["HELPID"].'");setInfoValue("'.$skin_dir.'images/tree/large/'.$this->pData["ICON"].'","'.($label).'");';
			$inarray[]=$item;
			
			if(isset($this->Childs))foreach($this->Childs as$node_key=>$node_val){
				$loc=$location.'/'.$node_val->pData["ID"];
				@$dir=$directory.$node_val->pData["FOLDER"];
				$node_val->MTNPrint($loc,$dir,$mtNodeCount,$inarray);
			}

		}

	}

	?>