var upl_catId = [];
var upl_catText=[];
var upl_catColor= [];
var upl_comboInitialized = [];
upl_loadCategories();
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
	emptyCombo();
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
	
	function emptyCombo(){
		while (comboElem.childElementCount != 0){
			comboElem.removeChild(comboElem.firstChild);
		}
		if(comboElem.id == "catAssignUp"){
			comboElem.parentElement.parentElement.children[0].value = "";
		}
	}
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