function savenum()
{
   document.cookie="skinwidth="+x+";expires=Fri, 31 Dec 2099 23:59:59 GMT;";
}

window.size = function()
{
	var w = 0;
	var h = 0;

	//IE
	if(!window.innerWidth)
	{
		//strict mode
		if(!(document.documentElement.clientWidth == 0))
		{
			w = document.documentElement.clientWidth;
			h = document.documentElement.clientHeight;
		}
		//quirks mode
		else
		{
			w = document.body.clientWidth;
			h = document.body.clientHeight;
		}
	}
	//w3c
	else
	{
		w = window.innerWidth;
		h = window.innerHeight;
	}
	return {width:w,height:h};
}


function getScrollXY() {
  var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return [ scrOfX, scrOfY ];
}

function strankaX(elem) {
    return elem.offsetParent ? elem.offsetLeft + strankaX(elem.offsetParent) : elem.offsetLeft;
}

function strankaY(elem) {
    return elem.offsetParent ? elem.offsetTop + strankaY(elem.offsetParent) : elem.offsetTop;
}

function mdown()
{
  var hdiv = document.getElementById("hiddendiv");
  hdiv.style.display="block";
  hdiv.onmousemove = mmove;
  hdiv.onmouseup = mup;

  document.getElementById("hiddendiv").style.cursor="w-resize";
  return false;
}

function mup()
{
  document.getElementById("hiddendiv").style.display="none";
  savenum();
}

//Checkbox functions

function CheckAll(xx)
{
  if(!xx) xx="main";

  for (var i=0;i<document[xx].elements.length;i++)
  {
    var e=document[xx].elements[i];
    if (e.name != 'checkall' && e.type=="checkbox") e.checked=document[xx].checkall.checked;
  }

}



/**** SECTIONS - Lukas *****/
function makelist(){

  for (var ii=0;ii<document.getElementsByTagName("blockquote").length;ii++)
  {
     var mainelm = document.getElementsByTagName("blockquote")[ii];
     if(mainelm.className!="seclist") break;

		 var activesec =  getCookie("activesection");
     var toplist = document.createElement("div");
     mainelm.insertBefore(toplist,mainelm.firstChild);

     //help button
     var hlpbtn = document.createElement("img");
         hlpbtn.className = "helpbtn";
         hlpbtn.src = "skins/default/images/help.gif";

     var firsttab,firstsec,firsthlp,activetab;
		 var uactive = false;
     for (var i=0;i<mainelm.childNodes.length;i++)
     {
       var elm = mainelm.childNodes[i];
       if(elm.className=="section")
       {
          if(!elm.childNodes[0]) continue;
          var removed = elm.removeChild(elm.childNodes[0]);
          var eid = elm.id;
          elm.id= "section_" +eid;
          removed.unselectable=true;
          removed.id = "seclist_" + eid;
      	  if (elm.id == activesec){
		 	uactive = true;
		 	activetab = removed;
		  }
		  eval('removed.onclick=function(){show_section(this,"'+elm.id+'");document.cookie="activesection='+elm.id+';expires=Fri, 31 Dec 2099 23:59:59 GMT;";}');
	      toplist.appendChild(removed);

          if(!firsttab) {
             firsttab = removed;
             firstsec = elm.id;
          }
       }
     }
     //help button
	   toplist.appendChild(hlpbtn);
	 	 if (uactive) { firstsec = activesec;firsttab = activetab };
     if(firsttab && firstsec)	show_section(firsttab,firstsec);
   }
}

