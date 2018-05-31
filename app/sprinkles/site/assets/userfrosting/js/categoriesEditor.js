////  COMBO    //////////////////
var catEdit_phpPath = "../../php/";
var catEdit_pagemode = "";//"bbox","segmentation"

function catEdit_initEdit(pagemode){
	catEdit_pagemode = pagemode;
}
function catEdit_onEditClicked(){
	
	catEdit_fillCateditPanel();
	
	if(document.getElementById("editCatPanel")){
		document.getElementById("editCatPanel").style = "DISPLAY: flex;";
	}
}

function catEdit_fillCateditPanel(){
	var combo = document.getElementById("catEditList");
	var type = combo.options[combo.selectedIndex].value;
	var selectedCat = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == type})];
	var color = selectedCat.Color;
	var assignedSet = selectedCat.set_id;
	var str = selectedCat.Category;
	
	$("#catEditSetList").val(assignedSet).trigger("change.select2");

	if(type){//Edit existing category
		catEditText.value = str;
		catEditText.catType = type;
		document.getElementById('colorPicker').jscolor.fromString(color);
	}
	else{
		catEditText.value = "";
		type = -1;//New category
		catEditText.catType = type;
		document.getElementById('colorPicker').jscolor.fromString("#FFFFFF");
	}
}

function catEdit_onAddClicked(){
	var combo = document.getElementById("comboEdit");
	
	catEditText.value = "";
	type = -1;//New category
	catEditText.catType = type;
	document.getElementById('colorPicker').jscolor.fromString("#FFFFFF");
		
	if(document.getElementById("editCatPanel")){
		document.getElementById("editCatPanel").style = "DISPLAY: flex;";
	}
}

function catEdit_onCloseCatEditClicked(){
	catEdit_hideEditRow();
}
function catEdit_hideEditRow(){
	document.getElementById("editCatPanel").style = "DISPLAY: none;";
}
function onComboEditChanged(){
	catEdit_fillCateditPanel();
}

function catEdit_onSaveCatClicked(){
		if(catEditText.catType == -1){//Create
			catEdit_sendServerEdit("CREATE",catEditText.catType,catEditText.value,colorPicker.value,catEditSetList.value);
		}else{//Edit
		catEdit_sendServerEdit("EDIT",catEditText.catType,catEditText.value,colorPicker.value,catEditSetList.value);
		}
		catEdit_hideEditRow();
}
function catEdit_onDeleteClicked(){
		if(catEditText.catType == -1){
		}else{
			catEdit_sendServerEdit("DELETE",catEditText.catType,"","");
		}
		catEdit_hideEditRow();
}

function catEdit_sendServerEdit(mode,catId,catText = "",catColor = "",catSetId){
	var data= {};
	if (mode == "CREATE" || mode == "EDIT" || mode == "DELETE"){
		data["mode"]= mode;
		data["catId"] = catId;
		data["catText"] = catText;
		data["catColor"] = catColor;
		data["catSetId"] = catSetId;
	}else{
		exit;
	}
		

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	if(catEdit_pagemode == "bbox"){
		var url = site.uri.public + '/admin/upload/catedit';
	}else if(catEdit_pagemode == "segmentation"){
		var url = site.uri.public + '/admin/segUpload/catedit';
	}
	$.ajax({
	  type: "PUT",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	//catEdit_loadCategories();
	    	upl_loadCategories();
	    	//export_loadCategories();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
