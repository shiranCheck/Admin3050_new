//Content filters & Rules
function setCFAction(index,index2,action,type,ctype)
{
  document.getElementById('gridaction').value = action;
  document.getElementById('gridval').value = type;
  document.getElementById('index2').value = index2;
  document.getElementById('index').value = index;
  document.getElementById('ctype').value = ctype;
  document.getElementById('noreload').value=1;
  document.forms[0].submit();
}
function selectcfitem(grid,elm,index,index2,type,ctype){
  if (si[grid]!=null)
     document.getElementById(si[grid][0]).className="unselectedcf";
  elm.className="selectedcf";
  
  si[grid] = new Array();
  si[grid][0] = elm.id;
  si[grid][1] = index;
  si[grid][2] = index2;
  si[grid][3] = type;
  si[grid][4] = ctype;
  
}
function cfbuttons(action)
{
  var error = 0;
  if (si[1]==null) error = 1;
  else {
    if (si[1][3]=="condition") {
      setCFAction(si[1][1],si[1][2],action,si[1][3],si[1][4]);
    }
    else error = 1;
  }
  if (error==1){
    alert("You must select some condition first!");
    return false;
  }
  return false;
}
