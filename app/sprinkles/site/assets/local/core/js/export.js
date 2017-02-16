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
	var url = site.uri.public + '/category/all';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        var res = JSON.parse(data);
				for(i = 0; i < res.length; i++){
					export_catId[i] = parseInt(res[i].id);
					export_catText[i] = res[i].Category;
					export_catColor[i] = res[i].Color;
				}
				export_initCombo();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function export_initCombo(){
	$("#combo").append("<option></option>");
	for (i = 0; i < export_catId.length; i++) {
		appendToCombo(export_catText[i],export_catId[i]);
	}


	function appendToCombo(category,type){
		$("#combo").append("<option value=\""+type+"\">"+category+"</option>");
	}


	//$(".js-basic-single").select2({ width: '100px' });
	
	$('#combo').select2({placeholder: 'Select a category'});

}
function export_onComboChanged(){
	export_getNbrInCat();
}

function export_getNbrInCat(){
	
	var data= {};
	var combo = document.getElementById("combo");
	data["category"]=combo.value;

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	// Free the images (became available)
	var url = site.uri.public + '/images/nbrBYcategory';
	$.ajax({
	  type: "PUT",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	var res = JSON.parse(data);
			document.getElementById('imgCounter').innerHTML = res[0]+" Image(s) found";
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
///////////////////////////////

function export_onExportClicked(){
	var selectedCat = document.getElementById("combo").value;
	console.log("Export : "+selectedCat);
	var data= {};
	data["category"]=selectedCat;
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
	window.location.href = "../cloud/export/dl/"+export_token;
	document.getElementById('imgCounter').innerHTML = "";
}