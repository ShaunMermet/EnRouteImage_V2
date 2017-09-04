var upl_comboInitialized = [];
var upl_grpId = [];
var upl_grpText=[];
var upl_set = [];
var upl_pagemode = "";//"bbox","segmentation"
var mainContainer = {};

function upl_initPage(pagemode){
	upl_pagemode = pagemode;
	upl_loadGroups();
	upl_loadSets();
}
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
	if(upl_pagemode == "bbox"){
		var url = site.uri.public + '/category/all';
	}else if(upl_pagemode == "segmentation"){
		var url = site.uri.public + '/segCategory/all';
	}
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	mainContainer.catData = data;
			upl_initCombos();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function upl_loadSets(){
	// Fetch the groups
	if(upl_pagemode == "bbox"){
		var url = site.uri.public + '/api/sets/mysets';
	}else if(upl_pagemode == "segmentation"){
		var url = site.uri.public + '/api/segSets/mysets';
	}
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        console.log(data);
			for(i = 0; i < data.length; i++){
				upl_set[i] = {};
				upl_set[i]['id'] = parseInt(data[i].id);
				upl_set[i]['name'] = data[i].name;
				upl_set[i]['group'] = data[i].group;
			}
			upl_set.sort(function(a, b){return a.id-b.id})
			upl_initCombo(document.getElementById("setEditList"));

	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function upl_initComboClass(className){
	var x = document.getElementsByClassName(className);
	var i;
	for (i = 0; i < x.length; i++) {
	 	upl_initCombo(x[i]);
	}
}
function upl_initCombos(){
	upl_initComboClass("js-basic-single");
}
function upl_onAddInitCombos(){
	var groupAll = document.getElementById("grpAssignAll");
	var catAll = document.getElementById("catAssignAll");
	upl_initCombo(groupAll);
	upl_initCombo(catAll);
	upl_initCombo(document.getElementById("setAssignAll"));
	upl_initComboClass("js-basic-single grp");
	upl_initComboClass("js-basic-single cat");
	upl_initComboClass("js-basic-single set");
}
function upl_onAddInitRowCombos(row){
	console.log(row);
	upl_initRowCombos(row);
}
function upl_initRowCombos(row){
	setCombo = row.getElementsByClassName("js-basic-single set");
	upl_initCombo(setCombo[0], row);
}
function upl_initCombo(comboElem, row){
	preValue = comboElem.value;
	emptyCombo(comboElem);
	//initCombo(comboElem, preValue, Type, Allowclear, slave)
	if(comboElem.id == "grpAssignUp"){
		initCombo(comboElem, preValue, "GRP", true, "SLAVE");
	}
	else if(comboElem.id == "grpAssignAll" || comboElem.id == "grpAssignEx" || comboElem.id == "grpAssignFolder"){
		initCombo(comboElem, preValue, "GRP", true);
	}
	else if(comboElem.id == "setGrpList"){
		initCombo(comboElem, preValue, "GRP");
	}
	else if(comboElem.id == "setEditList" || comboElem.id == "setAssignEx" || comboElem.id == "catEditSetList"){
		initCombo(comboElem, preValue, "SET");
	}
	else if(comboElem.id == "setAssignAll" || comboElem.id == "setAssignFolder"){
		initCombo(comboElem, preValue, "SET", true);
	}
	else if(comboElem.id == "setAssignUp"){
		initCombo(comboElem, preValue, "SET", true, "SLAVE", row);
	}
	else if(comboElem.id == "setAssignDown"){
		downTable = document.getElementById("downloadTable");
		if(downTable.contains(comboElem)){
			row = downTable.getElementsByClassName("TD set id "+comboElem.getAttribute("data-imgID"));
			value = row[0].getAttribute("data-setID");
			initCombo(comboElem, value, "SET", false, "DOWN");
		}
	}
	else if(comboElem.id == "catAssignUp"){
		initCombo(comboElem, preValue, "CAT", true, "SLAVE");
	}
	else if(comboElem.id == "catAssignAll" || comboElem.id == "catAssignEx" || comboElem.id == "catAssignFolder"){
		initCombo(comboElem, preValue, "CAT", true);
	}
	else if(comboElem.id == "catEditList"){
		initCombo(comboElem, preValue, "CAT");
	}
}
function initCombo(comboElem, value, type, allowClear = false, mode = "", row=null){
	function appendToCombo(text,data){
		$(comboElem).append("<option value=\""+data+"\">"+text+"</option>");
	}
	$(comboElem).append("<option></option>");
	
	if(type == "SET"){
		for (i = 0; i < upl_set.length; i++) {
			appendToCombo(upl_set[i]['name']+" ("+upl_set[i]['group'].name+")",upl_set[i]['id']);
		}
		if(mode == "SLAVE"){
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a set'})
			.on("change", function(e) {
				hiddenSet = row.getElementsByClassName("hiddenSet");
		    	hiddenSet[0].value = this.value;
		    });
		    $(comboElem).val(value).trigger("change.select2");
		}
		else if (mode == "DOWN"){
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a set'})
			.on("change", function(e) {
		    	console.log("down change "+ this.value);
		    	upl_setImgSet(this.getAttribute("data-imgID"), this.value);
		    });
		    $(comboElem).val(value).trigger("change.select2");
		}
		else{
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a set'});
		}
	}
	else if (type == "GRP"){
		for (i = 0; i < upl_grpId.length; i++) {
			appendToCombo(upl_grpText[i],upl_grpId[i]);
		}
		if(mode == "SLAVE"){
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a group'})
			.on("change", function(e) {
		      	hiddenGrp = document.getElementsByClassName("hiddenGroup "+this.getAttribute("data-imgID"));
	    		hiddenGrp[0].value = this.value;
		    });
		    $(comboElem).val(value).trigger("change.select2");
		}
		else{
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a group'});
		}
	}
	else if (type == "CAT"){
		for (i = 0; i < mainContainer.catData.length; i++) {
			var cat = mainContainer.catData[i];
			appendToCombo(cat.Category+" - "+cat.set.name+" ("+cat.set.group.name+")", cat.id);
		}
		if(mode == "SLAVE"){
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a category'})
			.on("change", function(e) {
		    	hiddenCat = document.getElementsByClassName("hiddenCategory "+this.getAttribute("data-imgID"));
	    		hiddenCat[0].value = this.value;
		    });
    		$(comboElem).val(value).trigger("change.select2");
		}
		else{
			$(comboElem).select2({allowClear: allowClear,placeholder: 'Select a category'});
		}

	}else
		console.log("no combo type !")
}

function onSetAssignAllChanged(){
	var row = document.getElementsByClassName("template-upload fade");
	for (i = 0; i < row.length; i++) {
	   upl_syncSetRowCombos(row[i]);
	}
}
function upl_syncSetRowCombos(row){
	var masterCombo = document.getElementById("setAssignAll");
	var combo = row.getElementsByClassName("js-basic-single set");
	$(combo[0]).val(masterCombo.value).trigger("change");

}
function upl_syncSetAssignUp(comboElem){
	var masterCombo = document.getElementById("setAssignAll");
	if(comboElem.id == "setAssignUp"){
		$(comboElem).val(masterCombo.value).trigger("change");
	}
}
function emptyCombo(comboElem){
	while (comboElem.childElementCount != 0){
		comboElem.removeChild(comboElem.firstChild);
	}
	if(comboElem.id == "catAssignUp" || comboElem.id == "grpAssignUp" || comboElem.id == "setAssignUp"){
		comboElem.parentElement.parentElement.children[0].value = "";
	}
}
function onDownloadImageSetChanged(){
	upl_GetImg();
}
function upl_GetImg(){
	console.log("fetch image");
	var form = document.getElementById("fileupload");
	var dlTable = document.getElementById("downloadTable");
	if(upl_pagemode == "bbox"){
		var url = site.uri.public + '/admin/upload/upload';
	}else if(upl_pagemode == "segmentation"){
		var url = site.uri.public + '/admin/segUpload/upload';
	}
	
	var data= {};
	data["set"]= $("#setAssignFolder")[0].value;
	$.ajax({
	  type: "GET",
	  url: url,
	  dataType: 'json',
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
		    dlTable.children[0].innerHTML = "";
	        $(form).fileupload('option', 'done')
	            .call(form, $.Event('done'), {result: data});
	        upl_initComboClass("js-basic-single setDown");
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function upl_setImgSet(imgId, setId){
	if(upl_pagemode == "bbox"){
		var url = site.uri.public + '/images/imgEdit';
	}else if(upl_pagemode == "segmentation"){
		var url = site.uri.public + '/segImages/imgEdit';
	}
	
	var data= {};
	data["imgId"]= imgId;
	data["imgSet"]= setId;
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;
	$.ajax({
	  type: "PUT",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	console.log("success");
	    	upl_GetImg();
	    },
	    // Fetch failed
	    function (data) {
	        console.log("fail");
	    }
	);
	setCloseEdit(imgId);
}

function onDownSetEditClicked(elmnt){
	setDisplayEdit(elmnt.getAttribute("data-imgID"));
}
function onDownSetCloseClicked(elmnt){
	setCloseEdit(elmnt.getAttribute("data-imgID"));
}
function setDisplayEdit(imgID){
	downTable = document.getElementById("downloadTable");
	//display
	display = downTable.getElementsByClassName("TD set id "+imgID);
	display[0].style.display = "none";
	//Edit button
	editButton = downTable.getElementsByClassName("TD editButton "+imgID);
	editButton[0].style.display = "none";
	//select
	select = downTable.getElementsByClassName("TD spanSelect "+imgID);
	select[0].style.display = "initial";
	//Close button
	closeButton = downTable.getElementsByClassName("TD closeButton "+imgID);
	closeButton[0].style.display = "initial";
}
function setCloseEdit(imgID){
	downTable = document.getElementById("downloadTable");
	//display
	display = downTable.getElementsByClassName("TD set id "+imgID);
	display[0].style.display = "initial";
	//Edit button
	editButton = downTable.getElementsByClassName("TD editButton "+imgID);
	editButton[0].style.display = "initial";
	//select
	select = downTable.getElementsByClassName("TD spanSelect "+imgID);
	select[0].style.display = "none";
	//Close button
	closeButton = downTable.getElementsByClassName("TD closeButton "+imgID);
	closeButton[0].style.display = "none";
}
function upl_hideTemplateDonwnInUpload(){
	upTable = document.getElementById("uploadTable");
	row = upTable.getElementsByClassName("template-download");
	for(i = 0; i < row.length; i++){
		setColumn = row[i].getElementsByClassName("TD-settd");
		buttonColumn = row[i].getElementsByClassName("TD-button");
		setColumn[0].style.display = "none";
		buttonColumn[0].style.display = "none";
		
	}
}
function upl_onDeleteClicked(data){
	console.log("itel deleeted");
	rows = document.getElementsByClassName("template-download fade "+ data.getAttribute("data-imgID"));
	for(i = 0; i < rows.length; i++){
		deleteBtn = rows[i].getElementsByClassName("btn btn-danger delete");
		deleteBtn[0].click();
	}
	//upl_GetImg()
}
$('#fileupload').bind('fileuploadsubmit', function (e, data) {
    var inputs = data.context.find(':input');
    if (inputs.filter(function () {
            return !this.value && $(this).prop('required');
        }).first().focus().length) {
        data.context.find('button').prop('disabled', false);
        return false;
    }
    data.formData = inputs.serializeArray();
    // add csrf
    var csrfName = {name:site.csrf.keys.name, value:site.csrf.name};
    var csrfValue = {name:site.csrf.keys.value, value:site.csrf.value};
    data.formData.push(csrfName);
    data.formData.push(csrfValue);
});