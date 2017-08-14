var validate_imgPathList=[];
var validate_imgPathListIndex = 0;
var validate_imgPath = "../img/segmentation/light/";
var validate_phpPath = "../../php/";
var validate_srcId = 0;

var validate_AreasList = [];
var validate_currentRectangle = null;


///  COMBO    //////////////////
//creating categories 

var validate_catId = [];
var validate_catText=[];
var validate_catColor= [];



function validate_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/segCategory/all';
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
				validate_loadset();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_loadset(){
	// Fetch the sets
	var url = site.uri.public + '/api/segSets/mysets';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        var res = data;
	        validate_set = [];
			for(i = 0; i < res.length; i++){
				validate_set[i] = {};
				validate_set[i]['id'] = parseInt(res[i].id);
				validate_set[i]['name'] = res[i].name;
				validate_set[i]['group'] = res[i].group;
			}
			validate_set.sort(function(a, b){return a.id-b.id})
			validate_initComboSet();
			validate_loadImages();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function validate_initComboSet(){
	for (i = 0; i < validate_set.length; i++) {
		appendToCombo(validate_set[i]['name']+" ("+validate_set[i]['group'].name+")",validate_set[i]['id']);
	}


	function appendToCombo(text,value){
		$("#combo4").append("<option value=\""+value+"\">"+text+"</option>");
	}


	$("#combo4").select2({width: '100px',placeholder: 'Select a set'});
}
///////////////////////////////


////////////GET IMG FROM SERVER//////
validate_loadCategories();

function validate_loadImages(){
	// Fetch and render the images
	var combo4 = document.getElementById("combo4");
	var imgSet;
	imgSet = combo4.options[combo4.selectedIndex].value;
	var data= {};
	data["setID"]=imgSet;
	var url = site.uri.public + '/segImages/annotated';
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
			validate_loadRects();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_loadRects(){
	var data= {};
	data["ids"]=[];
	for(var i = 0; i < validate_imgPathList.length; ++i){
		data["ids"].push(validate_imgPathList[i].id);
	}
	console.log("Sources requested");
	console.log(data);
	// Fetch and render the categories
	var url = site.uri.public + '/segAreas/byIds';
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
				validate_AreasList = data;
				console.log("areas retrived");
				console.log(data);
			}
			else validate_AreasList = [];
			validate_addImage();
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
	  validate_drawLegend(validate_srcId);
	  validate_drawAreas(validate_srcId);//initSelection();
	  img.removeEventListener('load', loaded);
	  img.removeEventListener('load', error);
	  updateNbrAreas();
	}
	function error() {
		
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
function validate_drawAreas(idImage){
	var areaCanvas = document.getElementById("areaCanvas");
	var refImage = document.getElementById('image');
	areaCanvas.width = refImage.width;
	areaCanvas.height = refImage.height;
	var initRatio = validate_getImgRatio();
	for(var i = 0; i < validate_AreasList.length; ++i){
		reviewedArea = validate_AreasList[i];
		if(parseInt(reviewedArea.source) == idImage){

			console.log(reviewedArea);
			areaCtx = areaCanvas.getContext("2d");
			areaCtx.lineJoin = "round";
			areaCtx.beginPath();
			var coordList = JSON.parse( reviewedArea.data );
			areaCtx.moveTo(coordList[0][0]*initRatio, coordList[0][1]*initRatio);

			for(var j = 1; j < coordList.length; ++j){
				areaCtx.lineTo(coordList[j][0]*initRatio, coordList[j][1]*initRatio);
			}
			
			var color = reviewedArea.category.Color;
			areaCtx.globalAlpha=0.5;
	 		areaCtx.fillStyle = color;//"#ff0000";
	 		areaCtx.lineWidth  = 3;
	 		areaCtx.strokeStyle = "#ffffff";
	 		areaCtx.closePath();
			areaCtx.fill();
			areaCtx.globalAlpha=1;
			areaCtx.stroke();
		}
	}
}
function validate_drawLegend(idImage){
	var legendDiv = document.getElementById("legend");
	var legend = {};
	for(var i = 0; i < validate_AreasList.length; ++i){
		reviewedArea = validate_AreasList[i];
		if(parseInt(reviewedArea.source) == idImage){
			legend[reviewedArea.category.id] = reviewedArea.category;
		}
	}
	console.log(legend);
	for (var key in legend){
		var cat = legend[key];
		legContainer = document.createElement('div');
		legContainer.style.display = "inline-flex";
		legContainer.className = 'legend';
		
		legColor = document.createElement('div');
		legColor.style.width = legColor.style.height = 30 +'px';
		legColor.style.background = cat.Color;
		legColor.style.border= "1px solid "+"#000000";
		legColor.title = cat.Category;
		legContainer.appendChild(legColor);
		
		legText = document.createElement('div');
		legText.style.width = 65 +'px';
		legText.style.margin = "4px 0px 0px 3px";
		legText.style.overflow = "hidden";
		legText.style.textOverflow = "ellipsis";
		legText.title = cat.Category;
		t = document.createTextNode(cat.Category);
		legText.appendChild(t);
		legContainer.appendChild(legText);
		
		legendDiv.appendChild(legContainer);
	}
}
function validate_getImgRatio(){
	var refImage = document.getElementById('image');
	return refImage.clientWidth/refImage.naturalWidth;
}

function validate_nextImage(){
	if(validate_imgPathList.length>0){
		validate_wipeLegend();
		validate_wipeAreas();
		validate_removeImage();
		//tools_freeImage(validate_imgPathList[validate_imgPathListIndex].id);
		validate_imgPathListIndex++;
		if(validate_imgPathListIndex<validate_imgPathList.length)
			validate_addImage();
		else{
			console.log("no more img");
			//document.getElementById("moreButton").style = "DISPLAY: initial;";
			//document.getElementById("RejectButton").style = "DISPLAY: none;";
			//document.getElementById("ValidateButton").style = "DISPLAY: none;";
			validate_loadImages();
		}
	}else{
		validate_loadImages();
	}
}

function validate_removeImage(){
	var refImage = document.getElementById('image');
	if(refImage){
		//refImage.remove();
		refImage.src = "";
	}
}
function validate_wipeLegend(){
	var elements = document.getElementsByClassName("legend");
	while(elements.length>0){
		elements[0].remove();
		
	}
}
function validate_wipeAreas(){
	var areaCanvas = document.getElementById("areaCanvas");
	areaCtx = areaCanvas.getContext("2d");
	areaCtx.clearRect(0, 0, areaCanvas.width, areaCanvas.height);
}

/////////////////////////

function validate_onValidateClicked(){
	console.log("Validate");
	validate_sendData(1);
}

function validate_onRejectClicked(){
	console.log("Reject");
	validate_sendData(0);
}

function validate_sendData(validated){

	var data= {};
	data["dataSrc"]=validate_srcId;
	data["validateType"]=validated;
	data["updated"]= validate_imgPathList[validate_imgPathListIndex].updated_at;
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;
	// Validate or reject areas
	var url = site.uri.public + '/segment/validate/evaluate';
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
	        modal.style.display = "block";
	        console.log("Sorry image outdated, go to the next one");
	    }
	);
}
// Get the modal
var modal = document.getElementById('myModal');

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
    validate_nextImage();
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
        validate_nextImage();
    }
}
function validate_onModalClicked(){
	modal.style.display = "none";
	validate_nextImage();
}
function validate_onNextClicked(){
	validate_nextImage();
}
function validate_onMoreClicked(){
	validate_loadImages();
	console.log("Load more");
}
window.onbeforeunload = function(e) {
	for(var i = validate_imgPathListIndex; i < validate_imgPathList.length; ++i){
		//tools_freeImage (validate_imgPathList[i].id);
		console.log("Free " +validate_imgPathList[i].id);
	}
};
function updateNbrAreas(){
	//var elements = document.getElementsByClassName("rectangle");
	document.getElementById('value1').innerHTML = validate_AreasList.length;
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