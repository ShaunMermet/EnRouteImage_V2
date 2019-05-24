var label_imgPathList=[];
var label_imgPathListIndex = 0;
var label_imgPath = site.uri.public +"/efs/img/segmentation/light/";
var label_phpPath = "../../php/";
var label_srcName = 0;
var label_AreasList = [];

var mainContainer = {};

//var pako = require('pako');


////////////GET IMG FROM SERVER//////

function label_loadImages(idImage){
	label_wipeGrid();
	label_wipeSegment();
	label_imgPathList = [];
	var combo4 = document.getElementById("combo4");
	var imgSet;
	imgSet = combo4.options[combo4.selectedIndex].value;
	
	var data= {};
	data["setID"]=imgSet;
	var comboNbrSeg = document.getElementById("comboNbrSeg");
	var nbrSegments = comboNbrSeg.options[comboNbrSeg.selectedIndex].value;
	data["nbrSegments"]= nbrSegments;
	var comboCompact = document.getElementById("comboCompact");
	var compactness = comboCompact.options[comboCompact.selectedIndex].value;
	data["compactness"] = compactness
	// Fetch and render the images
	var url = site.uri.public + '/segImages/clean';
	if(typeof idImage !== 'undefined'){
		data["imgID"]=idImage;
	}
	$('#preview .stdLoaderButton').show();
	$('#nextButton').prop('disabled', true);
	$('#reloadButton').prop('disabled', true);
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (datas) {
	    	$('#preview .stdLoaderButton').hide();
	    	$('#nextButton').prop('disabled', false);
	    	$('#reloadButton').prop('disabled', false);
	    	if(datas!=""){
				//var res = JSON.parse(datas);
				label_imgPathList = datas;//res;
				for (data of datas) {
				    /*var dec = window.atob(data.slic.data);
					function atos(arr) {
					    for (var i=0, l=arr.length, s='', c; c = arr[i++];)
					        s += String.fromCharCode(
					            c > 0xdf && c < 0xf0 && i < l-1
					                ? (c & 0xf) << 12 | (arr[i++] & 0x3f) << 6 | arr[i++] & 0x3f
					            : c > 0x7f && i < l
					                ? (c & 0x1f) << 6 | arr[i++] & 0x3f
					            : c
					        );
					    return s
					}
					result = atos(pako.ungzip(dec));*/
					result = data.slic.data;
					ar1d = result.split(" ");
					result = null;
					ar2d = [];
					for(j = 0 ; j < data.slic.y; j++){
						ar2d[j] = [];
					}
					for(i = 0 ; i < ar1d.length; i++){
						row = Math.floor(i/data.slic.x);
						col = i%data.slic.x;
						ar2d[row][col]=parseInt(ar1d[i]);
					}
					ar1d = null;
					data.slic.udata = ar2d;
					ar2d =[];
					tag =[];
					for (k = 0 ; k < data.slic.nbrSeg ; k++) {
						tag[k]=-1;
					}
					data.slic.tag = tag;
					tag = [];
				}
				//console.log(datas);
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
	label_addImage();
	return;
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
			//console.log("wide image");
			img.style.height = "100%";
			img.style.width = "";
			columnFour.style.width= "calc(100% - 110px)";
			imgContainer[0].style.height = "calc(100vh - 168px)";
		}else{
			//console.log("classic image");
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
		//document.getElementById("nextButton").style = "DISPLAY: initial;";
		//document.getElementById("reloadButton").style = "DISPLAY: initial;"

		

		function loaded() {
		  label_drawSlic(label_srcName);
		  label_drawAreas(label_srcName);//initSelection();
		  label_drawLegend(label_srcName);
		  img.removeEventListener('load', loaded);
		  img.removeEventListener('error', error);
		  updateNbrAreas();
		  redrawAll();
		}
		function error() {
			img.removeEventListener('load', loaded);
		  	img.removeEventListener('error', error);
		  	updateNbrAreas();
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

function label_drawSlic(label_srcName){
	data = label_imgPathList[label_imgPathListIndex];
	gridarray = data.slic.udata;
	//console.log("draw grid");
	//console.log(gridarray);
	var refImage = document.getElementById('image');
	var slicCanvas = document.getElementById("slicCanvas");
	slicCanvas.width = parseInt(data.slic.x);
	slicCanvas.height = parseInt(data.slic.y);
	var segmentCanvas = document.getElementById("segmentCanvas");
	segmentCanvas.width = parseInt(data.slic.x);
	segmentCanvas.height = parseInt(data.slic.y);
	//function drawPixel(x,y){
		var slicCtx = slicCanvas.getContext("2d");
		slicCtx.fillStyle = "#FFFF00";
		//slicCtx.fillRect( x, y, 1, 1 );
		
	//}
	var imageData = slicCtx.getImageData(0, 0, data.naturalWidth, data.naturalHeight);
	var dataPxl = imageData.data;
	for(j = 0; j < data.naturalHeight -1 ; j++){
		for(i = 0 ; i < data.naturalWidth -1; i++){
			if(gridarray[j][i] != gridarray[j][(i+1)]  || gridarray[j][i] != gridarray[(j+1)][i]){
				//slicCtx.fillRect( i, j, 1, 1 );
				var index = (j * data.naturalWidth + i) * 4;

		        //var value = x * y & 0xff;

		        dataPxl[index]   = 255;    // red
		        dataPxl[++index] = 255;    // green
		        dataPxl[++index] = 0;    // blue
		        dataPxl[++index] = 255;      // alpha
			}
		}
	}
	slicCtx.putImageData(imageData, 0, 0);
}

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
function redrawSlic(){

	var initRatio = label_getImgRatio();

	data = label_imgPathList[label_imgPathListIndex];
	if(typeof data === 'undefined'){
		return;
	}
	gridarray = data.slic.udata;
	//console.log("draw grid");
	//console.log(gridarray);
	var refImage = document.getElementById('image');
	var slicCanvas = document.getElementById("slicCanvas");
	slicCanvas.style.width = refImage.width+'px';
	slicCanvas.style.height = refImage.height+'px';
	var segmentCanvas = document.getElementById("segmentCanvas");
	segmentCanvas.style.width = refImage.width+'px';
	segmentCanvas.style.height = refImage.height+'px';
	/*function drawPixel(x,y){
		slicCtx = slicCanvas.getContext("2d");
		slicCtx.fillStyle = "#FFFF00";
		slicCtx.fillRect( x*initRatio, y*initRatio, 1, 1 );
	}
	for(j = 0; j < data.slic.y -1 ; j++){
		for(i = 0 ; i < data.slic.x -1; i++){
			if(gridarray[j][i] != gridarray[j][(i+1)]  || gridarray[j][i] != gridarray[(j+1)][i]){
				//drawPixel(i, j);
			}
		}
	}*/
	//gridarray = [];
}
function redrawArea(){
	var refImage = document.getElementById('image');
	var areaCanvas = document.getElementById("areaCanvas");
	areaCanvas.width = refImage.width;
	areaCanvas.height = refImage.height;
	var lineCanvas = document.getElementById("lineCanvas");
	lineCanvas.width = refImage.width;
	lineCanvas.height = refImage.height;
	var slicCanvas = document.getElementById("slicCanvas");
	//slicCanvas.width = refImage.width;
	//slicCanvas.height = refImage.height;
	var initRatio = label_getImgRatio();
	for(var i = 0; i < dataAreas.length; ++i){
		reviewedArea = dataAreas[i];
		//if(parseInt(reviewedArea.source) == idImage){

			areaCtx = areaCanvas.getContext("2d");
			areaCtx.lineJoin = "round";
			areaCtx.beginPath();
			var coordList = JSON.parse( reviewedArea.points );
			areaCtx.moveTo(coordList[0][0]*initRatio, coordList[0][1]*initRatio);

			for(var j = 1; j < coordList.length; ++j){
				areaCtx.lineTo(coordList[j][0]*initRatio, coordList[j][1]*initRatio);
			}
			
			var color = reviewedArea.Color;
			areaCtx.globalAlpha=0.5;
	 		areaCtx.fillStyle = color;//"#ff0000";
	 		areaCtx.lineWidth  = 3;
	 		areaCtx.strokeStyle = "#ffffff";
	 		areaCtx.closePath();
			areaCtx.fill();
			areaCtx.globalAlpha=1;
			areaCtx.stroke();

			currentPoly = {};
			currentPoly.type = reviewedArea.type;
			currentPoly.points = reviewedArea.points;
		//}
	}
}
function label_drawLegend(idImage){
	var legendDiv = document.getElementById("legend");
	legendDiv.innerHTML = "";
	var legend = {};


	for(var i = 0; i < dataAreas.length; ++i){
		var areaType = dataAreas[i].type;
		legend[areaType] = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == areaType})];;
	}

	data = label_imgPathList[label_imgPathListIndex];
	if(!data){
		return;
	}
	segdata = data.slic.tag;
	for(var i = 0; i < segdata.length; ++i){
		var segmentCatID = segdata[i];
		if(segmentCatID != -1){
			legend[segmentCatID] = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == segmentCatID})];;
		}
	}

	//console.log(legend);
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
function label_wipeLegend(){
	var legendDiv = document.getElementById("legend");
	legendDiv.innerHTML = "";
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
	label_wipeGrid();
	label_wipeSegment();
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
	currentPoly= {};
	updateNbrAreas();

	

}
function label_wipeGrid(){
	var slicCanvas = document.getElementById("slicCanvas");
	slicCtx = slicCanvas.getContext("2d");
	slicCtx.clearRect(0, 0, slicCanvas.width, slicCanvas.height);
}
function label_wipeSegment(){
	var segmentCanvas = document.getElementById("segmentCanvas");
	slicCtx = segmentCanvas.getContext("2d");
	slicCtx.clearRect(0, 0, segmentCanvas.width, segmentCanvas.height);
	label_wipeLegend();
	// Reset all values
	var data = label_imgPathList[label_imgPathListIndex];
	if(data){
		segments = data.slic.tag;
		for(var i = 0; i < segments.length; i++){
			segments[i] = -1;
		}
		console.log(segments);
	}
}

