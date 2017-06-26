var validate_imgPathList=[];
var validate_imgPathListIndex = 0;
var validate_imgPath = "../img/light/";
var validate_phpPath = "../../php/";
var validate_srcId = 0;

var validate_imageLoaded = false;
var validate_rectanglesLoaded = false;
var validate_rectanglesList = [];
var validate_currentRectangle = null;


///  COMBO    //////////////////
//creating categories 

var validate_catId = [];
var validate_catText=[];
var validate_catColor= [];



function validate_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/category/all2';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        //var res = JSON.parse(data);
	        var res = data.rows;
				for(i = 0; i < res.length; i++){
					validate_catId[i] = parseInt(res[i].id);
					validate_catText[i] = res[i].Category;
					validate_catColor[i] = res[i].Color;
				}
				validate_initCombo();
				validate_loadGroups();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_loadGroups(){
	// Fetch the groups
	var url = site.uri.public + '/api/groups/mygroups';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        var res = data.rows;
        	validate_grpId = [];
			validate_grpText = [];
			for(i = 0; i < res.length; i++){
				validate_grpId[i] = parseInt(res[i].id);
				validate_grpText[i] = res[i].name;
			}
			validate_initComboGrp();
			validate_loadImages();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function validate_initCombo(){
	for (i = 0; i < validate_catId.length; i++) {
		appendToCombo(validate_catText[i],validate_catId[i]);
	}


	function appendToCombo(category,type){
		//console.log("creating "+category)
		$("#combo").append("<option value=\""+type+"\">"+category+"</option>");
		$("#combo2").append("<option value=\""+type+"\">"+category+"</option>");
	}


	$("#combo").select2({ width: '100px'});
	$("#combo2").select2({allowClear: true});
	$("#combo2").val(-1).trigger("change");
}
function validate_initComboGrp(){
	for (i = 0; i < validate_grpId.length; i++) {
		appendToCombo(validate_grpText[i],validate_grpId[i]);
	}


	function appendToCombo(text,value){
		//console.log("creating "+category)
		$("#combo3").append("<option value=\""+value+"\">"+text+"</option>");
	}


	$("#combo3").select2({width: '100px',placeholder: 'Select a group'});
}
///////////////////////////////


////////////GET IMG FROM SERVER//////
validate_loadCategories();
//validate_loadImages();
validate_loadRects();

function validate_handlerLoadsDone(){
	if(validate_imageLoaded && validate_rectanglesLoaded){
		if (validate_imgPathList.length>0)
			validate_addImage();
		validate_imageLoaded = false;
		validate_rectanglesLoaded = false;
	}
}
function validate_loadRects(){
	// Fetch and render the categories
	var url = site.uri.public + '/areas/all';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
				validate_rectanglesList = data;
			}
			else validate_rectanglesList = [];
			validate_rectanglesLoaded = true;
			validate_handlerLoadsDone();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_loadImages(){
	// Fetch and render the categories
	var combo2 = document.getElementById("combo2");
	var imgCat;
	if(combo2.selectedIndex == -1)
		imgCat = null;
	else
		imgCat = combo2.options[combo2.selectedIndex].value;
	var combo3 = document.getElementById("combo3");
	var imgGrp;
	imgGrp = combo3.options[combo3.selectedIndex].value;
	var data= {};
	data["catID"]=imgCat;
	data["grpID"]=imgGrp;
	console.log(imgCat);
	console.log(imgGrp);
	var url = site.uri.public + '/images/annotated';
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
	    		validate_imgPathList = data;//res;
			}
			else validate_imgPathList = [];
			if(validate_imgPathList.length == 0){
				document.getElementById('imgCounter').style = "DISPLAY: initial;";
				updateNbrAreas();
			}
			validate_imgPathListIndex = 0;
			validate_imageLoaded = true;
			validate_handlerLoadsDone();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function validate_addImage(){
	if(validate_imgPathList.length == 0){
		document.getElementById("moreButton").style = "DISPLAY: initial;";
		document.getElementById("RejectButton").style = "DISPLAY: none;";
		document.getElementById("ValidateButton").style = "DISPLAY: none;";
		return;
	}
	var nativeWidth = validate_imgPathList[validate_imgPathListIndex].naturalWidth;
	var nativeHeight = validate_imgPathList[validate_imgPathListIndex].naturalHeight;
	var img = document.getElementById('image');
	var imgContainer = document.getElementsByClassName('labelimg-container');
	if(nativeWidth/nativeHeight > 16/9){
		console.log("wide image");
		img.style.height = "100%";
		img.style.width = "";
		imgContainer[0].style.height = "calc(100vh - 168px)";
	}else{
		console.log("classic image");
		img.style.height = "";
		img.style.width = "100%";
		imgContainer[0].style.height = "";
	}
	validate_srcId = validate_imgPathList[validate_imgPathListIndex].id;
	var imgName = validate_imgPathList[validate_imgPathListIndex].path;
	var imgToAdd = validate_imgPath+imgName;
	document.getElementById('image').src = imgToAdd;//$('#preview').html("<img id='image' unselectable='on' src='"+imgToAdd+"' />")
	
	function loaded() {
	  //alert('loaded')
	  validate_drawRects(validate_srcId);//initSelection();
	  img.removeEventListener('load', loaded);
	  img.removeEventListener('load', error);
	}
	function error() {
		//alert('error');
	}
	if (img.complete) {
	  loaded();
	} else {
	  img.addEventListener('load', loaded)
	  img.addEventListener('error', error)
	}
	
	document.getElementById('imgCounter').style = "DISPLAY: none;";//"Image "+(validate_imgPathListIndex+1)+" of "+validate_imgPathList.length;
	document.getElementById("moreButton").style = "DISPLAY: none;";
	document.getElementById("RejectButton").style = "DISPLAY: initial;";
	document.getElementById("ValidateButton").style = "DISPLAY: initial;";
}
function validate_drawRects(idImage){
	for(var i = 0; i < validate_rectanglesList.length; ++i){
		reviewedRect = validate_rectanglesList[i];
		if(parseInt(reviewedRect.source) == idImage){
			var refImage = document.getElementById('image');
			var initRatio = validate_getImgRatio();
			//var leftImage = refImage.offsetLeft;
			//var topImage = refImage.offsetTop ;
			//console.log("matched rect found");
			validate_currentRectangle = document.createElement('div');
			validate_currentRectangle.className = 'rectangle';
			var str = reviewedRect.category.Category;
			var type = reviewedRect.rectType;
			var color = reviewedRect.category.Color;
			validate_currentRectangle.rectType = type;
			validate_currentRectangle.rectSetRatio = 1;
			validate_currentRectangle.rectSetLeft = parseInt(reviewedRect.rectLeft);
			validate_currentRectangle.rectSetTop = parseInt(reviewedRect.rectTop);
			validate_currentRectangle.rectSetWidth = reviewedRect.rectRight - reviewedRect.rectLeft;
			validate_currentRectangle.rectSetHeight = reviewedRect.rectBottom - reviewedRect.rectTop;
			validate_currentRectangle.style.left = /*(leftImage+*/parseInt(reviewedRect.rectLeft)*initRatio/*)*/ + 'px';
			validate_currentRectangle.style.top = /*(topImage+*/parseInt(reviewedRect.rectTop)*initRatio/*)*/ + 'px';
			validate_currentRectangle.style.border= "3px solid "+color;
			validate_currentRectangle.style.color= color;
			var text = document.createElement('div');
			var t = document.createTextNode(str);
			text.className = 'rectangleText';
			text.appendChild(t);
			validate_currentRectangle.appendChild(text);
			validate_currentRectangle.style.width = (reviewedRect.rectRight - reviewedRect.rectLeft)*initRatio + 'px';
			validate_currentRectangle.style.height = (reviewedRect.rectBottom - reviewedRect.rectTop)*initRatio + 'px';
			(document.getElementById('preview')).appendChild(validate_currentRectangle);
			validate_adaptText();
			updateNbrAreas();
		}
	}
}
function validate_getImgRatio(){
	var refImage = document.getElementById('image');
	return refImage.clientWidth/refImage.naturalWidth;
}
window.addEventListener("resize", function(){
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	var elements = document.getElementsByClassName("rectangle");
	var ratio = validate_getImgRatio();
	if(elements.length>0){
		for (var i = 0; i < elements.length; ++i) {
			elements[i].style.left = parseFloat(elements[i].rectSetLeft*ratio/elements[i].rectSetRatio) + 'px';
			elements[i].style.top = parseFloat(elements[i].rectSetTop*ratio/elements[i].rectSetRatio) + 'px';
			elements[i].style.width = parseFloat(elements[i].rectSetWidth*ratio/elements[i].rectSetRatio) + 'px';
			elements[i].style.height = parseFloat(elements[i].rectSetHeight*ratio/elements[i].rectSetRatio) + 'px';
		}
	}
});
function validate_adaptText(){
	////  X //////
	var refImage = document.getElementById('image');
	var textWidth = validate_currentRectangle.childNodes[0].scrollWidth;
	var leftImage = refImage.offsetLeft - 0;
	if((parseFloat(validate_currentRectangle.style.left) + textWidth) >= (leftImage+refImage.width)){
		validate_currentRectangle.childNodes[0].style.left = -textWidth + 'px';
	}
	else{
		validate_currentRectangle.childNodes[0].style.left = 0 + 'px';
	}
	////  Y //////
	var refImage = document.getElementById('image');
	var textHeight = validate_currentRectangle.childNodes[0].scrollHeight;
	var topImage = refImage.offsetTop;
	if((parseFloat(validate_currentRectangle.style.top) + textHeight) >= (topImage+refImage.height)){
		validate_currentRectangle.childNodes[0].style.top = -textHeight + 'px';
	}
	else{
		validate_currentRectangle.childNodes[0].style.top = 0 + 'px';
	}
}

function validate_nextImage(){
	if(validate_imgPathList.length>0){
		validate_wipeRectangle();
		validate_removeImage();
		tools_freeImage(validate_imgPathList[validate_imgPathListIndex].id);
		validate_imgPathListIndex++;
		if(validate_imgPathListIndex<validate_imgPathList.length)
			validate_addImage();
		else{
			console.log("no more img");
			//document.getElementById("moreButton").style = "DISPLAY: initial;";
			//document.getElementById("RejectButton").style = "DISPLAY: none;";
			//document.getElementById("ValidateButton").style = "DISPLAY: none;";
			validate_onMoreClicked();
		}
	}else{
		validate_onMoreClicked();
	}
}

function validate_removeImage(){
	var refImage = document.getElementById('image');
	if(refImage){
		//refImage.remove();
		refImage.src = "";
	}
}
function validate_wipeRectangle(){
	var elements = document.getElementsByClassName("rectangle");
	while(elements.length>0){
		elements[0].remove();
		
	}
}

/////////////////////////

function validate_onValidateClicked(){
	console.log("Validate");
	//validate_nextImage();
	validate_sendData(1);
}

function validate_onRejectClicked(){
	console.log("Reject");
	//validate_nextImage();
	validate_sendData(0);
}

function validate_sendData(validated){

	var data= {};
	data["dataSrc"]=validate_srcId;
	data["validated"]=validated;
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;
	// Validate or reject areas
	var url = site.uri.public + '/bbox/validate/evaluate';
	$.ajax({
	  type: "PUT",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	validate_nextImage();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_onNextClicked(){
	validate_nextImage();
}
function validate_onMoreClicked(){
	validate_loadImages();
	validate_loadRects();
	console.log("Load more");
}
window.onbeforeunload = function(e) {
	for(var i = validate_imgPathListIndex; i < validate_imgPathList.length; ++i){
		tools_freeImage (validate_imgPathList[i].id);
		console.log("Free " +validate_imgPathList[i].id);
	}
};
function updateNbrAreas(){
	var elements = document.getElementsByClassName("rectangle");
	document.getElementById('value1').innerHTML = elements.length;
}
window.onscroll = function(){
	var top  = window.pageYOffset || document.documentElement.scrollTop;
	var filler = document.getElementById('filler');
	var leftMenu = document.getElementById('leftMenu');
	var intendedHeight = top-filler.parentElement.offsetTop;
	if(intendedHeight < 0) intendedHeight = 0;
	var heightTest = ( intendedHeight + leftMenu.offsetHeight) < filler.parentElement.offsetHeight;
	if( heightTest > 0 )
		filler.style.height = (intendedHeight)+ 'px';
};
////////////////////////////////////////////