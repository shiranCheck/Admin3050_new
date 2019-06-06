
/////////////////////////////////////////
// DataGrid Object                     //
// Date: 10.07.2005                    //
// Creator: Lukas Dvorak               //
//          kis@advnet.cz              //
/////////////////////////////////////////

expiration = new Date;
expiration.setTime(expiration.getTime()+(86400000*365));

function ZDataGrid(targetID){
  this.doc= document;           //for frames? ;)
  this.targetID = targetID;     //Target element id


  this.headData=[];  //header data array
  this.gridData=[];  //body data array

  // header
  this.dg_header2;   //header parent div
  this.dg_header;    //header data div
  this.hii=0;        //no of columns

  // body
  this.dg_body;
  this.dg_data;      //data div (bodys child), +1px height
  this.scrBarSize;   //scrollbar width

  // resize          global variables for column resize
  this.melm;
   this.melmw;
  this.sibl;
   this.siblw;
  this.col1;
  this.col2;
  this.col_no;
  this.mouse;
      //old document. action
      this.oldmove=this.doc.onmousemove;
      this.oldup=this.doc.onmouseup;

  this.staticpx = 0; //size[px] of static columns
  // sort
  this.lastSort=-1;  //no of last sorted column (for ASC/DESC)

  //limit            global variables for listing
  this.page=0;

  this.limit=0;
  this.limitLang;
  this.limitArray;

  //Search
  this.searchColumn;  // index from 0
  this.search ='';    // last search string
  this.searchLang=''; // language "search"
  this.rows = 0;

  //Strict HTML
  this.STgridbd =0;  //bordered grid in px
}

  ZDataGrid.prototype.addElm = function(type,css,eid,eatt){
     var elm = this.doc.createElement(type);
         if (css) elm.className = css;
         if (eid) elm.id = eid;
         if (typeof eatt == 'object'){
               for (var i in eatt){
                   try{
                      switch(i){
                         case 'href': elm[i] = eatt[i]?eatt[i]:'javascript: void(0);';
                                      break;
                         case 'name': elm[i] = eatt[i]; break;
                         default    : elm[i] = function(){eval(eatt[i]);};
                      }
                   }
                   catch(e){}
               }
         }
     return elm;
  }

  ZDataGrid.prototype.drawGrid =function(){

     //var page = getCookie("dg_cols_"+this.uniqueid+"_page");
     //if(page) this.page = page;

     //draw grid HTML
     this.dg_header2 = this.addElm("div","dg_header2");
     this.dg_header = this.addElm("div","dg_header");
     this.dg_body = this.addElm("div","dg_body");
     this.dg_data = this.addElm("div","dg_data");

     this.doc.getElementById(this.targetID).appendChild(this.dg_header2);
     this.dg_header2.appendChild(this.dg_header);
     this.dg_body.appendChild(this.dg_data);
     this.doc.getElementById(this.targetID).appendChild(this.dg_body);

     //set dg_body & dg_data height
     var h=document.getElementById(this.targetID).clientHeight - this.dg_header2.offsetHeight;
     //alert(h+"/"+this.doc.getElementById(this.targetID).clientHeight+'/'+this.dg_header2.offsetHeight);
     if (h<300) {h=300;}
     this.dg_body.style.height=(h)+'px';
     this.dg_data.style.height=(h+1)+'px';

     //unify dg_header and dg_data width
     this.scrBarSize = this.dg_body.offsetWidth-this.dg_data.clientWidth;
     this.scrBarSize = this.scrBarSize?this.scrBarSize:25;

     //scroll body
     var dg_header = this.dg_header;
     this.dg_body.onscroll = function(){
        dg_header.style.left = this.scrollLeft*-1;
     }

  }

  ZDataGrid.prototype.fillHeader = function(){
     var x1,x2,x3,c,hii=0,sorted,cwidth,data = this.headData,targetID = this.targetID; ;
     this.hii=0;
     this.lastSort=-1;

     var colSize = getCookie("dg_cols_"+this.uniqueid);
     if(colSize)
      var colArr = colSize.split(",");

      if(colArr){
        for (var cv in colArr)
          data[cv][1] = 1*colArr[cv];
      }
     //resize static width sum
     while(data[hii]){
          if(data[hii][1]<0) this.staticpx-=data[hii][1];
          hii++;
     }

     for (this.hii=0;this.hii<data.length;this.hii++){
           if(typeof data[this.hii] != 'object') continue;

           x1 = this.addElm('div','col_'+this.hii,this.targetID+'_hdr_'+this.hii,'','');

           if(data[this.hii][2]){
             x2 = this.addElm('a','',targetID+'_hdra_'+this.hii,{'href':''});
             x2.onclick = function(){GridObj[targetID].sortMe(this); };
           }
           else
             x2 = this.addElm('span');

           x2.innerHTML = data[this.hii][0] || '&nbsp;';

           if(data[this.hii][3]){
             x3 = this.addElm('div','','resizer_'+targetID+'_hdra_'+this.hii);
             x3.onmousedown = function(){GridObj[targetID].resizeMe(this);}
           }

           this.dg_header.appendChild(x1);
           x1.appendChild(x2);
           if(x3) x1.appendChild(x3);

           // Draw columns
           // class="col col_[n]" id="[target]_col_[n]"
           c = this.addElm("div","col col_" + this.hii,targetID + "_col_" + this.hii,'','');
           this.dg_data.appendChild(c);

           // sort if default
           if(data[this.hii][2] && data[this.hii][5] && !sorted){ sorted = 1; this.sortMe(x2); }

        x1=x2=x3=null;
     }
     this.rePosition();
  }
  ZDataGrid.prototype.countItems = function(){
	 return this.gridData.length;
  }
  ZDataGrid.prototype.fillBody = function(){
     var i=0,ii=0,elm,css,ide,x=[];
     var limit = getCookie("dg_cols_"+this.uniqueid+"_limit");
     if(limit) this.limit = limit;

     //searching
     if (this.search)
     {
         var sVal,rRe = new RegExp(this.search,'gi');
         var data = [];

         for (var i in this.gridData){
              sVal = this.gridData[i][3][(this.searchColumn*2)+1];
              if(sVal.match(rRe)) data.push(this.gridData[i]);
         }
     }
     else
         var data = this.gridData.slice(0,this.gridData.length);


    this.rows = data.length;

     //pageing

     if(this.limit)
     {
       if(data.length<=this.page*this.limit){
         if(this.page*this.limit==data.length && this.page)
           this.page--;
         else
           this.page = Math.floor(data.length/this.limit);
        }

       data = data.slice(this.page*this.limit*1,parseInt((this.page*this.limit*1)+(this.limit*1)));
     }


     //clear table columns
     var x = this.dg_data.childNodes;
     for(i in x) x[i].innerHTML="";

     // rows
     // class="row row-[row no%2] row_[row no] crow_[column no]" id="[dataId]_[target]_[n]"

     for (i=0;i<data.length;i++){
        if(typeof data[i] != 'object') continue;

        css = 'row row-'+(i%2)+' row_' + i + (data[i][1]?' '+data[i][1]:'');


        ide = data[i][0]+'_'+this.targetID+'_';
        ii=0;
        while((ii/2)<this.hii){
           if(ii%2){ ii++; continue;}
           elm = this.addElm('div',css+' crow_'+(ii/2),ide+(ii/2),data[i][2]);
           elm.innerHTML=data[i][3][ii]?data[i][3][ii]:'';

           if (x[(ii/2)]!=null) x[(ii/2)].appendChild(elm);

           ii++;
        }

     }

  }

  // ELEMENT position
  ZDataGrid.prototype.elmPos = function(elm){
    var elmns = elm.parentNode.childNodes;
    i=0;
    while(elmns[i]){
       if(elmns[i]==elm) return i;
       i++;
    }
    return null;
  }

