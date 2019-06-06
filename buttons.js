// JavaScript Document - BUTTONS
function textfileedit(object, fileid)
{
  newwindow("editfile.html?object=" + object + "&fileid=" + fileid, "editfile");
}

function bwfileedit(object, fileid)
{
  newwindow("bwlist.html?object=" + object + "&fileid=" + fileid, "editfile");
}

function mailboxview(object, fileid)
{
  newwindow("mailbox.html?object=" + object + "&fileid=" + fileid, "editfile");
}
function xmlfileedit(filewithoutext){
  newwindow("editform.html?object="+filewithoutext);
}

function buttonedit(type,object,fileid,cfileid,width,height){
	var pars ="?object=" + object + "&fileid=" + fileid;
	var str ='';var sp='';
	if (cfileid!='') pars+="&cfileid=" + cfileid;
	var target = "_blank";
	switch(type){
	  case "":
	  case "manualbackup":
	  case "backup":
	  case "restore":
    case "fbutton":
        var param = getParameters(cfileid);
        if (type.toLowerCase()=="manualbackup") var param ="manualbackup";
        if (param!=null) str= "functionbutton.html"+pars+"&param="+param;
		    else str= "functionbutton.html"+pars;

		break;
		case "textfile":
			str= "editfile.html" + pars;
			break;
		case "bwfile":
			str= "bwlist.html" + pars;
			break;
		case "mailbox":
			str= "mailbox.html" + pars;
		break;
		case "linkbutton":
      

		  if (cfileid=='object'){
        if (arr = fileid.split("|")){
          str = "editform.html?object=" + arr[0] +"&tab=" + arr[1];
          document.cookie="lastnode=" + arr[2] + ";expires=Fri, 31 Dec 2099 23:59:59 GMT;";
        }else str = "editform.html?object=" + fileid;
      }else{
        arr = fileid.split("|");
        str = arr[0]+".html";
        document.cookie="lastnode=" + arr[1] + ";expires=Fri, 31 Dec 2099 23:59:59 GMT;";
      }
        
        target = "_self";
    break;
   	case "xmledit":
	  default:
      var param = getParameters(cfileid);
			str= "editform.html" + pars +"&param=" + param;
			break;
	}
	newwindow(str,target,width,height);
}

function getParameters(cfileid)
{
        if (!cfileid) return;
  	    var param = "";
  	    cfileid = cfileid.toLowerCase();
   		  var parameters = cfileid.split("|");
	      for (j=0;j<parameters.length;j++)
			  for (i = 0; i < document.forms[0].elements.length; i++){
          var e = document.forms[0].elements[i];
          var ename = e.name.toLowerCase();
          if (parameters[j] == ename) {
                    if (j<parameters.length-1) sp = "|";
                    else sp="";
                    if (e.type=="radio") { if (e.checked) param+=(e.value+sp);}
                    else param+=(e.value+sp);
          }         
        }
        return param;
}

