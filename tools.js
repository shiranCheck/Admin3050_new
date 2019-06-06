// Debug Object (unnecessary for menu)
function inspect(obj,win) {
    var str = "";
    for (var prop in obj) str += "obj." + prop + " = " + obj[prop] + "<br>";
    if(win){var kokos = window.open("","ins_win"); kokos.document.writeln(str);}else alert(str);
};

function emulateEventHandlers(eventNames) {
   for (var i = 0; i < eventNames.length; i++) {
      document.addEventListener(eventNames[i], function (e) {
         window.event = e;
      }, true);
   }
};
typeof(window.event)=="object" ? "" : emulateEventHandlers(["mousemove","mousedown","mouseover"]);

function arrayKeys(){
    var keys = new Array();
    for (var i in GridObj) {
        if (GridObj[i] != null)
            keys.push(i);
    }
    return keys;
};

function createHDiv(sw)
{
  if(sw)
  {
    var hdiv = document.createElement("div");
    hdiv.id="hiddendiv";
    hdiv.innerHTML="Hiddendiv&nbsp;";
    document.body.appendChild(hdiv);
    return hdiv;
  }

  var hdiv = document.getElementById("hiddendiv");
  if(hdiv) document.body.removeChild(hdiv);
};