//********************
//**** Resizeing *****
//********************

  ZDataGrid.prototype.resizeMe = function(elm){
    if(!this.dg_data || !this.dg_data.hasChildNodes || !elm || elm.tagName!="DIV") return;
    var ev = window.event;



    this.melm = elm.parentNode;
    this.sibl = this.melm.nextSibling;
    if(!this.sibl) return;

    this.melmw = parseInt(this.melm.offsetWidth);
    this.siblw = parseInt(this.sibl.offsetWidth);

    i = this.elmPos(this.melm);

    this.col_no = i;
    this.col1 = this.dg_data.childNodes[i];
    this.col2 = this.dg_data.childNodes[i+1];

    if(!this.col1 || !this.col2) return;

    this.mouse = ev.clientX;

    //append new action handlers
    var targetID = this.targetID;
    this.doc.onmousemove = function(){GridObj[targetID].resizeMove();};
    this.doc.onmouseup = function(){GridObj[targetID].resizeEnd();};
  }

  ZDataGrid.prototype.resizeMove = function(){
    var ev = window.event;
    var newpos = ev.clientX - this.mouse;

    if(this.melmw+newpos<10 || this.siblw-newpos<10) return;

    this.colv1 = this.melmw+newpos;
    this.col1.style.width = (this.colv1-this.STgridbd)+"px"
    this.melm.style.width = (this.colv1)+"px";

    this.colv2 = this.siblw-newpos;
    this.col2.style.width = (this.colv2-this.STgridbd)+"px";
    this.sibl.style.width = (this.colv2)+"px";
  }

  ZDataGrid.prototype.resizeEnd = function(){
    this.doc.onmousemove = this.oldmove;
    this.doc.onmouseup = this.oldup;

  //  document.cookie('dg_columns_')
    var statonly = true;

    // col 1
    if(this.headData[this.col_no][1]>=0){
      statonly = false;
    }
    else{
      this.staticpx+= this.colv1+this.headData[this.col_no][1];
      this.headData[this.col_no][1] = this.colv1*-1;
    }
    // col 2
    if(this.headData[this.col_no+1][1]>=0){
       statonly = false;
    }
    else{
       this.staticpx+= this.colv2+this.headData[this.col_no+1][1];
       this.headData[this.col_no+1][1] = this.colv2*-1;
    }

    if(!statonly){
        var k = (this.dg_body.offsetWidth-this.scrBarSize-this.staticpx-1)/100;
        var data1 = this.dg_header.childNodes;
        var i=0,lastone,max=100;
        while(data1[i]){
              data = this.headData[i][1];
              if(data<0){i++; continue;}

              cwidth = (data1[i].offsetWidth/k);
              this.headData[i][1] = cwidth;


              //MSIE math hack pI.
              max-=cwidth;
              lastone=i;

              i++;
        }

        //MSIE math hack pII.
        if(max>0 && lastone){
           this.headData[lastone][1] += max;
        }

    }
    var cc = '';
    for(var c in this.headData){
      cc += this.headData[c][1] + (c!=(this.headData.length*1-1)?',':'');
    }

    document.cookie = "dg_cols_"+this.uniqueid+"="+cc+";expires="+expiration.toGMTString();
  }

