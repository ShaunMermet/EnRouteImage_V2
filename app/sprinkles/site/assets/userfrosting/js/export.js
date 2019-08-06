var export_token = "";
//var export_modelToken = "";
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
	document.getElementById('trainProgress1').innerHTML = "";
	document.getElementById('trainProgress2').innerHTML = "";
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
	    	export_token = data.link;
	    	if(data && data.link){
		    	document.getElementById("dlButton").disabled = false;
				document.getElementById("dlButton").style.opacity = 1;
				document.getElementById("dlButton").style.pointerEvents = "all";
				export_fillDLDetails(data);
				export_fillWeightsTable(data);
			}else{
				document.getElementById("dlButton").disabled = true;
				document.getElementById("dlButton").style.opacity = 0.5;
				document.getElementById("dlButton").style.pointerEvents = "none";
				export_cleanDLDetails();
			}
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
function export_fillWeightsTable(data){
	//fill weights table
	var modelsHeader = [
		"File name",
		"Size"
	];
	buildHtmlTable(modelsHeader, data.modelList, $('#table_modelHeader'), $('#table_modelBody'));

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
function export_onDlModelClicked(){
	window.location.href = "../train/dl/"+export_token+"/test.weights";
}

function export_onTrainClicked(){
	$('#trainButton').hide();
	$('#trainColumn .stdLoaderButton').show();

	var iterationValue = $('#iterationNbr').val();
	console.log("Iteration value : "+iterationValue);

	var data= {};
	var setCombo = document.getElementById("setAssignEx");
	var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
	console.log("ask to train model "+setRequestedId);

	data["setID"]=setRequestedId;
	data["token"]=export_token;
	data["nbrStep"] = iterationValue;

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
				//export_modelToken = res.link;
				export_getSetDlLink();
				document.getElementById('trainState').innerHTML = "Model ready";
				document.getElementById('trainProgress1').innerHTML = "Done";
				document.getElementById('trainProgress2').innerHTML = "100%";
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
	document.getElementById('trainProgress2').innerHTML = "0%";
	document.getElementById('trainProgress1').innerHTML = "Preparing...";
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
		//var setCombo = document.getElementById("setAssignEx");
		//var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
		var progressValue = data[export_token].train_progress;
		var iteration_nbr = data[export_token].iteration_nbr;
		var iteration_max = data[export_token].iteration_max;
		console.log(data[export_token].train_progress);
		document.getElementById('trainProgress1').innerHTML = "Iteration : "+iteration_nbr+"/"+iteration_max;
		document.getElementById('trainProgress2').innerHTML = progressValue+"%...";
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

function buildHtmlTable(hData, bData, hSelector, bSelector, ){
	if(bData.length > 0){
		addAllColumnHeaders(hData, hSelector);
		updateHtmlTable(bData, bSelector, hData);
	}else{
		hSelector.html("");
		bSelector.html("");
	}
}

function updateHtmlTable(bData, bSelector, hData){
var header = "";

 	for (var i = 0; i < bData.length; i++) {
 		header += "<tr class=\"row100 body\">"
 		var rowHash = bData[i];
 		/*for (var k = 0; k < bData.length; k++){
 			var j = k+1;
  			header += "<td class=\"cell100 column"+ j +"\">"+ rowHash[k] +"</td>"
  		}*/
  		var j = 1;
  		var filename = "";
  		for (var key in rowHash) {
  			if(j <= hData.length){
  				if(key == "filesize"){
  					var format = rowHash[key]/1024/1024
  					var display = Math.round(format * 100) / 100
					header += "<td class=\"cell100 column"+ j +"\">"+ display +" MB</td>"
	    		}else if(key == "filename"){
	    			filename = rowHash[key];
  					header += "<td class=\"cell100 column"+ j +"\">"+ filename +"</td>"
	    		}else {
  					header += "<td class=\"cell100 column"+ j +"\">"+ rowHash[key] +"</td>"
	    		}
	    		j++;

    		}
	    }
	    header += "<td class=\"cell100 column"+ j +"\">"+ "<button class=\"small-btn\" type=\"button\" onclick=\"export_onModelDeleteClicked(event)\" title=\"Delete\" style=\"background-image: linear-gradient(to bottom, #dd4b39, #a94442);\" data-token=\""+export_token+"\" data-file=\""+filename+"\"><i class=\"fa fa-trash\"></i></button>" +"</td>"
  		j++;
  		header += "<td class=\"cell100 column"+ j +"\">"+ "<button class=\"small-btn\" type=\"button\" onclick=\"export_onModelRenameClicked(event)\" title=\"Rename\" data-token=\""+export_token+"\" data-file=\""+filename+"\"><i class=\"fa fa-edit\"></i></button>" +"</td>"
  		header += "</tr>";
  	}
  	bSelector.html(header);
  	//set download
  	//$('.row100').on('mouseclick',function(){
  	//	console.log("row clicked");
  	//});
  	$( ".row100.body" ).click(function(e){
  		//console.log(this);
  		//console.log(e);
  		if($(e.target).hasClass('cell100')){
	  		var filename = $(this).find(".column1").text();
	  		console.log(filename);
	  		window.location.href = "../train/dl/"+export_token+"/"+filename;
  		}
  	});
}

function addAllColumnHeaders(hData, hSelector) {
 	var header = "<tr class=\"row100 head\">";

 	var j;
 	for (var i = 0; i < hData.length; i++) {
 		var rowHash = hData[i];
 		j = i+1;
  		header += "<th class=\"cell100 column"+ j +"\">"+ rowHash +"</th>"
  	}
  	j++;
  	header += "<th class=\"cell100 column"+ j +"\">"+ "<button class=\"small-btn\" type=\"button\" title=\"Delete\" style=\"background-image: linear-gradient(to bottom, #dd4b39, #a94442);pointer-events: none;\"><i class=\"fa fa-trash\"></i></button>" +"</th>"
  	j++;
  	header += "<th class=\"cell100 column"+ j +"\">"+ "<button class=\"small-btn\" type=\"button\" title=\"Rename\" style=\"pointer-events: none;\"><i class=\"fa fa-edit\"></i></button>" +"</th>"
  	header += "</tr>";
  	hSelector.html(header);

//  return columnSet;
}


function export_onModelDeleteClicked(event){
	console.log("delete model");
	var filename = event.currentTarget.dataset.file;
	var token = event.currentTarget.dataset.token;
	console.log(filename);
	var data= {};
	//var setCombo = document.getElementById("setAssignEx");
	//var setRequestedId = setCombo.options[setCombo.selectedIndex].value;
	//console.log("ask to train model "+setRequestedId);

	//data["setID"]=setRequestedId;
	//data["token"]=export_token;

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	var url = site.uri.public + '/train/del/'+token+"/"+filename;
	//if(export_pagemode == "bbox"){
	//	var url = site.uri.public + '/train';
	//}else if(export_pagemode == "segmentation"){
	//	var url = site.uri.public + '/segTrain';
	//}
	//export_listenTrainProgress();
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	
	    	//console.log(data);
	    	//refresh list
	    	export_getSetDlLink();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
	//document.getElementById('trainState').innerHTML = "Trainning model...";
}
function export_onModelRenameClicked(event){
	console.log("rename clicked");
	console.log(event);

	$("body").ufModal({
        sourceUrl: site.uri.public + "/api/sets/aiModel/edit",
        ajaxParams: {
            token: event.currentTarget.dataset.token,
            filename : event.currentTarget.dataset.file
        },
        msgTarget: $("#alerts-page")
    });

    attachGroupForm();
}

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachGroupForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        /**
         * Set up modal widgets
         */
        // Set up any widgets inside the modal
        form.find(".js-select2").select2({
            width: '100%'
        });

        // Auto-generate slug
        form.find('input[name=name]').on('input change', function() {
            var manualSlug = form.find('#form-group-slug-override').prop('checked');
            if (!manualSlug) {
                var slug = getSlug($(this).val());
                form.find('input[name=slug]').val(slug);
            }
        });

        form.find('#form-group-slug-override').on('change', function() {
            if ($(this).prop('checked')) {
                form.find('input[name=slug]').prop('readonly', false);
            } else {
                form.find('input[name=slug]').prop('readonly', true);
                form.find('input[name=name]').trigger('change');
            }
        });

        // Set icon when changed
        form.find('input[name=icon]').on('input change', function() {
            $(this).prev(".icon-preview").find("i").removeClass().addClass($(this).val());
        });

        // Set up the form for submission
        form.ufForm({
            validators: page.validators
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}