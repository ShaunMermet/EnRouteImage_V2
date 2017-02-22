var upl_catId = [];
var upl_catText=[];
var upl_catColor= [];
var upl_comboInitialized = [];
upl_loadCategories();
function upl_loadCategories(){
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
	if(comboElem.length > 0){
		return;
	}
	if(upl_catText.length == 0 ){
		return;
	}
	emptyCombo();
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
	}
}