//******************
//**** Sorting *****
//******************

  // hii is optional parametr for external startup
  ZDataGrid.prototype.sortMe = function(elm,hii){
     var x,y,targetID = this.targetID;

     //get column number
     if(typeof hii == 'undefined') hii = this.elmPos(elm.parentNode);

     // CSS arrow
     if(elm.className.indexOf("sortUp")>-1)
        elm.className = elm.className.replace("sortUp","sortDn");
     else if(elm.className.indexOf("sortDn")>-1)
        elm.className = elm.className.replace("sortDn","sortUp");

		if (document.getElementById('resizer_'+elm.id))
		{
			elm2=document.getElementById('resizer_'+elm.id);
		}
		else
		{
			elm2=null;
		}
/*
     if(elm2.className.indexOf("sortUp")>-1)
        elm2.className = elm2.className.replace("sortUp","sortDn");
     else if(elm2.className.indexOf("sortDn")>-1)
        elm2.className = elm2.className.replace("sortDn","sortUp");
*/
     if(this.lastSort==hii)
        this.gridData.reverse();
     else {
         // remove sort CSS from previous elm
         if(this.lastSort>-1){
           var old = this.dg_header.childNodes[this.lastSort];
           var old2= this.dg_data.childNodes[this.lastSort];

           if(old)
           {
             old = old.getElementsByTagName("a")[0];
             old.className = old.className.replace("sortDn","");
             old.className = old.className.replace("sortUp","");
             if (document.getElementById('resizer_'+old.id))
           	{
           		var oldr=document.getElementById('resizer_'+old.id);
			}
             if (oldr)
             {
             	oldr.className = oldr.className.replace(/sortDn/g,"");
             	oldr.className = oldr.className.replace(/sortUp/g,"");
             }
           }
           if(old2)
             old2.className = old2.className.replace("sorted","");
         }


         // ASC vs DSC
         sc = this.headData[hii][4]?-1:1;
         // CSS arrow
         elm.className= elm.className + (sc>0?" sortDn":" sortUp");
         if (elm2)
         {
         	elm2.className= elm.className + (sc>0?" sortDn":" sortUp");
			}

         this.dg_data.childNodes[hii].className += " sorted";

         hii=hii*2;

         function mysortfn(a,b) {
            if(a[3][hii+1] || a[3][hii+1]==0) x = a[3][hii+1]; else  x = a[3][hii];
            if(b[3][hii+1] || b[3][hii+1]==0) y = b[3][hii+1]; else  y = b[3][hii];

            if (x<y) return (-1*sc);
            if (x>y) return (1*sc);
            return 0;
         }

         this.gridData.sort(mysortfn);
         this.lastSort=hii/2;
     }

     this.fillBody();
}
  ZDataGrid.prototype.rePosition = function(){
     var data;
     //width       //mozilla has error in iframe rendering sometime
     var h2width = (this.dg_body.offsetWidth?this.dg_body.offsetWidth:this.dg_body.scrollWidth)-this.scrBarSize-this.staticpx-1;

     //height
     var h=this.doc.getElementById(this.targetID).clientHeight - this.dg_header2.offsetHeight;
     this.dg_body.style.height=(h)+'px';
     this.dg_data.style.height=(h+1)+'px';

     var data1 = this.dg_header.childNodes;
     var data2 = this.dg_data.childNodes;

     this.dg_header.style.left = this.dg_body.scrollLeft*-1;

     var colSize = getCookie("dg_cols_"+this.uniqueid);
     if(colSize)
      var colArr = colSize.split(",");


     var i=0,mysize=0,mysize2=0;
     while(data1[i]){
           data = this.headData[i][1];

           if(colSize && colArr[i]) data = 1*colArr[i];

           if(data<0){
              data1[i].style.width = (data*-1) +'px';
              data2[i].style.width = ((data*-1)-this.STgridbd) +'px';
              mysize+=(data*-1);
              mysize2+=(data*-1-this.STgridbd);
           }
           else
           if(data>0)
           {
              cwidth = Math.round((h2width/100)*data);
              if(cwidth<10) cwidth = 10;

              mysize+=cwidth;
              mysize2+=(cwidth-this.STgridbd);
              data1[i].style.width = cwidth +'px';
              data2[i].style.width = (cwidth-this.STgridbd) +'px';
           }
           i++;
     }

     if(mysize){
      this.dg_data.style.width = this.dg_header.style.width = mysize+'px';
     }
  }
  /**
   * listing object
   * elm - ID attr of destination <form> tag
   */
  ZDataGrid.prototype.listGrid = function(elm){
     var frm,frmS,opt,ii,eTarget,me = this;

    try{
		var rowcount = getCookie("dg_cols_" + this.uniqueid + "_limit");
		if(rowcount){
			this.limit = rowcount;
		}
         eTarget = this.doc.getElementById(elm);
         eTarget.innerHTML = '';

         frmS = this.addElm('select','page_select','',{'name':'dgp1'});
         frmS.onchange = function(){ me.listGrid_list(this); }
         ii = this.rows/this.limit;

         if (Math.floor(ii)==ii) ii--; else ii=Math.floor(ii);
         for(i=0;i<=ii;i++)
             frmS.options[i] = new Option(i+1, i, false, false);

         /* search */
         if (this.searchColumn){
             if (this.searchLang){
                frm = this.addElm('span','search_lang','');
                frm.innerHTML = this.searchLang;
                eTarget.appendChild(frm);
             }

             frm =  this.addElm('input','search_input','',{'type':'text','name':'dgs1'});
             frm.value = this.search;
             frm.onkeyup = function(e){
                        if (this.value == me.search)
                            return;
                        else
                            me.search = this.value;

                        me.page = 0;
                        me.fillBody();

                        var ii = me.rows/me.limit;
                        if (Math.floor(ii)==ii) ii--; else ii=Math.floor(ii);

                        frmS.innerHTML = '';
                        for(var i=0;i<=(ii+1);i++) frmS.options[i] = new Option(i+1, i, false, false);
             }
             eTarget.appendChild(frm);
             frm = null;
         }


         frm = this.addElm('a','','',{'href':''});
         frm.onclick = function(){me.listGrid_list(-2,frmS);}
         frm.innerHTML = '&lt;&lt;';
         eTarget.appendChild(frm);
         frm = null;

         frm = this.addElm('a','','',{'href':''});
         frm.onclick = function(){me.listGrid_list(-1,frmS);}
         frm.innerHTML = '&lt;';
         eTarget.appendChild(frm);
         frm = null;

         eTarget.appendChild(frmS);

         frm = this.addElm('a','','',{'href':null});
         frm.onclick = function(){me.listGrid_list(1,frmS);}
         frm.innerHTML = '&gt;';
         eTarget.appendChild(frm);
         frm = null;

         frm = this.addElm('a','','',{'href':null});
         frm.onclick = function(){me.listGrid_list(2,frmS);}
         frm.innerHTML = '&gt;&gt;';
         eTarget.appendChild(frm);
         frm = null;

         /* rows per page */
         if (typeof this.limitArray == 'object') {
             if (this.limitLang){

                 frm = this.addElm('span','limit_lang','');
                 frm.innerHTML = this.limitLang;
                 eTarget.appendChild(frm);
                 frm = null;
             }

             frm =  this.addElm('select','limit_select','',{'name':'dgl1'});
             for (i in this.limitArray){
                this.limitArray[i] = this.limitArray[i]*1;
                frm.options[i] = new Option(this.limitArray[i], this.limitArray[i], false, false);
             }

             frm.value = this.limit;

             frm.onchange = function(){
                me.limit = this.value;
                document.cookie = "dg_cols_"+me.uniqueid+"_limit="+me.limit+";expires="+expiration.toGMTString();
                me.page = 0;
                me.fillBody();
                me.listGrid(elm);
             }

             eTarget.appendChild(frm);
             frm = null;
         }

     }catch(e){return;}
  }

  ZDataGrid.prototype.listGrid_list = function(elm,eSelect){

     if (elm.tagName == 'SELECT'){
        this.page = elm.value;
        //document.cookie = "dg_cols_"+this.uniqueid+"_page="+this.page+";expires="+expiration.toGMTString();

        this.fillBody();
        return;
     }


     var ii = this.rows/this.limit;
     if (Math.floor(ii)==ii) ii--; else ii=Math.floor(ii);

     switch (elm){
       case 1:  if(ii>this.page) this.page++; else return; break;
       case 2:  if(this.page<ii) this.page=ii;  else return; break;
       case -1: if(this.page>0) this.page--;  else return; break;
       case -2: if(this.page>0) this.page=0; else return; break;
     }

    // document.cookie = "dg_cols_"+this.uniqueid+"_page="+this.page+";expires="+expiration.toGMTString();

     this.fillBody();
     eSelect.value = this.page;
  }

