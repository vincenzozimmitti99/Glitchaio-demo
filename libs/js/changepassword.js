function changePassword(){
	var newPassword = document.getElementById("password").value;
	if(newPassword.length<8){
		alert("Inserisci una password di almeno 8 caratteri.");
		return;
	}
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState==4 && this.status==200){
			if(this.response=="1")
				alert("La password è stata cambiata con successo.");
			else alert("Si è verificato un errore durante la modifica della password.");
			console.log(this.response);
		}
	}
	xhttp.open("GET", "../libs/ajaxRequests.php?request=changePassword&nickname=" + nickname + "&password=" + newPassword);
	xhttp.send();
}