function navBarFunc() {
	var x = document.getElementById("topNav");
  	if (x.className === "topnav") {
	    x.className += " responsive";
	}
	else {
	    x.className = "topnav";
	 }
}

function goBack() {
	window.history.go(-1);
}

function showPosition(position) {
	document.getElementById("bom-latitude").innerHTML = position.coords.latitude;
	document.getElementById("bom-longitude").innerHTML = position.coords.longitude;
}

function selectDeselectAll() {
	var r = document.getElementsByClassName("resultcheckbox");
	var s = document.getElementById("selectall");
	var i = 0;
	while (i < r.length) {
		if(s.checked === true){
			r[i].checked = true;
		}
		else {
			r[i].checked = false;
		}
		i++;
	}
	enableDisableDownload();
}

function getQ() {
	var oldQ = document.getElementById("oldQ").value;
	document.getElementById("q").value = oldQ;
}

//Check to see if all results selected. If so, check off the "Select All" button.
function selectResult() {
	var r = document.getElementsByClassName("resultcheckbox");
	var s = document.getElementById("selectall");
	var allChecked = true;
	var i = 0;
	while (i < r.length && allChecked != false) {
		if(r[i].checked === false){
			allChecked = false;
		}
		i++;
	}
	if(allChecked === true) {
		s.checked = true;
	}
	else {
		s.checked = false;
	}
	enableDisableDownload();
}

//Check if at least one result is checked. If so, show the save results button.
function enableDisableDownload() {
	var r = document.getElementsByClassName("resultcheckbox");
	var s = document.getElementById("selectall");
	var noneChecked = true;
	var i = 0;
	while(i < r.length && noneChecked != false) {
		if(r[i].checked === true) {
			noneChecked = false;
		}
		i++;
	}
	var x = document.getElementById("download");
	if(noneChecked === false) {
		x.className = "showdownload";
	}
	else {
		x.className = "hidedownload";
	}
	countSelected();
}

function countSelected() {
	var r = document.getElementsByClassName("resultcheckbox");
	var i = 0;
	var count = 0;
	var s = "";
	while (i < r.length) {
		if(r[i].checked === true) {
			count++;
		}
		i++;
	}
	if(count == 1){
		s = count + " result selected";
	}
	else if(count > 1) {
		s = count + " results selected";
	}
	else {
		s = "";
	}
	document.getElementById("numselected").innerHTML = s;
}

function getSelections() {
	var e = document.getElementById("drp");
	//var drpValue = e.options[e.selectedIndex].value;
	var fileExt = e.options[e.selectedIndex].text;
	var fileName = "results" + fileExt;
	var fileText = "";	
	var selections=[];
	var r = document.getElementsByClassName("resultcheckbox");
	var i = 0;

	while (i < r.length) {
		if(r[i].checked === true) {
			selections.push(r[i].id);
		}
		i++;
	}
	
	var a = document.createElement("a");
	
	//Get results from database
	$.ajax({
		type:'POST',
		url: '../search-by/getSelectedResults.php',
		data: {selectionsAry: selections},
		success: function (data) {
			var r = JSON.parse(data);
			if(fileExt == ".json") {
				var Result = {'Result': r};
				fileText = JSON.stringify(Result);
			}
			else if (fileExt == ".csv") {
				for(i in r) {
					var title = r[i].title;
					var url = r[i].url;
					var description = r[i].description;
					fileText += title + "," + url + "," + description +"\n";
				}
			}
			else {
				var space = "  ";
				fileText = '<?xml version="1.0" encoding="UTF-8"?>\n' + '<results>\n';
				for(i in r) {
					var title = space + space + "<title>" + r[i].title + "</title>";
					var url = space + space + "<url>" + r[i].url + "</url>";
					var description = space + space +"<description>" + r[i].description + "</description>";
					fileText += space + "<result>\n" + title + "\n" + url + "\n" + description + "\n" + space + "</result>\n";
				}
				fileText += "</results>";
			}

			a.setAttribute("href", "data:text/plain;charset=utf-8," + encodeURIComponent(fileText));
			a.setAttribute("download", fileName);
			a.style.display = 'none';
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
		}
	})
}

function checkURL() {
	var url = document.getElementById("url").value;
    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    if(!regexp.test(url)){
    	alert("Please enter a valid URL.");
    	return false;
    }
    else {
    	return true;
    }
}


