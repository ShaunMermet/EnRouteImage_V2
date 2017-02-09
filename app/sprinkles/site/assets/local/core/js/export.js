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
	var http_req = new XMLHttpRequest();
	var url = export_phpPath+"get_category.php";

	http_req.open("GET", url, true);

	http_req.onreadystatechange = function() {
		if (http_req.readyState == 4 && http_req.status == 200) {
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			var res = JSON.parse(http_req.responseText);
			for(i = 0; i < res.length; i++){
				export_catId[i] = parseInt(res[i].id);
				export_catText[i] = res[i].Category;
				export_catColor[i] = res[i].Color;
			}
			export_initCombo();
		}
	};
	http_req.send();
}

function export_initCombo(){
	$("#combo").append("<option></option>");
	for (i = 0; i < export_catId.length; i++) {
		appendToCombo(export_catText[i],export_catId[i]);
	}


	function appendToCombo(category,type){
		$("#combo").append("<option value=\""+type+"\">"+category+"</option>");
	}


	$(".js-basic-single").select2({ width: '100px' });
	
	$('#combo').select2({placeholder: 'Select a category'});

}
function export_onComboChanged(){
	export_getNbrInCat();
}

function export_getNbrInCat(){
	
	var data= {};
	var combo = document.getElementById("combo");
	data["category"]=combo.value;
	var http_req = new XMLHttpRequest();
	var url = export_phpPath+"post_nbrImgInCat.php";

	http_req.open("POST", url, true);

	http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	
	http_req.onreadystatechange = function() {
		if(http_req.readyState == 4 && http_req.status == 200) {
			console.log(location.hostname);
			console.log(window.location.pathname);
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			var res = JSON.parse(http_req.responseText);
			console.log(res[0]);
			document.getElementById('imgCounter').innerHTML = res[0]+" Image(s) found";
		}
	}
	var json = JSON.stringify(data);
	http_req.send("data=" +json);
}
///////////////////////////////

function export_onExportClicked(){
	var selectedCat = document.getElementById("combo").value;
	console.log("Export : "+selectedCat);
	var data= {};
	data["category"]=selectedCat;
	var http_req = new XMLHttpRequest();
	var url = export_phpPath+"post_export.php";

	http_req.open("POST", url, true);

	http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	
	http_req.onreadystatechange = function() {
		if(http_req.readyState == 4 && http_req.status == 200) {
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			console.log(http_req.responseText);
			console.log("Response Over");
			var res = JSON.parse(http_req.responseText);
			if(typeof res === 'object' && "link" in res){
				document.getElementById("dlButton").disabled = false;
				document.getElementById("dlButton").style.opacity = 1;
				export_token = res.link;
				document.getElementById('imgCounter').innerHTML = "Download ready";
				//document.getElementById('dlLink').innerHTML = "<a href='../download.php?id="+export_token+"'>Download ready</a>";
			}
			else if( res == "No file found")
				document.getElementById('imgCounter').innerHTML = "No file";
			
		}
	}
	var json = JSON.stringify(data);
	http_req.send("data=" +json);
	document.getElementById('imgCounter').innerHTML = "Preparing download...";
}
function export_onDlClicked(){
	document.getElementById("dlButton").disabled = true;
	document.getElementById("dlButton").style.opacity = 0.5;
	window.location.href = "../public/download.php?id="+export_token;
	document.getElementById('imgCounter').innerHTML = "";
}
window.onbeforeunload = function(e) {
	//export_freeDL (export_token);
		console.log("Free " +export_token);
};
function export_freeDL (token){
		var data= {};
		data["token"]=token;
		////////////////////// POST  //////////////
		var http_req = new XMLHttpRequest();
		var url = export_phpPath+"post_freeDL.php";

		http_req.open("POST", url, true);

		//Send the proper header information along with the request
		http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		
		http_req.onreadystatechange = function() {//Call a function when the state changes.
			if(http_req.readyState == 4 && http_req.status == 200) {
				if(http_req.responseText == "session_closed")
					window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
				console.log(http_req.responseText);
			}
		}
		var json = JSON.stringify(data);
		http_req.send("data=" +json);
		////////////////////////////////////////////////
}