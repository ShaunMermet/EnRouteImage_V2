////  COMBO    //////////////////
var export_catId = [];
var export_catText=[];
var export_catColor= [];
var export_phpPath = "../../php/";
if(document.getElementById("dlButton")){
	document.getElementById("dlButton").disabled = true;
	document.getElementById("dlButton").style.opacity = 0.5;
}
var export_token = "";

export_loadCategories();
function export_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/segCategory/all';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	         var res = data.rows;
	        	export_catId = [];
				export_catText = [];
				export_catColor = [];
				for(i = 0; i < res.length; i++){
					export_catId[i] = parseInt(res[i].id);
					export_catText[i] = res[i].Category;
					export_catColor[i] = res[i].Color;
				}
				//export_initCombo();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function export_initCombo(thiscombo){
	emptyCombo();
	$(thiscombo).append("<option></option>");
	for (i = 0; i < export_catId.length; i++) {
		appendToCombo(export_catText[i],export_catId[i]);
	}


	function appendToCombo(category,type){
		$(thiscombo).append("<option value=\""+type+"\">"+category+"</option>");
	}


	
	$(thiscombo).select2({placeholder: 'Select a category'});

	function emptyCombo(){
		while (thiscombo.childElementCount != 0){
			thiscombo.removeChild(thiscombo.firstChild);
		}
	}

}
function export_onComboChanged(){
	export_getNbrInCat();
}

function export_onAddClicked(){
	addInput("comboColumn");
}
function addInput(divName) {
    var newDiv = document.createElement('select');
    newDiv.className = 'js-basic-single export';
    newDiv.onchange = export_onComboChanged;
    document.getElementById(divName).appendChild(newDiv);
    export_initCombo(newDiv);
}
function export_onRemoveClicked(){
	var comboColumn = document.getElementById("comboColumn");
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
	var x = document.getElementsByClassName("js-basic-single export");
	for (i = 0; i < x.length; i++) {
		if(x[i].value){
	   		console.log("Category "+x[i].value);
	   		data["ids"].push(x[i].value);
	   }
	}
	
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	var url = site.uri.public + '/segImages/nbrBYcategory';
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
	var x = document.getElementsByClassName("js-basic-single export");
	for (i = 0; i < x.length; i++) {
		if(x[i].value){
	   		console.log("Category "+x[i].value);
	   		data["category"].push(x[i].value);
	   }
	}
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;


	var url = site.uri.public + '/segExport';
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