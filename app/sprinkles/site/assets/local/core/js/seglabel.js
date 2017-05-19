var label_imgPathList=[];
var label_imgPathListIndex = 0;
var label_imgPath = "../img/segmentation/";
var label_phpPath = "../../php/";
var label_srcName = 0;



////////////GET IMG FROM SERVER//////
label_loadImages();
function label_loadImages(){
	// Fetch and render the images
	var url = site.uri.public + '/segImages/clean';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
				//var res = JSON.parse(data);
				label_imgPathList = data;//res;
			}
			else label_imgPathList = [];
			label_imgPathListIndex = 0;
			label_addImage();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function label_addImage(){
	if(label_imgPathList.length>0){
		label_srcName = label_imgPathList[label_imgPathListIndex].id;
		var imgName = label_imgPathList[label_imgPathListIndex].path;
		var imgToAdd = label_imgPath+imgName;
		document.getElementById('image').src = imgToAdd;
		label_initSelection();
		document.getElementById('imgCounter').innerHTML = "";//"Image "+(label_imgPathListIndex+1)+" / "+label_imgPathList.length;
		document.getElementById("moreButton").style = "DISPLAY: none;";
		document.getElementById("nextButton").style = "DISPLAY: initial;";
	}
}


function label_onImgLoaded(){
	console.log("loaded");
	var refImage = document.getElementById('image');
	var areaCanvas = document.getElementById("areaCanvas");
	areaCanvas.width = refImage.width;
	areaCanvas.height = refImage.height;
	var lineCanvas = document.getElementById("lineCanvas");
	lineCanvas.width = refImage.width;
	lineCanvas.height = refImage.height;
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
		label_wipeAreas();
		label_removeImage();
		//tools_freeImage(label_imgPathList[label_imgPathListIndex].id);
		label_imgPathListIndex++;
		if(label_imgPathListIndex<label_imgPathList.length)
			label_addImage();
		else{
			//document.getElementById("moreButton").style = "DISPLAY: initial;";
			//document.getElementById("nextButton").style = "DISPLAY: none;";
			label_loadImages();
		}
	}
}

function label_removeImage(){
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
				}
				label_initCombo();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function label_initCombo(){
	for (i = 0; i < label_catId.length; i++) {
		appendToCombo(label_catText[i],label_catId[i]);
	}


	function appendToCombo(category,type){
		$("#combo").append("<option value=\""+type+"\">"+category+"</option>");
	}


	$(".js-basic-single").select2({ width: '100px' });
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

    var dataAreas = [];
    var currentPoly= {};

function updateNbrAreas(){
	document.getElementById('value1').innerHTML = "Areas : "+dataAreas.length;
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
			lastX = (e.pageX - areaCanvas.offsetParent.offsetParent.offsetLeft)/cnvRatio;
			lastY = (e.pageY - areaCanvas.offsetParent.offsetParent.offsetTop)/cnvRatio;
		}
		else if(e.type == "touchstart"){
			lastX = (e.targetTouches[0].pageX - areaCanvas.offsetParent.offsetParent.offsetLeft)/cnvRatio;
			lastY = (e.targetTouches[0].pageY - areaCanvas.offsetParent.offsetParent.offsetTop)/cnvRatio;
		}
		else{
			console.log("no event recognized");
		}

		areaCtx.beginPath();
		areaCtx.moveTo(lastX, lastY);

		var combo = document.getElementById("combo");
		var type = combo.options[combo.selectedIndex].value;
		
		currentPoly = {};
		currentPoly.type = type;
		currentPoly.points = [];
		var ratio = label_getImgRatio();
		currentPoly.points.push([lastX/ratio,lastY/ratio]);
		
	}
}

function onMoveHandler(e) {

	if (painting) {
        if(e.type == "mousemove"){
    		mouseX = e.pageX - areaCanvas.offsetParent.offsetParent.offsetLeft;
        	mouseY = e.pageY - areaCanvas.offsetParent.offsetParent.offsetTop;
		}
		else if(e.type == "touchmove"){
			mouseX = e.targetTouches[0].pageX - areaCanvas.offsetParent.offsetParent.offsetLeft;
        	mouseY = e.targetTouches[0].pageY - areaCanvas.offsetParent.offsetParent.offsetTop;
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
	filler.style.height = (top-filler.parentElement.offsetTop)+ 'px';
};




