////  COMBO    //////////////////
var catEdit_catId = [];
var catEdit_catText=[];
var catEdit_catColor= [];
var catEdit_phpPath = "../../php/";
if(document.getElementById("editCatPanel")){
	document.getElementById("editCatPanel").style = "DISPLAY: none;";
}


catEdit_loadCategories();
function catEdit_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/category/all2';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	var res = data.rows;
				catEdit_catId = [];
				catEdit_catText=[];
				catEdit_catColor= [];
				for(i = 0; i < res.length; i++){
					catEdit_catId[i] = parseInt(res[i].id);
					catEdit_catText[i] = res[i].Category;
					catEdit_catColor[i] = res[i].Color;
				}
				catEdit_initCombo();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function catEdit_initCombo(){
	emptyCombo();
	$("#comboEdit").append("<option></option>");
	for (i = 0; i < catEdit_catId.length; i++) {
		catEdit_appendToCombo(catEdit_catText[i],catEdit_catId[i]);
	}


	function catEdit_appendToCombo(category,type){
		$("#comboEdit").append("<option value=\""+type+"\">"+category+"</option>");
	}


	
	$('#comboEdit').select2({placeholder: 'Select a category'});
	
	function emptyCombo(){
		while (comboEdit.childElementCount != 0){
			comboEdit.removeChild(comboEdit.firstChild);
		}
	}

}
function catEdit_onEditClicked(){
	
	catEdit_fillCateditPanel();
	
	if(document.getElementById("editCatPanel")){
		document.getElementById("editCatPanel").style = "DISPLAY: flex;";
	}
}

function catEdit_fillCateditPanel(){
	var combo = document.getElementById("comboEdit");
	var str = combo.options[combo.selectedIndex].text;
	var type = combo.options[combo.selectedIndex].value;
	var color = catEdit_catColor[catEdit_catId.indexOf(parseInt(type))];
	
	if(type){//Edit existing category
		catEditText.value = str;
		catEditText.catType = type;
		document.getElementById('colorPicker').jscolor.fromString(color);
		document.getElementById('saveCatButton').innerHTML = "Edit";
	}
	else{
		catEditText.value = "";
		type = -1;//New category
		catEditText.catType = type;
		document.getElementById('colorPicker').jscolor.fromString("#FFFFFF");
		document.getElementById('saveCatButton').innerHTML = "Create";
	}
}

function catEdit_onAddClicked(){
	var combo = document.getElementById("comboEdit");
	
	catEditText.value = "";
	type = -1;//New category
	catEditText.catType = type;
	document.getElementById('saveCatButton').innerHTML = "Create";
	document.getElementById('colorPicker').jscolor.fromString("#FFFFFF");
		
	if(document.getElementById("editCatPanel")){
		document.getElementById("editCatPanel").style = "DISPLAY: flex;";
	}
}

function catEdit_onCloseCatEditClicked(){
	hideEditRow();
}
function hideEditRow(){
	document.getElementById("editCatPanel").style = "DISPLAY: none;";
}
function onComboEditChanged(){
	catEdit_fillCateditPanel();
}

function catEdit_onSaveCatClicked(){
		if(catEditText.catType == -1){//Create
			catEdit_sendServerEdit("CREATE",catEditText.catType,catEditText.value,colorPicker.value);
		}else{//Edit
		catEdit_sendServerEdit("EDIT",catEditText.catType,catEditText.value,colorPicker.value);
		}
		hideEditRow();
}
function catEdit_onDeleteClicked(){
		if(catEditText.catType == -1){
		}else{
			catEdit_sendServerEdit("DELETE",catEditText.catType,"","");
		}
		hideEditRow();
}

function catEdit_sendServerEdit(mode,catId,catText = "",catColor = ""){
	var data= {};
	if (mode == "CREATE" || mode == "EDIT" || mode == "DELETE"){
		data["mode"]= mode;
		data["catId"] = catId;
		data["catText"] = catText;
		data["catColor"] = catColor;
	}else{
		exit;
	}
		

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	var url = site.uri.public + '/admin/upload/catedit';
	$.ajax({
	  type: "PUT",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	catEdit_loadCategories();
	    	upl_loadCategories();
	    	export_loadCategories();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
