var label_imgPathList=[];
var label_imgPathListIndex = 0;
var label_imgPath = "img/segmentation/light/";
var label_phpPath = "../../php/";
var label_srcName = 0;
var label_AreasList = [];


////////////GET IMG FROM SERVER//////

function label_loadImages(){
	label_imgPathList = [];
	var combo4 = document.getElementById("combo4");
	var imgSet;
	imgSet = combo4.options[combo4.selectedIndex].value;
	
	var data= {};
	data["setID"]=imgSet;
	// Fetch and render the images
	var url = site.uri.public + '/segImages/clean';
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
				//var res = JSON.parse(data);
				label_imgPathList = data;//res;
				console.log("Seg images");
				console.log(data);
			}
			else label_imgPathList = [];
			if(label_imgPathList.length == 0){document.getElementById('imgCounter').style = "DISPLAY: initial;max-width: 100px;";}
			label_imgPathListIndex = 0;
			label_loadRects();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function label_loadRects(){
	var data= {};
	data["ids"]=[];
	for(var i = 0; i < label_imgPathList.length; ++i){
		data["ids"].push(label_imgPathList[i].id);
	}
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
				label_AreasList = data;
				console.log("Seg areas");
				console.log(data);
			}
			else label_AreasList = [];
			label_addImage();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function label_addImage(){
	label_removeImage();
	if(label_imgPathList.length>0){
		var nativeWidth = label_imgPathList[label_imgPathListIndex].naturalWidth;
		var nativeHeight = label_imgPathList[label_imgPathListIndex].naturalHeight;
		var img = document.getElementById('image');
		var columnFour = document.getElementById('columnFour');
		var imgContainer = document.getElementsByClassName('labelimg-container');
		if(nativeWidth/nativeHeight > 16/9){
			console.log("wide image");
			img.style.height = "100%";
			img.style.width = "";
			columnFour.style.width= "calc(100% - 110px)";
			imgContainer[0].style.height = "calc(100vh - 168px)";
		}else{
			console.log("classic image");
			img.style.height = "";
			img.style.width = "100%";
			columnFour.style.width= "100%";
			imgContainer[0].style.height = "";
		}
		label_srcName = label_imgPathList[label_imgPathListIndex].id;
		var imgName = label_imgPathList[label_imgPathListIndex].path;
		var imgToAdd = label_imgPath+imgName;
		document.getElementById('image').src = imgToAdd;
		label_initSelection();
		document.getElementById('imgCounter').style = "DISPLAY: none;";//"Image "+(label_imgPathListIndex+1)+" / "+label_imgPathList.length;
		document.getElementById("moreButton").style = "DISPLAY: none;";
		document.getElementById("nextButton").style = "DISPLAY: initial;";

		

		function loaded() {
		  label_drawAreas(label_srcName);//initSelection();
		  label_drawLegend(label_srcName);
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

	}
}

var dataAreas = [];
var currentPoly= {};

function label_drawAreas(idImage){
	var areaCanvas = document.getElementById("areaCanvas");
	var refImage = document.getElementById('image');
	areaCanvas.width = refImage.width;
	areaCanvas.height = refImage.height;
	var lineCanvas = document.getElementById("lineCanvas");
	lineCanvas.width = refImage.width;
	lineCanvas.height = refImage.height;
	var initRatio = label_getImgRatio();
	for(var i = 0; i < label_AreasList.length; ++i){
		reviewedArea = label_AreasList[i];
		if(parseInt(reviewedArea.source) == idImage){

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

			currentPoly = {};
			currentPoly.type = reviewedArea.areaType;
			currentPoly.points = reviewedArea.data;
			dataAreas.push(currentPoly);
		}
	}
	updateNbrAreas();
}

function label_drawLegend(idImage){
	var legendDiv = document.getElementById("legend");
	legendDiv.innerHTML = "";
	var legend = {};
	for(var i = 0; i < dataAreas.length; ++i){
		var areaType = dataAreas[i].type;
		legend[areaType] = label_catObject[areaType];
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

function label_getImgRatio(){
	var refImage = document.getElementById('image');
	return refImage.clientWidth/refImage.naturalWidth;
}
function label_getCnvRatio(){
	var canvas = document.getElementById('areaCanvas');
	return canvas.clientWidth/canvas.width;
}

function label_nextImage(){
	if(label_imgPathList.length>0){
		label_removeImage();
		label_imgPathListIndex++;
		if(label_imgPathListIndex<label_imgPathList.length)
			label_addImage();
		else{
			//document.getElementById("moreButton").style = "DISPLAY: initial;";
			//document.getElementById("nextButton").style = "DISPLAY: none;";
			label_loadImages();
		}
	}else{
		label_loadImages();
	}
}

function label_removeImage(){
	label_wipeAreas();
	var refImage = document.getElementById('image');
	if(refImage){
		refImage.src = "";
	}
}
function label_wipeAreas(){
	var areaCanvas = document.getElementById("areaCanvas");
	areaCtx = areaCanvas.getContext("2d");
	areaCtx.clearRect(0, 0, areaCanvas.width, areaCanvas.height);
	dataAreas = [];
	updateNbrAreas();

}

/////////////////////////


////  COMBO    //////////////////
//creating categories 

var label_catId = [];
var label_catText=[];
var label_catColor= [];
var label_catObject = {};



label_loadCategories();
function label_loadCategories(){
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
				for(i = 0; i < res.length; i++){
					label_catId[i] = parseInt(res[i].id);
					label_catText[i] = res[i].Category;
					label_catColor[i] = res[i].Color;
					label_catObject[label_catId[i]] = {Category :label_catText[i], Color:label_catColor[i]}
				}
				label_initComboCat();
				label_loadset();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function label_loadset(){
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
        	label_set = [];
			for(i = 0; i < res.length; i++){
				label_set[i] = {};
				label_set[i]['id'] = parseInt(res[i].id);
				label_set[i]['name'] = res[i].name;
				label_set[i]['group'] = res[i].group;;
			}
			label_set.sort(function(a, b){return a.id-b.id})
			label_initComboSet();
			label_loadImages();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function label_initComboCat(){
	for (i = 0; i < label_catId.length; i++) {
		appendToCombo(label_catText[i],label_catId[i]);
	}


	function appendToCombo(category,type){
		$("#combo").append("<option value=\""+type+"\">"+category+"</option>");
	}


	$("#combo").select2({ width: '100px'});
}
function label_initComboSet(){
	for (i = 0; i < label_set.length; i++) {
		appendToCombo(label_set[i]['name']+" ("+label_set[i]['group'].name+")",label_set[i]['id']);
	}


	function appendToCombo(text,value){
		$("#combo4").append("<option value=\""+value+"\">"+text+"</option>");
	}


	$("#combo4").select2({width: '100px',placeholder: 'Select a set'});
}
///////////////////////////////


//////  mode button management  ////////

// init
var drawMode =true;
var eraseMode = false;
var moveMode = false;
label_updateButtons();

if(document.getElementById("moveButton"))
	document.getElementById("moveButton").style = "DISPLAY: none;";

function label_onEraseClicked(){
		drawMode =false;
		eraseMode = true;
		moveMode = false;
		label_updateButtons();
};
function label_onDrawClicked(){
		drawMode =true;
		eraseMode = false;
		moveMode = false;
		label_updateButtons();
};
function label_onMoveClicked(){
		drawMode =false;
		eraseMode = false;
		moveMode = true;
		label_updateButtons();
};

function label_updateButtons(){
	if(drawMode)
		$("#drawButton").toggleClass("selected", true);
	else
		$("#drawButton").toggleClass("selected", false);
	
	if(eraseMode)
		$("#eraseButton").toggleClass("selected", true);
	else
		$("#eraseButton").toggleClass("selected", false);
	if(moveMode)
		$("#moveButton").toggleClass("selected", true);
	else
		$("#moveButton").toggleClass("selected", false);
}
///////////////////////////
function label_onResetClicked(){
	label_wipeAreas();
}




////  Draw area management  /////////////////////////////////////////////////

function label_initSelection(){
	label_initDraw();
};

var mouse = {
		x: 0,
		y: 0,
		// coord in image ref
		startX: 0,
		startY: 0
	};

var element = null;
var minSize = 10;

var refImage = document.getElementById('image');
var areaCanvas = document.getElementById("areaCanvas");
var lineCanvas = document.getElementById("lineCanvas");
areaCtx = areaCanvas.getContext("2d"),
lineCtx = lineCanvas.getContext("2d"),
areaCtx.lineJoin = "round";
lineCtx.lineJoin = "round";
painting = false,
lastX = 0,
lastY = 0;



function updateNbrAreas(){
	document.getElementById('value1').innerHTML = dataAreas.length;
	label_drawLegend(label_srcName);
}	
function label_initDraw(canvas) {

	var canvas = document.getElementById('preview');
	/////  CLICK  ///////////////////////////
	document.onmousemove = function (e) {
		onMoveHandler(e);
	}
	canvas.onclick = function(e){
		onClickHandler(e);
	}
	canvas.onmousedown = function(e){
		onDownHandler(e);
	}
	document.onmouseup = function(e){
		onUpHandler(e);
	}
}
/////  TOUCH  ////////////////////////////
function label_onTouchStart(e){
	console.log("touchstart");
	if(!moveMode)
		e.preventDefault();
	onClickHandler(e);
	onDownHandler(e);
}
function label_onTouchEnd(e){
	if(!moveMode)
		e.preventDefault();
	console.log("touchend");
	onUpHandler(e);
}
function label_onTouchCancel(e){
	e.preventDefault();
	console.log("touchcancel");
}
function label_onTouchMove(e){
	if(!moveMode)
		e.preventDefault();
	onMoveHandler(e);
	console.log("touchmove");
}
///////////////////////////////////////////
	
function onClickHandler(e) {
	if(eraseMode== true){
		if(e.target.className == "rectangle"){
			e.target.remove();
		}
		else if(e.target.className == "rectangleText"){
			e.target.parentElement.remove();
		}
	}
}

function onDownHandler(e) {
	console.log("mouse down");
	painting = true;
	lineCtx.strokeStyle = "#ffffff";
	lineCtx.lineWidth = 3;
	var cnvRatio = label_getCnvRatio();

	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	if(drawMode== true && element == null && refImage !== null){
		if(e.type == "mousedown"){
			lastX = (e.pageX - areaCanvas.offsetParent.offsetParent.offsetLeft + refPreview.scrollLeft)/cnvRatio;
			lastY = (e.pageY - areaCanvas.offsetParent.offsetParent.offsetTop + refPreview.scrollTop)/cnvRatio;
		}
		else if(e.type == "touchstart"){
			lastX = (e.targetTouches[0].pageX - areaCanvas.offsetParent.offsetParent.offsetLeft + refPreview.scrollLeft)/cnvRatio;
			lastY = (e.targetTouches[0].pageY - areaCanvas.offsetParent.offsetParent.offsetTop + refPreview.scrollTop)/cnvRatio;
		}
		else{
			console.log("no event recognized");
		}

		areaCtx.beginPath();
		areaCtx.moveTo(lastX, lastY);

		var combo = document.getElementById("combo");
		var type = combo.options[combo.selectedIndex].value;
		
		currentPoly = {};
		currentPoly.type = parseInt(type);
		currentPoly.points = [];
		var ratio = label_getImgRatio();
		currentPoly.points.push([lastX/ratio,lastY/ratio]);
		
	}
}

function onMoveHandler(e) {

	if (painting) {
		var refPreview = document.getElementById('preview');
        if(e.type == "mousemove"){
    		mouseX = e.pageX - areaCanvas.offsetParent.offsetParent.offsetLeft + refPreview.scrollLeft;
        	mouseY = e.pageY - areaCanvas.offsetParent.offsetParent.offsetTop + refPreview.scrollTop;
		}
		else if(e.type == "touchmove"){
			mouseX = e.targetTouches[0].pageX - areaCanvas.offsetParent.offsetParent.offsetLeft + refPreview.scrollLeft;
        	mouseY = e.targetTouches[0].pageY - areaCanvas.offsetParent.offsetParent.offsetTop + refPreview.scrollTop;
		}

        console.log(" move : add x y");
        var ratio = label_getImgRatio();
		var cnvRatio = label_getCnvRatio();			

		if (painting) {
			x = mouseX/cnvRatio;
			y = mouseY/cnvRatio;
	        lineCtx.beginPath();
	        lineCtx.lineJoin = "round";
	        lineCtx.moveTo(lastX, lastY);
	        lineCtx.lineTo(x, y);
	        areaCtx.lineTo(x, y);
	        currentPoly.points.push([x/ratio,y/ratio]);
	        lineCtx.closePath();
	        lineCtx.stroke();
	    }
	    lastX = x; lastY = y;
    }
}

function onUpHandler(e) {
	console.log("mouse up");
		if(painting){
			var combo = document.getElementById("combo");
			var type = combo.options[combo.selectedIndex].value;
			var color = label_catColor[label_catId.indexOf(parseInt(type))];
	 		areaCtx.globalAlpha=0.5;
	 		areaCtx.fillStyle = color;//"#ff0000";
	 		areaCtx.lineWidth  = 3;
	 		areaCtx.strokeStyle = "#ffffff";
	 		areaCtx.closePath();
			areaCtx.fill();
			areaCtx.globalAlpha=1;
			areaCtx.stroke();
			lineCtx.clearRect(0, 0, lineCanvas.width, lineCanvas.height);
			if(currentPoly.points.length > 1){
				currentPoly.points = JSON.stringify(currentPoly.points);
				dataAreas.push(currentPoly);
			}
		}
	painting = false;
	updateNbrAreas();
}



// Get the modal
var modal = document.getElementById('myModal');

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
    label_nextImage();
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
        label_nextImage();
    }
}
function label_onModalClicked(){
	modal.style.display = "none";
	label_nextImage();
}


function label_onNextClicked(){
	var elements = document.getElementsByClassName("rectangle");
	if(dataAreas.length>0){
		console.log("prepare request");
		var data= {};
		data["areas"]=dataAreas;
		data["dataSrc"]=label_srcName;
		data["updated"]= label_imgPathList[label_imgPathListIndex].updated_at;
		console.log(data);
		data[site.csrf.keys.name] = site.csrf.name;
		data[site.csrf.keys.value] = site.csrf.value;

		// submit rects
		var url = site.uri.public + '/segment/annotate';
		$.ajax({
		  type: "POST",
		  url: url,
		  data: data
		})
		.then(
		    // Fetch successful
		    function (data) {
				label_nextImage();
			},
		    // Fetch failed
		    function (data) {
		    	modal.style.display = "block";
		        console.log("Sorry image outdated, go to the next one");
		    }
		);
	}
	else{
		label_nextImage();
	}
}

function label_onMoreClicked(){
	label_loadImages();
	console.log("Load more");
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

///////////////////////////////

