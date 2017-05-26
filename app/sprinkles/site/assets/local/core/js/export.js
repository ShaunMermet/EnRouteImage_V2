var export_token = "";

function export_onComboChanged(){
	export_getNbrInCat();
}

function export_onAddClicked(){
	addInput("comboColumn", "" , "js-basic-single export cat");
}
function export_onGrpAddClicked(){
	addInput("comboColumnGrp","grpAssignEx","js-basic-single export group");
}
function addInput(divName,id,className) {
    var newDiv = document.createElement('select');
    newDiv.className = className;
    newDiv.onchange = export_onComboChanged;
    newDiv.id = id;
    document.getElementById(divName).appendChild(newDiv);
    upl_initCombo(newDiv);
}
function export_onRemoveClicked(){
	removeInput("comboColumn");
}
function export_onGrpRemoveClicked(){
	removeInput("comboColumnGrp");
}
function removeInput(divName){
	var comboColumn = document.getElementById(divName);
	if(comboColumn.lastElementChild){
		comboColumn.removeChild(comboColumn.lastElementChild);
		comboColumn.removeChild(comboColumn.lastElementChild);
		export_getNbrInCat();
	}
}

function export_getNbrInCat(){
	console.log("combo detected");
	var data= {};
	data["ids"]=[];
	data["groups"]=[];
	var x = document.getElementsByClassName("js-basic-single export cat");
	for (i = 0; i < x.length; i++) {
		if(x[i].value){
	   		console.log("Category "+x[i].value);
	   		data["ids"].push(x[i].value);
	   }
	}
	var y = document.getElementsByClassName("js-basic-single export group");
	for (i = 0; i < y.length; i++) {
		if(y[i].value){
	   		console.log("group "+y[i].value);
	   		data["groups"].push(y[i].value);
	   }
	}
	
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	var url = site.uri.public + '/images/nbrBYcategory';
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
			document.getElementById('imgCounter').innerHTML = res['countByCat']+" Image(s) found";
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
///////////////////////////////

function export_onExportClicked(){
	var data= {};
	data["category"]=[];
	data["groups"]=[];
	var x = document.getElementsByClassName("js-basic-single export cat");
	for (i = 0; i < x.length; i++) {
		if(x[i].value){
	   		console.log("Category "+x[i].value);
	   		data["category"].push(x[i].value);
	   }
	}
	var y = document.getElementsByClassName("js-basic-single export group");
	for (i = 0; i < y.length; i++) {
		if(y[i].value){
	   		console.log("group "+y[i].value);
	   		data["groups"].push(y[i].value);
	   }
	}
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;


	var url = site.uri.public + '/export';
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