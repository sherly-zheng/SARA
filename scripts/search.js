var searchbtn = document.getElementById("searchbtn");
document.getElementById("browsefiles").addEventListener("change", function() {
	var fileName = "";
	var fileExt = "";
	var msg = "<br>";
	var isValidFile = false;
	var validFileExt = ["csv", "json", "xml"];
	
	if(this.files[0]) {
		//get file name to put in upload bar.
		var file = this.files[0];
		fileName = file.name;

		//validate file extension
		var i = 0;
		while(i < validFileExt.length && !isValidFile){
			fileExt = (fileName.substr((fileName.length-validFileExt[i].length), fileName.length)).toLowerCase();
			if (fileExt == validFileExt[i]) {
				isValidFile = true;
				searchbtn.disabled = false;
				searchbtn.className = "searchbtn";
			}
			i++;
		}
	}
	if(!isValidFile) {
		msg = "Please select a CSV, JSON or XML file.";
		searchbtn.disabled = true;
		searchbtn.className = "searchbtndisabled";
	}	
	document.getElementById("fileErrMsg").innerHTML = msg;
	document.getElementById("filenameinput").value = fileName;
});

document.getElementById("canceluploadbtn").addEventListener("click", function() {
	document.getElementById("browsefiles").value = "";
	document.getElementById("filenameinput").value = "";
	searchbtn.disabled = true;
	searchbtn.className = "searchbtndisabled";
});