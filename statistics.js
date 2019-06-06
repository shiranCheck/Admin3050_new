// JavaScript Document - Statistics
//Shows service statistics in statistics TAB
function showServiceStats(str)
{
	var id = 'section_' + str;
	if (document.getElementById('s_value').value!='')
		document.getElementById(document.getElementById('s_value').value).style.display = 'none';
	document.getElementById(id).style.display = 'block';
	document.getElementById('s_value').value = id;
}

//Shows section in statistics TAB
function showSection(id,el)
{

	var elem = document.getElementById(id);
	if (elem.style.display == 'none') {
		elem.style.display = 'block';
		el.className = "spanminus";
	}
	else{
		elem.style.display = 'none';
		el.className = "spanplus";
	}
}
