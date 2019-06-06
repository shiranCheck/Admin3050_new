// JavaScript Document
function selectService(cls)
{
	var startbut = document.getElementById('start');
	var stopbut = document.getElementById('stop');
	var stopmodule = document.getElementById('stopmodule');
	var startmodule = document.getElementById('startmodule');
	var logging = document.getElementById('logging');

	if (cls=='Control'){
		startbut.disabled = true;
		stopbut.disabled = true;
		stopmodule.disabled = true;
		startmodule.disabled = true;
    }
	else{
			startbut.disabled = false;
			stopbut.disabled = false;
			stopmodule.disabled = false;
			startmodule.disabled = false;
	}
	document.getElementById('s_value').value = cls;
	return true;
}

//Set service action in services TAB
function setServiceAction(action)
{
	document.getElementById('s_action').value = action;
	document.getElementById('lister').submit();
}
