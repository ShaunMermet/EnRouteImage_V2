var upl_catId = [];
var upl_catText=[];
var upl_catColor= [];
var upl_comboInitialized = [];
var upl_grpId = [];
var upl_grpText=[];
upl_loadGroups();
function upl_loadGroups(){
	// Fetch the groups
	var url = site.uri.public + '/api/groups/mygroups';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        console.log(data);
	        var res = data.rows;
        	upl_grpId = [];
			upl_grpText = [];
			for(i = 0; i < res.length; i++){
				upl_grpId[i] = parseInt(res[i].id);
				upl_grpText[i] = res[i].name;
			}
			upl_loadCategories();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function upl_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/category/all2';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	console.log(data);
	        var res = data.rows;
        	upl_catId = [];
			upl_catText = [];
			upl_catColor = [];
			for(i = 0; i < res.length; i++){
				upl_catId[i] = parseInt(res[i].id);
				upl_catText[i] = res[i].Category;
				upl_catColor[i] = res[i].Color;
			}
			upl_initCombos();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function upl_initCombos(){
	var x = document.getElementsByClassName("js-basic-single");
	var i;
	for (i = 0; i < x.length; i++) {
	 	upl_initCombo(x[i]);
	}
}
function upl_initCombo(comboElem){
	preValue = comboElem.value;
	emptyCombo(comboElem);
	if(comboElem.id == "grpAssignUp"){
		initGrpCombo(comboElem,preValue);
	}
	else if(comboElem.id == "catAssignUp"){
		initCatCombo(comboElem,preValue);
	}
	else if(comboElem.id == "grpAssignAll" || comboElem.id == "grpAssignEx"){
		initGrpAllCombo(comboElem,preValue);
	}
	else
		initStdCombo(comboElem,preValue);
}
function initGrpCombo(comboElem, value){
	if(upl_grpText.length == 0 ){
		return;
	}
	$(comboElem).append("<option></option>");
	for (i = 0; i < upl_grpId.length; i++) {
		appendToCombo(upl_grpText[i],upl_grpId[i]);
	}

	function appendToCombo(category,type){
		$(comboElem).append("<option value=\""+type+"\">"+category+"</option>");
	}

	$(comboElem).select2({allowClear: true,placeholder: 'Select a group'})
	.on("change", function(e) {
      this.parentElement.parentElement.children[3].value = this.value;
    });
    $(comboElem).val(value).trigger("change");
}
function initGrpAllCombo(comboElem, value){
	if(upl_grpText.length == 0 ){
		return;
	}
	$(comboElem).append("<option></option>");
	for (i = 0; i < upl_grpId.length; i++) {
		appendToCombo(upl_grpText[i],upl_grpId[i]);
	}

	function appendToCombo(category,type){
		$(comboElem).append("<option value=\""+type+"\">"+category+"</option>");
	}

	$(comboElem).select2({allowClear: true,placeholder: 'Select a group'});
	$(comboElem).val(value).trigger("change");
}
function initCatCombo(comboElem, value){
	if(upl_catText.length == 0 ){
		return;
	}
	$(comboElem).append("<option></option>");
	for (i = 0; i < upl_catId.length; i++) {
		appendToCombo(upl_catText[i],upl_catId[i]);
	}

	function appendToCombo(category,type){
		$(comboElem).append("<option value=\""+type+"\">"+category+"</option>");
	}

	$(comboElem).select2({allowClear: true,placeholder: 'Select a category'})
	.on("change", function(e) {
      this.parentElement.parentElement.children[0].value = this.value;
    });
    $(comboElem).val(value).trigger("change");
}
function initStdCombo(comboElem, value){
	if(upl_catText.length == 0 ){
		return;
	}
	$(comboElem).append("<option></option>");
	for (i = 0; i < upl_catId.length; i++) {
		appendToCombo(upl_catText[i],upl_catId[i]);
	}

	function appendToCombo(category,type){
		$(comboElem).append("<option value=\""+type+"\">"+category+"</option>");
	}

	$(comboElem).select2({allowClear: true,placeholder: 'Select a category'});
	$(comboElem).val(value).trigger("change");
}

function onCatAssignAllChanged(){
	var x = document.getElementsByClassName("js-basic-single cat");
	for (i = 0; i < x.length; i++) {
	   upl_syncCatAssignUp(x[i]);
	}
}
function onGrpAssignAllChanged(){
	var x = document.getElementsByClassName("js-basic-single grp");
	for (i = 0; i < x.length; i++) {
	   upl_syncGrpAssignUp(x[i]);
	}
}
function upl_syncCatAssignUp(comboElem){
	var masterCombo = document.getElementById("catAssignAll");
	if(comboElem.id == "catAssignUp"){
		$(comboElem).val(masterCombo.value).trigger("change");
	}
}
function upl_syncGrpAssignUp(comboElem){
	var masterGrpCombo = document.getElementById("grpAssignAll");
	if(comboElem.id == "grpAssignUp"){
		$(comboElem).val(masterGrpCombo.value).trigger("change");
	}
}
function emptyCombo(comboElem){
	while (comboElem.childElementCount != 0){
		comboElem.removeChild(comboElem.firstChild);
	}
	if(comboElem.id == "catAssignUp" || comboElem.id == "grpAssignUp"){
		comboElem.parentElement.parentElement.children[0].value = "";
	}
}