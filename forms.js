
function SelectDomain(append,target,addbracket){
  var dom = (addbracket?"[":"") + document.getElementById("domain_select_list").value + (addbracket?"]":"");
  if (append && !CheckForFill(dom,target)) return false;
  if (append) document.getElementById(target).value += dom + ";";
  else        document.getElementById(target).value = dom;
}
function SelectAccount(append,target){
  var acc = document.getElementById("account_select_list").value;
  if (!CheckForFill(acc,target)) return  false;
  if (append) document.getElementById(target).value += acc + ";";
  else        document.getElementById(target).value = acc;
}
function FillAccounts(accounts,dom)
{
  document.getElementById("account_select_list").innerHTML = "";
  for (i = 0; i < accounts[dom].length; i++){
    el = document.createElement("option");
    eli = document.createTextNode(accounts[dom][i]); 
    el.value = accounts[dom][i];
    el.appendChild(eli);
    document.getElementById("account_select_list").appendChild(el);   
  }
}
function CheckForFill(name,target)
{
  var val = document.getElementById(target).value;
  if (!val) return true;
  else{
      if (val.substr(val.length-1,1)!=";") document.getElementById(target).value+=";";
      if (val.indexOf(name)==-1) return true;    
  }
  return false;
}

function submitServices()
{
    var sf = document.getElementById('iframe_services');
    var doc = sf.contentDocument || sf.contentWindow.document;
    var lb = doc.getElementById('logging');
    lb.click();
    return true;
}
