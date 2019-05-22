if(navigator.geolocation) {
	navigator.geolocation.getCurrentPosition(showPosition);
}
else {
	document.getElementById("bom-latitude").innerHTML = "Geolocation is not supported by this browser.";
	document.getElementById("bom-longitude").innerHTML = "Geolocation is not supported by this browser.";
}