/////////////////////////


////  COMBO    //////////////////
//creating categories 


label_initComboNbrSeg();
label_initComboCompact();
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
	        mainContainer.catData = data;
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
			label_updateComboCat();
			label_loadImages();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function label_updateComboCat(){
	function appendToCombo(text,data){
		$("#combo").append("<option value=\""+data+"\">"+text+"</option>");
	}
	emptyCombo($("#combo")[0]);
	var setCombo = $("#combo4")[0];
	var setSelectedID = setCombo.value;
	if(setSelectedID == "") setSelectedID = 1;
	for (i = 0; i < mainContainer.catData.length; i++) {
		var cat = mainContainer.catData[i];
		if(cat.set_id == setSelectedID){
			appendToCombo(cat.Category,cat.id);
		}
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
	$("#combo4").select2({width: '100px',placeholder: 'Select a set'})
	.on("change", function(e) {
		label_updateComboCat();
    	label_loadImages();
    });
}
function emptyCombo(comboElem){
	while (comboElem.childElementCount != 0){
		comboElem.removeChild(comboElem.firstChild);
	}
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
	label_wipeSegment();
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

	var canvas = document.getElementById('imageDiv');
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
	var initRatio = label_getImgRatio();
	var refPreview = document.getElementById('preview');
	if(eraseMode== true){
		if(e.target.className == "rectangle"){
			e.target.remove();
		}
		else if(e.target.className == "rectangleText"){
			e.target.parentElement.remove();
		}
	}else{
		data = label_imgPathList[label_imgPathListIndex];
		gridarray = data.slic.udata;
		if(e.type == "click"){
			x = Math.round(e.offsetX/initRatio);
			y = Math.round(e.offsetY/initRatio);
		}
		else if(e.type == "touchstart"){
			x = Math.round((e.targetTouches[0].pageX - areaCanvas.offsetParent.offsetParent.offsetLeft + refPreview.scrollLeft)/initRatio);
			y = Math.round((e.targetTouches[0].pageY - areaCanvas.offsetParent.offsetParent.offsetTop + refPreview.scrollTop)/initRatio);
		}
		else{
			console.log("no event recognized");
		}

		
		segmentId = gridarray[y][x];

		//console.log("mouse click");
		//console.log(e);
		//console.log(x+" "+y+" "+segmentId);
		//canvas OK
		var segmentCanvas = document.getElementById("segmentCanvas");

		//segment OK 

		//color get color
		var combo = document.getElementById("combo");
		var type = combo.options[combo.selectedIndex].value;
		var selectedCat = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == type})];
		var color = selectedCat.Color;
		var colorId = parseInt(type);
		segdata = data.slic.tag;
		if(segdata[segmentId] == colorId){
			segdata[segmentId] = -1;
			color = "null";
		}else{
			segdata[segmentId] = colorId;
		}
		console.log(segdata);

		// launch function

		label_segFillCollor(segmentCanvas, segmentId, color, data);

		label_drawLegend();
	}
}

