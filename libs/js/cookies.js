if(getCookie("cookies")!=""){
	document.getElementById('cookiepolicy').style.display='none';
}

if(hasOptedOut()){ //Disable all not relevant cookies
	//window['ga-disable-UA-XXXXXX-Y'] = true;
}

function acceptCookie(){
	setCookie("cookies", "true", 365);
	document.getElementById('cookiepolicy').style.display='none';
}

function refuseCookie(){
	setCookie("cookies", "false", 365);
	document.getElementById('cookiepolicy').style.display='none';
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function noCookiePreference(){
	var cookie = getCookie("cookies");
	if(cookie!=""){
		return false;
	} else {
		return true;
	}
}

function hasOptedOut(){
	if(!noCookiePreference()){
		if(getCookie("cookies")=="false"){
			return true;
		}
		return false;
	}
	return true;
}