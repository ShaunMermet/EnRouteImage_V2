var export_token = "";
var export_pagemode = "";//"bbox","segmentation"

function export_initExport(pagemode){
	export_pagemode = pagemode;
}


function export_onComboChanged(){
	export_getSetDlLink();
	export_getNbrInSet();
	document.getElementById("exportButton").disabled = false;
	document.getElementById("exportButton").style.opacity = 1;
	document.getElementById("exportButton").style.pointerEvents = "all";
	$('#exportState').text("");
}


function export_getNbrInSet(){
	console.log("combo detected");
	$('#exportButton').hide();
	$('#exportColumn .stdLoaderButton').show();
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
			$('#exportButton').show();
			$('#exportColumn .stdLoaderButton').hide();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function export_getSetDlLink(){
	$('#dlButton').hide();
	export_cleanDLDetails();
	$('#dlColumn .stdLoaderButton').show();
	var data= {};
	var setCombo = document.getElementById("setAssignEx");
	var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
	data["setID"]=setRequestedId;
	data["setMode"]=export_pagemode;
	
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	var url = site.uri.public + '/api/sets/dlInfos';
	
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	console.log(data);
	    	if(data && data.link){
		    	document.getElementById("dlButton").disabled = false;
				document.getElementById("dlButton").style.opacity = 1;
				document.getElementById("dlButton").style.pointerEvents = "all";
				export_fillDLDetails(data);
			}else{
				document.getElementById("dlButton").disabled = true;
				document.getElementById("dlButton").style.opacity = 0.5;
				document.getElementById("dlButton").style.pointerEvents = "none";
				export_cleanDLDetails();
			}
			export_token = data.link;
			$('#dlButton').show();
			$('#dlColumn .stdLoaderButton').hide();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function export_fillDLDetails(data){
	export_cleanDLDetails();
	var displaySize = 0;
	if(data.size > 1024*1024*1024){// KB MB GB
		displaySize = Math.trunc(data.size / (1024*1024*1024)*100)/100+" GB";
	}else
	if(data.size > 1024*1024){// KB MB 
		displaySize = Math.trunc(data.size / (1024*1024)*100)/100+" MB";
	}else
	{// KB 
		displaySize = Math.trunc(data.size / (1024)*100)/100+" KB";
	}

	var areaDetails = JSON.parse(data.areaPerType);
	for (var key in areaDetails) {
		var node = document.createElement("LI");
		var textnode  = document.createTextNode(key+" "+areaDetails[key]);
		node.appendChild(textnode); 
		$('#dlColumn #dlNbrAreaPerType').append(node);
	}



	$('#dlColumn #dlNbrImgs').text(data.nbrImgs+" Image(s)");
	$('#dlColumn #dlNbrAreas').text(data.nbrAreas+" Area(s)");
	//$('#dlColumn #dlNbrAreaPerType').text(data.areaPerType);

	$('#dlColumn #dlSize').text(displaySize);
	$('#dlColumn #dlUser').text("By "+data.user);
	$('#dlColumn #dlDateGen').text(""+data.dateGen);
}
function export_cleanDLDetails(){
	$('#dlColumn #dlNbrImgs').text("");
	$('#dlColumn #dlNbrAreas').text("");
	$('#dlColumn #dlNbrAreaPerType').html("");


	$('#dlColumn #dlSize').text("");
	$('#dlColumn #dlUser').text("");
	$('#dlColumn #dlDateGen').text("");
}
///////////////////////////////

function export_onExportClicked(){
	$('#exportButton').hide();
	$('#exportColumn .stdLoaderButton').show();
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
				document.getElementById('exportState').innerHTML = "Download ready";
				$('#imgCounter').text(res.nbrImgs+" Image(s) found");
				export_fillDLDetails(res);
			}
			else if( res == "No file found"){
				$('#exportState').text("No file");
			}
			$('#exportButton').show();
			$('#exportColumn .stdLoaderButton').hide();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
	document.getElementById('exportState').innerHTML = "Preparing download...";
}

function export_onDlClicked(){
	//document.getElementById("dlButton").disabled = true;
	//document.getElementById("dlButton").style.opacity = 0.5;
	window.location.href = "../export/dl/"+export_token;
	//document.getElementById('imgCounter').innerHTML = "";
}