function onDownHandler(e) {
	return;
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
	return;
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
	return;
	console.log("mouse up");
		if(painting){
			var combo = document.getElementById("combo");
			var type = combo.options[combo.selectedIndex].value;
			var selectedCat = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == type})];
			var color = selectedCat.Color;
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
				currentPoly.Color = color;
				dataAreas.push(currentPoly);
			}
		}
	painting = false;
	updateNbrAreas();
}

//Fill the segment provided with the provided color
//If segment already colored, uncolor it
function label_segFillCollor(canva, segment, color, data){
	gridarray = data.slic.udata;
	
	var initRatio = label_getImgRatio();
	canvaCtx = canva.getContext("2d");
		
	function drawPixel(x,y,color,canva){
		canvaCtx.clearRect(x, y, 1, 1);
		if(color == "null"){
			
		}else{
			canvaCtx.fillStyle = color;
			canvaCtx.fillRect( x, y, 1, 1 );
		}
	}
	for(j = 0; j < gridarray.length ; j++){
		for(i = 0 ; i < gridarray[j].length; i++){
			//console.log(gridarray[j][i]+" "+segment)
			if(gridarray[j][i] == segment){
				drawPixel(i, j,color, canva);
			}
		}
	}
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
	var imageData = label_imgPathList[label_imgPathListIndex];
	var segments = imageData.slic.tag;
	//imageData.slic.udata = [];
	var gridChanged = false;
	for(var i = 0; i < segments.length ; i++){
		if(segments[i] != -1){
			gridChanged = true;
			break;
		}
	}
	var stringedTag = JSON.stringify(imageData.slic.tag);
	
	var lzwCompressed = JSON.stringify(LZW.compress(imageData.slic.data));//testStr
	var lzwTagCompressed = JSON.stringify(LZW.compress(stringedTag));
    
	if(gridChanged){
		console.log("prepare request");
		var data= {};
		data["areas"]=dataAreas;
		data["slicStr"] = lzwCompressed;//compressedTest;//compressedStr;
		data["segInfo"] = lzwTagCompressed;
		data["dataSrc"]=label_srcName;
		data["updated"]= label_imgPathList[label_imgPathListIndex].updated_at;
		var comboNbrSeg = document.getElementById("comboNbrSeg");
		var nbrSegments = comboNbrSeg.options[comboNbrSeg.selectedIndex].value;
		data["nbrSegments"]= nbrSegments;
		var comboCompact = document.getElementById("comboCompact");
		var compactness = comboCompact.options[comboCompact.selectedIndex].value;
		data["compactness"] = compactness
		//console.log(data);
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
		    	if (data.status == 408){
		    		console.log("Sorry image outdated, go to the next one");
		    	}else if (data.status == 489){
		    		console.log("Sorry image too big");
		    	}else {
		    		console.log("Sorry an error occured at the server level");
		    	}
		        
		    }
		);
	}
	else{
		label_nextImage();
	}
}