/**
 * window.resize handler function (optional)
 * use this part if you want to make datagrid object
 * resizeing with browser window
 */
function ZDataGridRSZ(){
  if(typeof GridObj != 'object') return;
  var keys = arrayKeys(GridObj);
  for(var i in keys)  GridObj[keys[i]].rePosition();
}

function initDatagrid(srcid,count){
        var srcEl = document.getElementById(srcid);
        var dgform = document.createElement('form');
        var blok = document.createElement('blockquote');
        dgform.id = "lister" + count;
        blok.id = "dgrid" + count;
        blok.className="dgrid spacial";
        dgform.appendChild(blok);
        srcEl.appendChild(newform);
}

function processdatagrid(type,dialog,parentname,pitem,width,height,args,dgcount,maxitems,editid,puid)
{

	var form = document.forms[0];
	var name = "sel_" + parentname + "[]";
	if(parentname=='val'){
		name = 'sel_header[]';
	}
	var count=0;
	var id = "dgrid"+dgcount;
	var count = GridObj[id].countItems();

  //Hack
  if(navigator.appName=="Microsoft Internet Explorer" && count>0)
    count = count-1;


  if (count >= maxitems && maxitems!=0 && maxitems!="" && maxitems!='undefined') {alert('Max members count exceeded');return false;}


for (var i=0;i<form.elements.length;i++)
	  {
		var e=form.elements[i];
		if ((e.type=='checkbox') && (e.checked) && (e.name==name))
			var ret=e.value;
	  if (e.type=='checkbox' && e.name==name) count+=1;
	  }

	if (type=="add" || type=="addmlistmember" || type=="addresourcemember" || type=="addgroupmember") ret = count;
	if (type=="cfitem" || type=="headertype" || type=="expression") ret = pitem;
	if(editid) ret = editid;

  base = document.getElementById("base").value;
  if(document.getElementById('loc')!=null) var location = document.getElementById('loc').value;
  else var location='';

  if (args==null) args='';
  if (base=='{base}') base ='';

  if(ret===undefined && (type!="add" &&  type!="addmlistmember" && type!="addgroupmember")) return false;

    newwin = window.open('editform.html?type=' + type + '&object=datagrid&value=' + dialog +'&pitem=' + ret +'&pname=' +parentname + '&puid=' + puid +'&base=' + base +'&location='+location + '&args=' + args , '_blank', "resizable=yes,scrollbars=yes,status=0,width=" + width + ",height="+ height);
    newwin.focus();



}

function createDatagridContainer(sourceid,count){

  var source = document.getElementById(sourceid);
  var newform = document.createElement('div');
  newform.id = "lister" + count;
  newform.className="lister";
  var blok = document.createElement('blockquote');
  blok.id = "dgrid" + count;
  blok.className="dgrid spacial";

  var listing=document.createElement('span');
  listing.id='listing' + count;
  newform.appendChild(blok);
  newform.appendChild(listing);
  source.appendChild(newform);
}

function createGridButton(type,label,onclick,count)
{
  var butt = document.createElement('button');
  butt.innerHTML = label;
  eval('butt.onclick = function () { '+onclick+'return false; }');
  //butt.onclick = function () {  eval(onclick);return false; }
  document.getElementById("lister"+count).appendChild(butt);
}

//RBL
function setRBLAction(id,index,uid)
{

  var value = document.getElementById('sel_'+id+'_'+index).checked;
  document.getElementById('gridaction').value = 'rblset';
  document.getElementById('gridval').value = id;

  document.getElementById('index').value = uid;
  document.getElementById('index2').value = value;

  document.getElementById('noreload').value=1;
  document.forms[0].submit();
}
