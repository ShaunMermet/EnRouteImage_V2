////  COMBO    //////////////////
var setEdit_phpPath = "../../php/";

var setEdit_pagemode = "";//"bbox","segmentation"
function setEdit_initEdit(pagemode){
	setEdit_pagemode = pagemode;
}
function setEdit_onEditClicked(){
	
	setEdit_fillSetEditPanel();
	
	if(document.getElementById("editSetPanel")){
		document.getElementById("editSetPanel").style = "DISPLAY: flex;";
	}
}

function setEdit_fillSetEditPanel(){
	var combo = document.getElementById("setEditList");
	var id = combo.options[combo.selectedIndex].value;
	
	if(id){//Edit existing category
		setEditText.setId = id;
		//var pos = upl_setId.indexOf(parseInt(setEditText.setId));
		var pos = getIndexFromParam(upl_set,"id",setEditText.setId)
		setEditText.value = upl_set[pos]["name"];
		$("#setGrpList").val(upl_set[pos]["group"].id).trigger("change.select2");
	}
	else{
		setEdit_newSet();
	}
}

function getIndexFromParam (array, param, value){
	for(var i = 0; i < array.length; i++){
		if(array[i][param] == value){
			return i;
		}
	}
}

function setEdit_onAddClicked(){
	setEdit_newSet();
}
function setEdit_newSet(){
	setEditText.value = "";
	id = -1;//New category
	setEditText.setId = id;
	if(document.getElementById("editSetPanel")){
		document.getElementById("editSetPanel").style = "DISPLAY: flex;";
	}
	$("#setGrpList").val(1).trigger("change");//Default public
}

function setEdit_onCloseSetEditClicked(){
	setEdit_hideEditRow();
}
function setEdit_hideEditRow(){
	document.getElementById("editSetPanel").style = "DISPLAY: none;";
}
function onSetListChanged(){
	setEdit_fillSetEditPanel();
}

function setEdit_onSaveCatClicked(){
		if(setEditText.setId == -1){//Create
			setEdit_sendServerEdit("CREATE",setEditText.setId,setEditText.value,setGrpList.value);
		}else{//Edit
			setEdit_sendServerEdit("EDIT",setEditText.setId,setEditText.value,setGrpList.value);
		}
		setEdit_hideEditRow();
}
function setEdit_onDeleteClicked(){
		if(setEditText.setId >= 0){
			setEdit_sendServerEdit("DELETE",setEditText.setId,"","");
		}
		setEdit_hideEditRow();
}

function setEdit_sendServerEdit(mode,setId,setText = "",setGroup = ""){
	var data= {};
	if (mode == "CREATE" || mode == "EDIT" || mode == "DELETE"){
		data["mode"]= mode;
		data["setId"] = setId;
		data["setName"] = setText;
		if(setGroup <= 0)setGroup = 1;//Public by default
		data["setGroup"] = setGroup;
	}else{
		exit;
	}
		

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	
	if(setEdit_pagemode == "bbox"){
		var url = site.uri.public + '/admin/upload/setEdit';
	}else if(setEdit_pagemode == "segmentation"){
		var url = site.uri.public + '/admin/segUpload/setEdit';
	}
	$.ajax({
	  type: "PUT",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	upl_loadSets();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
