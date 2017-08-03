var label_imgPathList=[];
var label_imgPathListIndex = 0;
var label_imgPath = "img/light/";
var label_phpPath = "../../php/";
var label_srcName = 0;
var label_pagemode = "";//"label", "homepage",//"segmentation"
var label_rectanglesList = [];
//if(document.getElementById("openButton"))
//	document.getElementById("openButton").style = "DISPLAY: none;";


var mouse = {
		x: 0,
		y: 0,
		// coord in image ref
		startX: 0,
		startY: 0
	};

	var element = null;
	var minSize = 10;


function label_initpage(pagemode){
	label_pagemode = pagemode;
	label_loadCategories();
	label_getCountEdit();
}
////////////GET IMG FROM SERVER//////
function label_loadImages(){
	// Fetch and render the images
	label_freeRemainingImages();
	label_imgPathList = [];
	
	var combo4 = document.getElementById("combo4");
	var imgSet;
	imgSet = combo4.options[combo4.selectedIndex].value;
	
	
	var data= {};
	data["setID"]=imgSet;
	if(label_pagemode == "label"){
		if(reworkMode)
			var url = site.uri.public + '/images/myedit';
		else
			var url = site.uri.public + '/images/clean';
	}
	else if (label_pagemode == "homepage"){
		var url = site.uri.public + '/images/cleanNA';
	}
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
			}
			else label_imgPathList = [];
			label_imgPathListIndex = 0;
			if(label_imgPathList.length == 0){document.getElementById('imgCounter').style = "DISPLAY: initial;max-width: 100px;";}
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
	var url = site.uri.public + '/areas/byIds';
	$.ajax({
	  type: "GET",
	  url: url,
	  data: data
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
				label_rectanglesList = data;
			}
			else label_rectanglesList = [];
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
		document.getElementById('image').src = imgToAdd;//$('#preview').html("<img id='image' unselectable='on' onresize='"label_onImgResize()"' src='"+imgToAdd+"' />")
		label_initSelection();
		document.getElementById('imgCounter').style = "DISPLAY: none;";//"Image "+(label_imgPathListIndex+1)+" / "+label_imgPathList.length;
		document.getElementById("moreButton").style = "DISPLAY: none;";
		document.getElementById("nextButton").style = "DISPLAY: initial;";
		updateNbrAreas();

		///Load complete
		function loaded() {
		  //draw rect
		  label_drawRects(label_srcName);//initSelection();
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
	}
	else
		label_removeImage();
}

function label_drawRects(idImage){
	for(var i = 0; i < label_rectanglesList.length; ++i){
		reviewedRect = label_rectanglesList[i];

		if(parseInt(reviewedRect.source) == idImage){
			var refImage = document.getElementById('image');
			var initRatio = label_getImgRatio();
			var element = document.createElement('div');
			element.className = 'rectangle';
			var str = reviewedRect.category.Category;
			var type = reviewedRect.rectType;
			var color = reviewedRect.category.Color;
			element.rectType = type;
			element.rectSetRatio = 1;
			element.rectSetLeft = parseInt(reviewedRect.rectLeft);
			element.rectSetTop = parseInt(reviewedRect.rectTop);
			element.rectSetWidth = reviewedRect.rectRight - reviewedRect.rectLeft;
			element.rectSetHeight = reviewedRect.rectBottom - reviewedRect.rectTop;
			element.style.left = /*(leftImage+*/parseInt(reviewedRect.rectLeft)*initRatio/*)*/ + 'px';
			element.style.top = /*(topImage+*/parseInt(reviewedRect.rectTop)*initRatio/*)*/ + 'px';
			element.style.border= "3px solid "+color;
			element.style.color= color;
			var canvas = document.createElement('canvas');
			canvas.style.pointerEvents = "none";
	  		canvas.style.margin = "-5px";//(anchorScale-3)/2 +3
			var text = document.createElement('div');
			var t = document.createTextNode(str);
			text.className = 'rectangleText';
			if (!$('#labelShowSwitch').is(":checked")){text.style.display = "none";}
			text.appendChild(t);
			element.appendChild(text);
			text.style.pointerEvents = "none";
			text.style.position = "absolute";
			element.appendChild(canvas);
			element.style.width = (reviewedRect.rectRight - reviewedRect.rectLeft)*initRatio + 'px';
			element.style.height = (reviewedRect.rectBottom - reviewedRect.rectTop)*initRatio + 'px';
			(document.getElementById('preview')).appendChild(element);
			adaptText(element);
			updateNbrAreas();
			drawAnchor(element);
		
			element.onmousedown = function(e){
				onElementDownHandler(e,$(this));
			}
			element.onmousemove = function(e){
				onElementMoveHandler(e,$(this));
			}
			$(".rectangle").on('touchstart',function(e,data) {
				e.preventDefault();  
				onElementDownHandler(e,$(this));
			});
			$(".rectangle").on('touchmove',function(e,data) {
				e.preventDefault();  
				onMoveHandler(e); 
			});
			$(".rectangle").on('touchend',function(e,data) { 
				e.preventDefault();  
				resizeMode = false;
				elemMoveMode = false;
				element = null;
			});
		}
	}
}

