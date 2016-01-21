function AJAXnextMonth() {
	document.getElementById('fullscreenload').style.display = 'block';
	if(window.nm === 12){
		window.nm = 1;
		window.ny = window.ny + 1;
	}else{
		window.nm = window.nm + 1;
	}

    var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200){
			$('#calhold').append(xmlhttp.responseText);
			document.getElementById('fullscreenload').style.display = 'none';
		}
	};
	xmlhttp.open("GET","/calendar/next/?m="+encodeURIComponent(window.nm)+"&y="+window.ny,true);
	xmlhttp.send();
}
function AJAXprevMonth() {
	document.getElementById('fullscreenload').style.display = 'block';
	if(window.pm === 1){
		window.pm = 12;
		window.py = window.py - 1;
	}else{
		window.pm = window.pm - 1;
	}

    var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200){
			$('#calhold').prepend(xmlhttp.responseText);
			document.getElementById('fullscreenload').style.display = 'none';
		}
	};
	xmlhttp.open("GET","/calendar/next/?m="+encodeURIComponent(window.pm)+"&y="+window.py,true);
	xmlhttp.send();
}

function AJAXcalEvent(uid){
	document.getElementById('calEventContent').innerHTML = '<img src="/img/loading.gif">';
	showModal('calEvent');
    var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200){
			document.getElementById('calEventContent').innerHTML = xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/calendar/getevent/?uid="+encodeURIComponent(uid),true);
	xmlhttp.send();
}