function hexToString (hex) {
    var string = '';
    for (var i = 0; i < hex.length; i ++) {
      string += hex.charCodeAt(i).toString(16)
    }
    return string;
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

function redrawAll(){
	redrawArea();
	redrawSlic();
}



// please note, 
// that IE11 now returns undefined again for window.chrome
// and new Opera 30 outputs true for window.chrome
// but needs to check if window.opr is not undefined
// and new IE Edge outputs to true now for window.chrome
// and if not iOS Chrome check
// so use the below updated condition
var isChromium = window.chrome;
var winNav = window.navigator;
var vendorName = winNav.vendor;
var isOpera = typeof window.opr !== "undefined";
var isIEedge = winNav.userAgent.indexOf("Edge") > -1;
var isIOSChrome = winNav.userAgent.match("CriOS");

if (isIOSChrome) {
   // is Google Chrome on IOS
  //chrome fix
	var globImage = document.getElementById('image');
	new ResizeObserver(redrawAll).observe(globImage);
} else if(
  isChromium !== null &&
  typeof isChromium !== "undefined" &&
  vendorName === "Google Inc." &&
  isOpera === false &&
  isIEedge === false
) {
   // is Google Chrome
   //chrome fix
	var globImage = document.getElementById('image');
	new ResizeObserver(redrawAll).observe(globImage);
} else { 
   // not Google Chrome 
   window.addEventListener("resize", function(){
		redrawAll();
	});
}


///////// SWITCHS /////////////

function label_onViewSegmentClicked(element){

	if (element.checked){
		label_showSegSegment(true);
	}
	else{
		label_showSegSegment(false);
	}
}
function label_showSegSegment(bool){
	var segmentCanvas = document.getElementById("segmentCanvas");
	if(bool){
		segmentCanvas.style.display = "initial";
	}else{
		segmentCanvas.style.display = "none";
	}
}

function label_onViewGridClicked(element){

	if (element.checked){
		label_showSegGrid(true);
	}
	else{
		label_showSegGrid(false);
	}
}
function label_showSegGrid(bool){
	var slicCanvas = document.getElementById("slicCanvas");
	if(bool){
		slicCanvas.style.display = "initial";
	}else{
		slicCanvas.style.display = "none";
	}
}

function label_onViewImgClicked(element){

	if (element.checked){
		label_showSegImg(true);
	}
	else{
		label_showSegImg(false);
	}
}
function label_showSegImg(bool){
	var lineCanvas = document.getElementById("lineCanvas");
	//var image = document.getElementById("image");
	if(bool){
		lineCanvas.style.background = "initial";
		//image.style.display = "initial";
	}else{
		lineCanvas.style.background = "black";
		//image.style.display = "none";
	}
}
function label_initComboNbrSeg(){
	$('#comboNbrSeg').append("<option value=100>100</option>");
	$('#comboNbrSeg').append("<option value=250>250</option>");
	$('#comboNbrSeg').append("<option value=500>500</option>");
	$('#comboNbrSeg').append("<option value=1000>1000</option>");
	$('#comboNbrSeg').val(250);
	$('#comboNbrSeg').select2({tags: true});
}
function label_initComboCompact(){
	$('#comboCompact').append("<option value=0.01>0.01</option>");
	$('#comboCompact').append("<option value=0.1>0.1</option>");
	$('#comboCompact').append("<option value=1>1</option>");
	$('#comboCompact').append("<option value=10>10</option>");
	$('#comboCompact').append("<option value=100>100</option>");
	$('#comboCompact').val(10);
	$('#comboCompact').select2({tags: true});
}
function label_onReloadClicked(){
	var data = label_imgPathList[label_imgPathListIndex];
	label_removeImage();
	label_loadImages(data.id);
}

//LZW Compression/Decompression for Strings
var LZW = {
    compress: function (uncompressed) {
        "use strict";
        // Build the dictionary.
        var i,
            dictionary = {},
            c,
            wc,
            w = "",
            result = [],
            dictSize = 256;
        for (i = 0; i < 256; i += 1) {
            dictionary[String.fromCharCode(i)] = i;
        }
 
        for (i = 0; i < uncompressed.length; i += 1) {
            c = uncompressed.charAt(i);
            wc = w + c;
            //Do not use dictionary[wc] because javascript arrays 
            //will return values for array['pop'], array['push'] etc
           // if (dictionary[wc]) {
            if (dictionary.hasOwnProperty(wc)) {
                w = wc;
            } else {
                result.push(dictionary[w]);
                // Add wc to the dictionary.
                dictionary[wc] = dictSize++;
                w = String(c);
            }
        }
 
        // Output the code for w.
        if (w !== "") {
            result.push(dictionary[w]);
        }
        return result;
    },
 
 
    decompress: function (compressed) {
        "use strict";
        // Build the dictionary.
        var i,
            dictionary = [],
            w,
            result,
            k,
            entry = "",
            dictSize = 256;
        for (i = 0; i < 256; i += 1) {
            dictionary[i] = String.fromCharCode(i);
        }
 
        w = String.fromCharCode(compressed[0]);
        result = w;
        for (i = 1; i < compressed.length; i += 1) {
            k = compressed[i];
            if (dictionary[k]) {
                entry = dictionary[k];
            } else {
                if (k === dictSize) {
                    entry = w + w.charAt(0);
                } else {
                    return null;
                }
            }
 
            result += entry;
 
            // Add w+entry[0] to the dictionary.
            dictionary[dictSize++] = w + entry.charAt(0);
 
            w = entry;
        }
        return result;
    }
}/*, // For Test Purposes
    comp = LZW.compress("TOBEORNOTTOBEORTOBEORNOT"),
    decomp = LZW.decompress(comp);
document.write(comp + '<br>' + decomp)*/;