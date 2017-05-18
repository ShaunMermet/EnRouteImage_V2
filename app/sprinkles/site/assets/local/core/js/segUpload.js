var upl_catId = [];
var upl_catText=[];
var upl_catColor= [];
var upl_comboInitialized = [];
var upl_grpId = [];
var upl_grpText=[];
upl_loadGroups();
function upl_loadGroups(){
	// Fetch the groups
	var url = site.uri.public + '/api/groups';
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
	var url = site.uri.public + '/segCategory/all';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
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
	emptyCombo(comboElem);
	if(comboElem.id == "grpAssignUp"){
		initGrpCombo(comboElem);
	}else if(comboElem.id == "catAssignUp"){
		initCatCombo(comboElem);
	}else
		initStdCombo(comboElem);
}

function initGrpCombo(comboElem){
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

	$(comboElem).select2({placeholder: 'Select a group'})
	.on("change", function(e) {
      this.parentElement.parentElement.children[3].value = this.value;
    });
}
function initCatCombo(comboElem){
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

	$(comboElem).select2({placeholder: 'Select a category'})
	.on("change", function(e) {
      this.parentElement.parentElement.children[0].value = this.value;
    });
}
function initStdCombo(comboElem){
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

	$(comboElem).select2({placeholder: 'Select a category'})
}

function onCatAssignAllChanged(){
	var x = document.getElementsByClassName("js-basic-single");
	for (i = 0; i < x.length; i++) {
	   upl_syncCatAssignUp(x[i]);
	}
}
function upl_syncCatAssignUp(comboElem){
	var masterCombo = document.getElementById("catAssignAll");
	if(comboElem.id == "catAssignUp"){
		$(comboElem).val(masterCombo.value).trigger("change");
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