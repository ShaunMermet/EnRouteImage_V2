var export_token = "";
var export_pagemode = "";//"bbox","segmentation"

function export_initExport(pagemode){
	export_pagemode = pagemode;
}


function export_onComboChanged(){
	export_getNbrInSet();
}


function export_getNbrInSet(){
	console.log("combo detected");
	var data= {};
	var setCombo = document.getElementById("setAssignEx");
	var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
	data["setID"]=setRequestedId;
	
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	console.log(data);
	if(export_pagemode == "bbox"){
		var url = site.uri.public + '/images/nbrBYset';
	}else if(export_pagemode == "segmentation"){
		var url = site.uri.public + '/segImages/nbrBYset';
	}
	
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	console.log(data);
	    	var res = data;
			document.getElementById('imgCounter').innerHTML = res['countBySet']+" Image(s) found";
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
///////////////////////////////

function export_onExportClicked(){
	var data= {};
	var setCombo = document.getElementById("setAssignEx");
	var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
	data["setID"]=setRequestedId;

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	if(export_pagemode == "bbox"){
		var url = site.uri.public + '/export';
	}else if(export_pagemode == "segmentation"){
		var url = site.uri.public + '/segExport';
	}
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	var res = JSON.parse(data);
			if(typeof res === 'object' && "link" in res){
				document.getElementById("dlButton").disabled = false;
				document.getElementById("dlButton").style.opacity = 1;
				document.getElementById("dlButton").style.pointerEvents = "all";
				export_token = res.link;
				document.getElementById('imgCounter').innerHTML = "Download ready";
			}
			else if( res == "No file found")
				document.getElementById('imgCounter').innerHTML = "No file";
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
	document.getElementById('imgCounter').innerHTML = "Preparing download...";
}

function export_onDlClicked(){
	document.getElementById("dlButton").disabled = true;
	document.getElementById("dlButton").style.opacity = 0.5;
	window.location.href = "../export/dl/"+export_token;
	document.getElementById('imgCounter').innerHTML = "";
}