function show_section(me,elmid) {

     var ELMparent= document.getElementById(elmid).parentNode;
     var MEparent = me.parentNode;

     for (var i=0;i<ELMparent.childNodes.length;i++)
     {
       var chn = ELMparent.childNodes[i];
       if(chn.className=="section" && chn.style.display=="block")
       {
          chn.style.display="none";
          WM_removeClass(document.getElementById(chn.id.replace("section","seclist")),"sel_sectitle");
       }
     }

     var elm = document.getElementById(elmid);

     elm.style.display="block";
     WM_addClass(me,"sel_sectitle");
     /*** helpid ***/
     if(helpBtn = MEparent.lastChild){
		for (var i=0;i<elm.childNodes.length;i++)
		{
		 if (elm.childNodes[i] && elm.childNodes[i].className=="helpid"){
           	if ((helpid = elm.childNodes[i].innerHTML)!==""){
              helpBtn.onclick = function(){ show_help(helpid); };
			  helpBtn.style.display = "inline";
            }
         }
		}
     }
	ZDataGridRSZ();
}

function confirmdelete(){
  var agree=confirm(alang["AREYOUSURE"]);
  if (agree) { document.form.deleteflag.value = 1; return true; } else { document.form.deleteflag.value = 0; return false; }
}
function confirmaction(action){
  var agree=confirm(alang["AREYOUSURE"]);
  if (agree) { document.form.actionflag.value = action; return true; } else { document.form.actionflag.value = 0; return false; }
}


function mmove(e)
{

    if (!e) mousePosition = window.event.clientX;
    else mousePosition = e.pageX;

    if(mousePosition<159 || mousePosition>400) return false;

      x=mousePosition-2;
      b=3;
      setSkinSize(x,b);
	  return false;

}
function setSkinSize(x,b)
{
  document.getElementById("folderdiv").style.width= (x-b) +"px";
  document.getElementById("vscroll").style.left= x +"px";
  document.getElementById("viewdiv").style.padding= 63 +"px "+b+"px "+b+"px "+ (x+5) +"px";
  document.getElementById("readdiv").style.padding= "0px "+b+"px 0px "+ (x+5) +"px";
}

function str_replace(str,find,repl)
{
  while(1)
  {
    if(str.indexOf(find)==-1) return str;
    str = str.replace(find,repl);
  }
}

//Rover
function drawToolTip(html,width,height,elm,fs)
{
  if(window.__tool_timeout)
      clearTimeout(window.__tool_timeout);

  if(!width) width = 320;
  if(!height) height = 200;

  //Check for tooltip existence
  var div = document.getElementById('_webadmin_tool_tip');

  if(!div){
    var div = document.createElement('div');
    div.id = '_webadmin_tool_tip';
}
if (!fs)
{
    div.style.position = 'absolute';
    div.style.zindex = 10;
    div.style.padding = 5;
    div.style.background = "#FFFFEC";
    div.style.border = "1px solid #FC4";
    div.style.cursor = "pointer";

    div.style.width = width;
    div.style.height = height;

	var xy=getScrollXY();
	
	var size=window.size();
	
	var ch=size['height'];
	var cw=size['width'];
	
	var chx=document.documentElement.clientHeight;
	var cwx=document.documentElement.clientWidth;
	if (document.all)
	{
		chx=document.body.offsetHeight;
		cwx=document.body.offsetWidth-20;
	}

    mouseX = window.event.clientX+xy[0]+3;
    mouseY = window.event.clientY+xy[1]+3;

	if ((mouseX+width+10)>cw)
	{
		mouseX=cwx-10-width;
	}
	if ((mouseY+height+10)>ch)
	{
		mouseY=ch-10-height;
	}

	//alert(mouseX+' '+cwx+' '+width+' '+document.height);

    div.style.left = mouseX+"px";
    div.style.top = mouseY+"px";
    div.onmouseover = function(){
      drawToolTip(html,width,height,elm,true);
    }
    div.onmouseout = function() {closeToolTip(fs);};
	div.onclick = function() {closeToolTip(fs);};
}
else
{
	div.onmouseover = function(){}
	div.onmouseout = function() {closeToolTip(fs);};
	div.onclick = function() {closeToolTip(fs);};
}


  document.body.appendChild(div);
  div.innerHTML = html;

}

function closeToolTip(e)
{
  window.__tool_timeout = setTimeout('closeToolTip2()',200);
}
function closeToolTip2()
{
  var div = document.getElementById('_webadmin_tool_tip');
  if(div)
    div.parentNode.removeChild(div);
}