function label_onImgResize(){
	console.log("resize");
}
window.addEventListener("resize", function(){
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	//document.getElementById('value1').innerHTML = "left "+refPreview.parentElement.offsetLeft+" top "+refPreview.parentElement.offsetTop;
	//document.getElementById('value3').innerHTML = "left "+refPreview.offsetLeft+" top "+refPreview.offsetTop;
	var elements = document.getElementsByClassName("rectangle");
	var ratio = label_getImgRatio();
	if(elements.length>0){
		for (var i = 0; i < elements.length; ++i) {
			console.log("Resize rect ratio :"+ratio);
			elements[i].style.left = parseFloat(elements[i].rectSetLeft*ratio/elements[i].rectSetRatio) + 'px';
			elements[i].style.top = parseFloat(elements[i].rectSetTop*ratio/elements[i].rectSetRatio) + 'px';
			elements[i].style.width = parseFloat(elements[i].rectSetWidth*ratio/elements[i].rectSetRatio) + 'px';
			elements[i].style.height = parseFloat(elements[i].rectSetHeight*ratio/elements[i].rectSetRatio) + 'px';
			drawAnchor(elements[i]);
		}

	}

   if(window.innerWidth < 768){
      console.log('narrow');

   }
   else{
       console.log('wide');
   }
});

function label_getImgRatio(){
	var refImage = document.getElementById('image');
	return refImage.clientWidth/refImage.naturalWidth;
	//document.getElementById('value2').innerHTML = "ratio "+refImage.clientWidth/refImage.naturalWidth;
	//document.getElementById('value3').innerHTML = "ratio "+refImage.clientHeight/refImage.naturalHeight;
}

function label_nextImage(){
	if(label_imgPathList.length>0){
		label_removeImage();
		if(label_pagemode == "label"){
			tools_freeImage(label_imgPathList[label_imgPathListIndex].id);
		}
		else if (label_pagemode == "homepage"){
			tools_freeImageNA(label_imgPathList[label_imgPathListIndex].id);
		}
		label_imgPathListIndex++;
		if(label_imgPathListIndex<label_imgPathList.length)
			label_addImage();
		else{
			//console.log("no more img");
			//document.getElementById("moreButton").style = "DISPLAY: initial;";
			//document.getElementById("nextButton").style = "DISPLAY: none;";
			label_loadImages();
		}
	}else{
		label_loadImages();
	}
}

function label_removeImage(){
	label_wipeRectangle();
	var refImage = document.getElementById('image');
	if(refImage){
		//refImage.remove();
		refImage.src = "";
	}
}
function label_wipeRectangle(){
	var elements = document.getElementsByClassName("rectangle");
	while(elements.length>0){
		elements[0].remove();
		
	}
	updateNbrAreas();
}

function updateNbrAreas(){
	var elements = document.getElementsByClassName("rectangle");
	document.getElementById('value1').innerHTML = elements.length;
}	

