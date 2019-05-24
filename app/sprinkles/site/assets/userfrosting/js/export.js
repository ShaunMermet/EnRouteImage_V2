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
	document.getElementById("trainButton").disabled = false;
	document.getElementById("trainButton").style.opacity = 1;
	document.getElementById("trainButton").style.pointerEvents = "all";

	document.getElementById('trainState').innerHTML = "";
	document.getElementById('trainProgress').innerHTML = "";
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

function export_onTrainClicked(){
	$('#trainButton').hide();
	$('#trainColumn .stdLoaderButton').show();
	var data= {};
	var setCombo = document.getElementById("setAssignEx");
	var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
	console.log("ask to train model "+setRequestedId);

	data["setID"]=setRequestedId;

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	if(export_pagemode == "bbox"){
		var url = site.uri.public + '/train';
	}else if(export_pagemode == "segmentation"){
		var url = site.uri.public + '/segTrain';
	}
	export_listenTrainProgress();
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
				document.getElementById("dlModelButton").disabled = false;
				document.getElementById("dlModelButton").style.opacity = 1;
				document.getElementById("dlModelButton").style.pointerEvents = "all";
				export_token = res.link;
				document.getElementById('trainState').innerHTML = "Model ready";
				document.getElementById('trainProgress').innerHTML = "100%";
				//$('#imgCounter').text(res.nbrImgs+" Image(s) found");
				//export_fillDLDetails(res);
				closeEventSource(mainContainer.TKUEvent);
			}
			else if( res == "No file found"){
				$('#trainState').text("No file");
			}
			$('#trainButton').show();
			$('#trainColumn .stdLoaderButton').hide();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
	document.getElementById('trainState').innerHTML = "Trainning model...";
}

function export_listenTrainProgress(){
	//$('#progress_bar_process').width("0%");
	var url = site.uri.public + '/admin/train/keepUpdated';
	if(mainContainer.TKUEvent){
		closeEventSource(mainContainer.TKUEvent);
	}
	var source = new EventSource(url);
	mainContainer.TKUEvent = source;
	//mainContainer.lastTicksTable = [];
	//for(var i = 0; i < mainContainer.tickFrameNumber; i++){
	//	mainContainer.lastTicksTable.push(0);
	//}
	//mainContainer.archiveProgress_lastTick = 0;
	source.onmessage = function(event) {
		var data = JSON.parse(event.data);
		console.log(data);
		var setCombo = document.getElementById("setAssignEx");
		var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
		var progressValue = data[setRequestedId].train_progress;
		console.log(data[setRequestedId].train_progress);
		document.getElementById('trainProgress').innerHTML = progressValue+"%...";
		return;
		if(!mainContainer.archiveProgress_lastTick) mainContainer.archiveProgress_lastTick = 0;
		console.log(data);
		if(jQuery.isEmptyObject(data)){
			$('#progress_bar_process').width("0%");
		}
		var current = 0;
	    var total = 0;
	    for (var key in data) {
		    current = current + data[key].upload_current;
		    total = total + data[key].upload_total;
		}
		var filesPerSec = current - mainContainer.archiveProgress_lastTick;
	    var filesPerSecFrame = meanLastTicks(filesPerSec);
	    console.log(filesPerSecFrame);
	    if(filesPerSec == 0) {
	    	if(!mainContainer.archiveProgress_NoChangeCount)mainContainer.archiveProgress_NoChangeCount = 0;
	    	mainContainer.archiveProgress_NoChangeCount++;
	    	return;
	    } else{
	    	filesPerSec = filesPerSec/mainContainer.archiveProgress_NoChangeCount;
	    	mainContainer.archiveProgress_NoChangeCount = 1;
	    }
	    var remainingSec = (total - current) / filesPerSec;
	    var remainingTime = formatTime(remainingSec);
	    var remainingSecFrane = (total - current) / filesPerSecFrame;
	    var remainingTimeFrame = formatTime(remainingSecFrane);
	    var processingProgress = Math.floor(current / total * 100);
	    if(!processingProgress)processingProgress = 0;
	    $('.progress-extendedLine2').html("File processing | "+processingProgress+"% | "+current+"/"+total+" | "+remainingTimeFrame);
	    $('#progress_bar_process').width(processingProgress+"%");
	    mainContainer.archiveProgress_lastTick = current;
	    console.log("Current : "+current);
	    console.log("Total : "+total);
	    if(total == current){
	    	upl_listenArchiveProgressStop();
	    	$('.progress-extendedLine2').hide();
			$('#progress_bar_process_main').hide();
	    }
	};
}
function export_listenTrainProgressStop(){
	closeEventSource(mainContainer.TKUEvent);
}