/////////////////////////


////  COMBO    //////////////////
//creating categories 

var label_catId = [];
var label_catText=[];
var label_catColor= [];



function label_loadCategories(){
	// Fetch and render the categories
	if(label_pagemode == "label"){
		var url = site.uri.public + '/category/all2';
	}
	else if (label_pagemode == "homepage"){
		var url = site.uri.public + '/category/allNA';
	}
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
					label_catId[i] = parseInt(res[i].id);
					label_catText[i] = res[i].Category;
					label_catColor[i] = res[i].Color;
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
	if(label_pagemode == "label"){
		var url = site.uri.public + '/api/sets/mysets';
	}
	else if (label_pagemode == "homepage"){
		var url = site.uri.public + '/api/sets/mysetsNA';
	}
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
				label_set[i]['group'] = res[i].group;
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
		//console.log("creating "+category)
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


//////  Column 3 button management  ////////

// init
var drawMode =true;
var eraseMode = false;
var moveMode = false;
var resizeMode = false;
var elemMoveMode = false;
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





////  Draw square management  /////////////////////////////////////////////////

function label_initSelection(){
	label_initDraw();
};
	

	

function label_initDraw() {
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
	if(!moveMode)
		e.preventDefault();
	onClickHandler(e);
	onDownHandler(e);
}
function label_onTouchEnd(e){
	if(!moveMode)
		e.preventDefault();
	onUpHandler(e);
}
function label_onTouchCancel(e){
	e.preventDefault();
}
function label_onTouchMove(e){
	if(!moveMode)
		e.preventDefault();
	onMoveHandler(e);
}
///////////////////////////////////////////

function onMoveHandler(e) {
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	
	if(drawMode== true){
		if(resizeMode)
			resizeElement(e, element);
		else if(elemMoveMode){
			moveElement(e, element);
		}
		else if (element !== null) {

		
			if(e.type == "mousemove"){
				var pageX = e.pageX + refPreview.scrollLeft;
				var pageY = e.pageY;
			}
			else if(e.type == "touchmove" && e.targetTouches){
				var pageX = e.targetTouches[0].pageX + refPreview.scrollLeft;
				var pageY = e.targetTouches[0].pageY;
			}
			else{
				var pageX = 0;
				var pageY = 0;
				console.log("no event recognized");
				return;
			}
			//document.getElementById('value4').innerHTML = "pageX "+parseInt(pageX)+" pageY "+parseInt(pageY);
			//document.getElementById('value6').innerHTML = "mX "+mouse.startX+" mY "+mouse.startX;
			//document.getElementById('value7').innerHTML = "e.X "+e.offsetX+" e.Y "+e.offsetY;
			////////   X   /////////////////////////
			var leftImage = refPreview.parentElement.offsetLeft;//distance between window border and the left of the image
			var currentDistanceX = pageX - (leftImage + mouse.startX);//distance between initial mouse and mouse
			var tmpWidth = Math.abs(currentDistanceX);//width of rect
			var normalLeft = mouse.startX;//when going to the right, distance between left of image and the border of rect (initial pos of mouse)
			var reverseLeft = pageX - leftImage;//when going to the left, distance between left of image and the mouse (current mouse)
			if(currentDistanceX > 0)
				var reverseX = false; else var reverseX = true;
			
			if (reverseX == false && ((normalLeft + tmpWidth) <= refImage.width))//going right, offset + rect.width can't be bigger than img.width
				element.style.width = tmpWidth + 'px';
			if (reverseX == true && reverseLeft >= 0)//going left, when going under 0 we stop expending the box
				element.style.width = tmpWidth + 'px';
													//On some case we don't actualise width
			
			if(reverseX == true && reverseLeft >= 0)//Rectangle is moved if going on left, when going out of image we stop moiving rect ( width expand is also stopped)
				element.style.left = reverseLeft + 'px';
			if(reverseX == false)
				element.style.left = normalLeft + 'px';//when going right, starting point is always the same 
			
			
			////////   Y   /////////////////////////
			var topImage = refPreview.parentElement.offsetTop ;
			var currentDistanceY = pageY - (topImage + mouse.startY);
			var tmpHeight = Math.abs(currentDistanceY);
			var normalTop = mouse.startY;
			var reverseTop = pageY - topImage;
			if(currentDistanceY > 0)
				var reverseY = false; else var reverseY = true;
			
			if (reverseY == false && ((normalTop + tmpHeight) <= refImage.height))
				element.style.height = tmpHeight + 'px';
			if (reverseY == true && reverseTop >= 0)
				element.style.height = tmpHeight + 'px';
			//Rectangle is moved if going on top
			if(reverseY == true && reverseTop >= 0)
				element.style.top = reverseTop + 'px';
			if(reverseY == false)
				element.style.top = normalTop + 'px';
			//Mimimum size of rect
			if(parseFloat(element.style.width) >= minSize && parseFloat(element.style.height) >= minSize)
				refPreview.appendChild(element);
			else if(element.parentElement)
				refPreview.removeChild(element);
			//document.getElementById('value5').innerHTML = "left "+element.style.left+" top "+element.style.top;
			adaptText(element);
			drawAnchor(element);
		}
	}
}

function resizeElement(e, element){
	console.log("try resize");
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	if(e.type == "mousemove"){
		var pageX = e.pageX + refPreview.scrollLeft;
		var pageY = e.pageY;
	}
	else if(e.type == "touchmove"){
		var pageX = e.originalEvent.targetTouches[0].pageX + refPreview.scrollLeft;
		var pageY = e.originalEvent.targetTouches[0].pageY;
	}
	else{
		console.log("no event recognized");
		return;
	}
	var leftImage = refPreview.parentElement.offsetLeft;//distance between window border and the left of the image
	var normalLeft = mouse.startX;
	var currentDistanceX = pageX - (leftImage + normalLeft);//distance between initial mouse and mouse
	var reverseLeft = pageX - leftImage;//when going to the left, distance between left of image and the mouse (current mouse)
	var tmpWidth = Math.abs(currentDistanceX);//width of rect
	var reverseWidth = mouse.startWidth - currentDistanceX;

	////////   Y   /////////////////////////
	var topImage = refPreview.parentElement.offsetTop ;
	var normalTop = mouse.startY;
	var currentDistanceY = pageY - (topImage + normalTop);
	var reverseTop = pageY - topImage;
	var tmpHeight = Math.abs(currentDistanceY);
	var reverseHeight = mouse.startHeight - currentDistanceY;
	
	if(resizeMode == "Left" || resizeMode == "Top-left" || resizeMode == "Bottom-left"){
		if (reverseLeft >= 0 && currentDistanceX < mouse.startWidth)//going left, when going under 0 we stop expending the box
			element.style.width = reverseWidth + 'px';
		if(reverseLeft >= 0 && currentDistanceX < mouse.startWidth)//Rectangle is moved if going on left, when going out of image we stop moiving rect ( width expand is also stopped)
			element.style.left = reverseLeft + 'px';
	}
	if (resizeMode == "Top" || resizeMode == "Top-left" || resizeMode == "Top-right"){
		if (reverseTop >= 0 && currentDistanceY < mouse.startHeight)
			element.style.height = reverseHeight + 'px';
		if(reverseTop >= 0 && currentDistanceY < mouse.startHeight)
			element.style.top = reverseTop + 'px';
	}
	if ( (  resizeMode == "Right" || resizeMode == "Bottom-right" || resizeMode == "Top-right"  ) && currentDistanceX > 0 ){
		if ((normalLeft + tmpWidth) <= refImage.width)//going right, offset + rect.width can't be bigger than img.width
			element.style.width = tmpWidth + 'px';
	}
	if ( (  resizeMode == "Bottom" || resizeMode == "Bottom-right" || resizeMode == "Bottom-left"  ) && currentDistanceY > 0 ){
		if ((normalTop + tmpHeight) <= refImage.height)
				element.style.height = tmpHeight + 'px';
	}
	//Mimimum size of rect
	if(parseFloat(element.style.width) >= minSize && parseFloat(element.style.height) >= minSize)
		refPreview.appendChild(element);
	else if(element.parentElement)
		refPreview.removeChild(element);
	adaptText(element);
	drawAnchor(element);
}
function moveElement(e, element){
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	if(e.type == "mousemove"){
		var pageX = e.pageX;
		var pageY = e.pageY;
	}
	else if(e.type == "touchmove"){
		var pageX = e.originalEvent.targetTouches[0].pageX;
		var pageY = e.originalEvent.targetTouches[0].pageY;
	}
	else{
		console.log("no event recognized");
		return;
	}
	////////   X   /////////////////////////
	var leftImage = refPreview.parentElement.offsetLeft;//distance between window border and the left of the image
	var currentDistanceX = pageX - (leftImage + mouse.startX);//distance between initial mouse and mouse
	
	if(currentDistanceX >= 0 && (currentDistanceX + element.offsetWidth) <= refImage.width)//Rectangle is moved if going on left, when going out of image we stop moiving rect ( width expand is also stopped)
		element.style.left = currentDistanceX + 'px';
	
	////////   Y   /////////////////////////
	var topImage = refPreview.parentElement.offsetTop ;
	var currentDistanceY = pageY - (topImage + mouse.startY);
	if(currentDistanceY >= 0 && (currentDistanceY + element.offsetHeight) <= refImage.height)
		element.style.top = currentDistanceY + 'px';

	adaptText(element);
	drawAnchor(element);
}


function onClickHandler(e) {
	if(eraseMode== true){
		//console.log("go pour effacement"+e.target);
		if(e.target.className == "rectangle"){
			//console.log("effacement");
			e.target.remove();
		}
		else if(e.target.className == "rectangleText"){
			e.target.parentElement.remove();
		}
	}
	else{
		e.target.parentElement.appendChild(e.target);
	}
	updateNbrAreas();
}

function onDownHandler(e) {
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
	if(drawMode== true && element == null && refImage !== null){
		if(e.type == "mousedown"){
			var pageX = e.pageX + refPreview.scrollLeft;
			var pageY = e.pageY;
		}
		else if(e.type == "touchstart"){
			var pageX = e.targetTouches[0].pageX + refPreview.scrollLeft;
			var pageY = e.targetTouches[0].pageY;
		}
		else{
			var pageX = 0;
			var pageY = 0;
			console.log("no event recognized");
		}
		//In image coord (relative to img)
		//mouse.startX = (pageX - refImage.offsetLeft);
		//mouse.startY = (pageY - refImage.offsetTop);
		mouse.startX = (pageX - refPreview.parentElement.offsetLeft);
		mouse.startY = (pageY - refPreview.parentElement.offsetTop);
		element = document.createElement('div');
		element.className = 'rectangle';
		var combo = document.getElementById("combo");
		var str = combo.options[combo.selectedIndex].text;
		var type = combo.options[combo.selectedIndex].value;
		var color = label_catColor[label_catId.indexOf(parseInt(type))];
		element.rectType = type;
		//Don't use it as we do not display rect when too small (it's a dot at the start)
		//element.style.left = pageX + 'px';
		//element.style.top = pageY + 'px';
		element.style.border= "3px solid "+color;
		element.style.color= color;
		var canvas = document.createElement('canvas');
		canvas.style.pointerEvents = "none";
  		canvas.style.margin = "-5px";//(anchorScale-3)/2 +3
		var text = document.createElement('div');
		var t = document.createTextNode(str);
		text.className = 'rectangleText';
		if (!$('#labelShowSwitch').is(":checked")){text.style.display = "none";}
		text.appendChild(t);
		text.style.pointerEvents = "none";
		text.style.position = "absolute";
		element.appendChild(text);
		element.appendChild(canvas);
		//refPreview.appendChild(element);
		//adaptText();
		element.style.width = 0;
		element.style.height = 0;
		//refPreview.style.cursor = "crosshair";

		element.onmousedown = function(e){
			onElementDownHandler(e,$(this));
		}
		element.onmousemove = function(e){
			onElementMoveHandler(e,$(this));
		}
		

	}
}

function onUpHandler(e) {
	var canvas = document.getElementById('preview');
	if(drawMode== true){
		if(element != null){
			element.rectSetLeft = element.offsetLeft;
			element.rectSetTop = element.offsetTop;
			element.rectSetWidth = element.offsetWidth;
			element.rectSetHeight = element.offsetHeight;
			element.rectSetRatio = label_getImgRatio();
			element = null;
		}
		canvas.style.cursor = "default";

		$(".rectangle").on('touchstart',function(e,data) {
			e.preventDefault();  
			onElementDownHandler(e,$(this));
		});
		$(".rectangle").on('touchmove',function(e,data) {
			e.preventDefault();
			onMoveHandler(e); 
		});
		$(".rectangle").on('touchend',function(e,data) {
			onClickHandler(e);
			e.preventDefault(); 
			resizeMode = false;
			elemMoveMode = false;
			element = null;
		});
	}
	resizeMode = false;
	elemMoveMode = false;
	updateNbrAreas();
}
function onElementDownHandler(e,selection){
	if(drawMode){
		var refPreview = document.getElementById('preview');
		if(e.type == "mousedown"){
			var offsetX = e.offsetX;
			var offsetY = e.offsetY;
			var reactiveLength = parseInt(selection.css('borderLeftWidth'));
			var pageX = e.pageX;
			var pageY = e.pageY;
		}
		else if(e.type == "touchstart"){
			var offsetX = e.originalEvent.targetTouches[0].pageX - e.originalEvent.currentTarget.offsetLeft - refPreview.parentElement.offsetLeft  + refPreview.scrollLeft;
			var offsetY = e.originalEvent.targetTouches[0].pageY - e.originalEvent.currentTarget.offsetTop - refPreview.parentElement.offsetTop;
			var reactiveLength = parseInt(selection.css('borderLeftWidth')) +10;
			var pageX = e.originalEvent.targetTouches[0].pageX;
			var pageY = e.originalEvent.targetTouches[0].pageY;
		}
		else{
			var offsetX = 0;
			var offsetY = 0;
			console.log("no event recognized");
			return;
		}
		element = e.target;

		if( (  offsetX <= reactiveLength  ) && (  offsetY <= reactiveLength  ) ){// Top-left
	    	resizeMode = "Top-left";
	    }
	    else if( (  offsetX >= (e.target.clientWidth - reactiveLength)  ) && (  offsetY >= (e.target.clientHeight - reactiveLength)  ) ){// Bottom-right
	    	resizeMode = "Bottom-right";
		}

		else if( (  offsetX <= reactiveLength  ) && (  offsetY >= (e.target.clientHeight - reactiveLength)  ) ){// Bottom-left
	    	resizeMode = "Bottom-left";
	    }
	    else if( (  offsetX >= (e.target.clientWidth - reactiveLength)  ) && (  offsetY <= reactiveLength  ) ){// Top-right
	    	resizeMode = "Top-right";
		}

		else if(  offsetX <= reactiveLength){
	       	resizeMode = "Left";
	       	mouse.startWidth = element.offsetWidth;
	    }
	    else if(  offsetY <= reactiveLength){
       		resizeMode = "Top";
       		mouse.startHeight = element.offsetHeight;
	    }
	    else if(  offsetX >= (e.target.clientWidth - reactiveLength) ){
	       	resizeMode = "Right";
	    }
	    else if(  offsetY >= (e.target.clientHeight - reactiveLength) ){
	       	resizeMode = "Bottom";
	    }
	    else{
	    	elemMoveMode = true;
	    }
	    if(elemMoveMode){
	    	mouse.startX = pageX - element.offsetLeft - refPreview.parentElement.offsetLeft;
	    	mouse.startY = pageY - element.offsetTop - refPreview.parentElement.offsetTop;
	    }
	    else{
	    	mouse.startX = element.offsetLeft;
			mouse.startY = element.offsetTop;
			mouse.startWidth = element.offsetWidth;
			mouse.startHeight = element.offsetHeight;
	    }
	}
}
function onElementMoveHandler(e,selection){
	var reactiveLength = parseInt(selection.css('borderLeftWidth'));
	if(eraseMode){
		e.target.style.cursor = "default";
	}

	else if( (  e.offsetX <= reactiveLength  ) && (  e.offsetY <= reactiveLength  ) ){// Top-left
    	e.target.style.cursor = "nwse-resize";
    }
    else if( (  e.offsetX >= (e.target.clientWidth - reactiveLength)  ) && (  e.offsetY >= (e.target.clientHeight - reactiveLength)  ) ){// Bottom-right
    	e.target.style.cursor = "nwse-resize";
	}

	else if( (  e.offsetX <= reactiveLength  ) && (  e.offsetY >= (e.target.clientHeight - reactiveLength)  ) ){// Top-right
    	e.target.style.cursor = "nesw-resize";
    }
    else if( (  e.offsetX >= (e.target.clientWidth - reactiveLength)  ) && (  e.offsetY <= reactiveLength  ) ){// Bottom-left
    	e.target.style.cursor = "nesw-resize";
	}

    else if(e.offsetX <= reactiveLength || e.offsetX >= (e.target.clientWidth - reactiveLength) ){// Left & Right
		e.target.style.cursor = "ew-resize";
    }
    else if(e.offsetY <= reactiveLength || e.offsetY >= (e.target.clientHeight - reactiveLength) ){// Top & Bottom
		e.target.style.cursor = "ns-resize";
    }
    
    else
    	e.target.style.cursor = "all-scroll";
}
function adaptText(element){
	////  X //////
	var refImage = document.getElementById('image');
	var textWidth = element.childNodes[0].scrollWidth;
	var leftImage = refImage.offsetLeft - 0;
	if((parseFloat(element.style.left) + textWidth) >= (leftImage+refImage.width)){
		element.childNodes[0].style.left = -textWidth + 'px';
	}
	else{
		element.childNodes[0].style.left = 0 + 'px';
	}
	////  Y //////
	var refImage = document.getElementById('image');
	var textHeight = element.childNodes[0].scrollHeight;
	var topImage = refImage.offsetTop;
	if((parseFloat(element.style.top) + textHeight) >= (topImage+refImage.height)){
		element.childNodes[0].style.top = -textHeight + 'px';
	}
	else{
		element.childNodes[0].style.top = 0 + 'px';
	}
}
function drawAnchor(element){
	var canvas = element.children[1];
	var anchorScale = 7;//In px
	canvas.width = element.offsetWidth+(anchorScale-3);
	canvas.height = element.offsetHeight+(anchorScale-3);

	var ctx=canvas.getContext("2d");
	ctx.fillStyle = 'white';
	//Corner
	ctx.fillRect( 0, 0, anchorScale, anchorScale );
	ctx.fillRect( canvas.width-anchorScale, 0, anchorScale, anchorScale );
	ctx.fillRect( 0, canvas.height-anchorScale, anchorScale, anchorScale );
	ctx.fillRect( canvas.width-anchorScale, canvas.height-anchorScale, anchorScale, anchorScale );
	//Mid-Border
	ctx.fillRect( (  canvas.width-anchorScale  )/2, 0, anchorScale, anchorScale );
	ctx.fillRect( (  canvas.width-anchorScale  )/2, canvas.height-anchorScale, anchorScale, anchorScale );
	ctx.fillRect( 0, (  canvas.height-anchorScale  )/2, anchorScale, anchorScale );
	ctx.fillRect( canvas.width-anchorScale, (  canvas.height-anchorScale  )/2, anchorScale, anchorScale );
	//Middle
	ctx.fillRect( (  canvas.width-anchorScale  )/2, (  canvas.height-anchorScale  )/2, anchorScale, anchorScale );
	ctx.fillStyle = 'black';
	//Corner
	ctx.rect( 0, 0, anchorScale, anchorScale );
	ctx.rect( canvas.width-anchorScale, 0, anchorScale, anchorScale );
	ctx.rect( 0, canvas.height-anchorScale, anchorScale, anchorScale );
	ctx.rect( canvas.width-anchorScale, canvas.height-anchorScale, anchorScale, anchorScale );
	//Mid-Border
	ctx.rect( (  canvas.width-anchorScale  )/2, 0, anchorScale, anchorScale );
	ctx.rect( (  canvas.width-anchorScale  )/2, canvas.height-anchorScale, anchorScale, anchorScale );
	ctx.rect( 0, (  canvas.height-anchorScale  )/2, anchorScale, anchorScale );
	ctx.rect( canvas.width-anchorScale, (  canvas.height-anchorScale  )/2, anchorScale, anchorScale );
	//Middle
	ctx.rect( (  canvas.width-anchorScale  )/2, (  canvas.height-anchorScale  )/2, anchorScale, anchorScale );
	ctx.stroke();
}
	
	
	





function label_onNextClicked(){
	var elements = document.getElementsByClassName("rectangle");
	if(elements.length>0){
		console.log("prepare request");
		var data= {};
		data["rects"]=[];
		for (var i = 0; i < elements.length; ++i) {
			var rectLeft = elements[i].rectSetLeft/elements[i].rectSetRatio;
			var rectTop = elements[i].rectSetTop/elements[i].rectSetRatio;
			var rectRight = (elements[i].rectSetLeft + elements[i].rectSetWidth)/elements[i].rectSetRatio;
			var rectBottom = (elements[i].rectSetTop + elements[i].rectSetHeight)/elements[i].rectSetRatio;
			var rectType = elements[i].rectType;
			data["rects"][i]={type:rectType,rectLeft:rectLeft,rectTop:rectTop,rectRight:rectRight,rectBottom:rectBottom}
		}
		data["dataSrc"]=label_srcName;
		console.log(data);
		data[site.csrf.keys.name] = site.csrf.name;
		data[site.csrf.keys.value] = site.csrf.value;

		// submit rects
		if(label_pagemode == "label"){
			var url = site.uri.public + '/bbox/annotate';
		}
		else if (label_pagemode == "homepage"){
			var url = site.uri.public + '/bbox/annotateNA';
		}
		$.ajax({
		  type: "POST",
		  url: url,
		  data: data
		})
		.then(
		    // Fetch successful
		    function (data) {
				label_nextImage();
				label_getCountEdit();
		    },
		    // Fetch failed
		    function (data) {
		        
		    }
		);
	}
	else{
		label_nextImage();
	}
}
function label_getCountEdit(){
	if(label_pagemode == "homepage") return;
	var url = site.uri.public + '/images/myeditCount';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
			label_updateEditNbr(data);
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function label_updateEditNbr(data){
	document.getElementById('editValue').innerHTML = data.pendingImg;
}

function label_onMoreClicked(){
	label_loadImages();
}
window.onbeforeunload = function(e) {
	label_freeRemainingImages();
};
function label_freeRemainingImages(){
	for(var i = label_imgPathListIndex; i < label_imgPathList.length; ++i){
		if(label_pagemode == "label"){
			tools_freeImage (label_imgPathList[i].id);
		}
		else if (label_pagemode == "homepage"){
			tools_freeImageNA (label_imgPathList[i].id);
		}
	}
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

var reworkMode = false;

function label_onReworkClicked(element){
	if (element.checked)
		reworkMode = true;
	else
		reworkMode = false;
	label_freeRemainingImages();
	label_loadImages();
}
function label_onViewLabelClicked(element){
	if (element.checked){
		label_showBboxLabel(true);
	}
	else{
		label_showBboxLabel(false);
	}
}
function label_showBboxLabel(bool){
	if(bool){
		$(".rectangleText").each(function(){
			this.style.display = "initial"
		});
	}else{
		$(".rectangleText").each(function(){
			this.style.display = "none"
		});
	}
}



