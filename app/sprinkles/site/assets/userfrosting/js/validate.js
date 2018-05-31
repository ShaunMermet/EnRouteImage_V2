var validate_imgPathList=[];
var validate_imgPathListIndex = 0;
var validate_imgPath = site.uri.public +"/efs/img/light/";
var validate_phpPath = "../../php/";
var validate_srcId = 0;

var validate_imageLoaded = false;
var validate_rectanglesLoaded = false;
var validate_rectanglesList = [];
var validate_currentRectangle = null;

var mainContainer = {};

///  COMBO    //////////////////
//creating categories 


function validate_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/category/all';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        mainContainer.catData = data;
			validate_updateComboCat();
			validate_loadset();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_loadset(){
	// Fetch the sets
	
	var url = site.uri.public + '/api/sets/mysets';
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

function validate_updateComboCat(){
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
function validate_initComboSet(){
	for (i = 0; i < validate_set.length; i++) {
		appendToCombo(validate_set[i]['name']+" ("+validate_set[i]['group'].name+")",validate_set[i]['id']);
	}
	function appendToCombo(text,value){
		$("#combo4").append("<option value=\""+value+"\">"+text+"</option>");
	}
	$("#combo4").select2({width: '100px',placeholder: 'Select a set'})
	.on("change", function(e) {
		hideLeftSub();
    	validate_updateComboCat();
    	validate_loadImages();
    });
}
function emptyCombo(comboElem){
	while (comboElem.childElementCount != 0){
		comboElem.removeChild(comboElem.firstChild);
	}
}
///////////////////////////////


////////////GET IMG FROM SERVER//////
validate_loadCategories();

function validate_loadRects(){
	var data= {};
	data["ids"]=[];
	for(var i = 0; i < validate_imgPathList.length; ++i){
		data["ids"].push(validate_imgPathList[i].id);
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
				validate_rectanglesList = data;
			}
			else validate_rectanglesList = [];
			validate_addImage();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}
function validate_loadImages(){
	var combo4 = document.getElementById("combo4");
	var imgSet;
	imgSet = combo4.options[combo4.selectedIndex].value;
	var data= {};
	data["setID"]=imgSet;
	var url = site.uri.public + '/images/annotated';
	$.ajax({
		type: "GET",
	  	url: url,
	  	data: data,
	  	complete: function() {
    	}
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
	    		validate_imgPathList = data;//res;
			}
			else {
				validate_imgPathList = [];
				document.getElementById('imgCounter').style = "DISPLAY: initial;";
				$("#fetchFeedback").text("No images");
				showFiler();
			}
			validate_imgPathListIndex = 0;
			validate_loadRects();
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function validate_addImage(){
	validate_removeImage();
	if(validate_imgPathList.length == 0){
		document.getElementById("moreButton").style = "DISPLAY: initial;";
		document.getElementById("ImgValidButton").style = "DISPLAY: none;";
		document.getElementById("AreaValidButton").style = "DISPLAY: none;";
		return;
	}

	validate_srcId = validate_imgPathList[validate_imgPathListIndex].id;
	
	genericAddImage(validate_imgPathList[validate_imgPathListIndex],validate_imgPath);

	
	label_initSelection();
	document.getElementById('imgCounter').style = "DISPLAY: none;";//"Image "+(validate_imgPathListIndex+1)+" of "+validate_imgPathList.length;
	document.getElementById("moreButton").style = "DISPLAY: none;";
	document.getElementById("ImgValidButton").style = "DISPLAY: initial;";
	document.getElementById("AreaValidButton").style = "DISPLAY: initial;";

	updateNbrAreas();
	updateNbrSelectedAreas();
}
function onImageLoaded(){
	validate_drawRects(validate_srcId);//initSelection();
	validate_rectsApplyState();
	initSelectAll();
	updateNbrAreas();
	updateNbrSelectedAreas();
}
moveCounter = 0;
function rectAttacheEvents(element){
	element.onmousedown = function(e){
		moveCounter = 0;
		onElementDownHandler(e,$(this));
	}
	element.onmousemove = function(e){
		onElementMoveHandler(e,$(this));
	}
	element.onclick = function(e){
		onClickHandler(e);
		if(moveCounter < 10){
			onElementDblClickHandler(e,$(this));
		}
	}
	element.ondblclick = function(e){
		//onElementDblClickHandler(e,$(this));
	}
	element.onmouseover = function(e){
		onElementOverHandler(e,$(this));
	}
	element.onmouseout = function(e){
		onElementOutHandler(e,$(this));
	}
	$(element).on('touchstart',function(e,data) {
		e.preventDefault();
		onElementDownHandler(e,$(this));
		moveCounter = 0;
	});
	$(element).on('touchmove',function(e,data) {
		e.preventDefault();
		onMoveHandler(e);
	});
	$(element).on('touchend',function(e,data) {
		e.preventDefault();
		if(moveCounter < 10){
			onElementDblClickHandler(e,$(this));
		}
		onUpHandler(e);
		onClickHandler(e);
		resizeMode = false;
		elemMoveMode = false;
		element = null;
	});
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
			validate_currentRectangle.rectData = reviewedRect;
			validate_currentRectangle.rectType = type;
			validate_currentRectangle.rectId = reviewedRect.id;
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
			if (!$('#labelShowSwitch').is(":checked")){text.style.display = "none";}
			text.appendChild(t);
			updateNbrAreas();
			validate_currentRectangle.appendChild(text);
			validate_currentRectangle.style.width = (reviewedRect.rectRight - reviewedRect.rectLeft)*initRatio + 'px';
			validate_currentRectangle.style.height = (reviewedRect.rectBottom - reviewedRect.rectTop)*initRatio + 'px';
			(document.getElementById('preview')).appendChild(validate_currentRectangle);
			adaptText(validate_currentRectangle);
			//Anchor param
			var canvas = document.createElement('canvas');
			canvas.style.pointerEvents = "none";
	  		canvas.style.margin = "-5px";//(anchorScale-3)/2 +3
	  		text.style.pointerEvents = "none";
			text.style.position = "absolute";
			validate_currentRectangle.appendChild(canvas);
			drawAnchor(validate_currentRectangle);
			rectAttacheEvents(validate_currentRectangle);
		}
	}
}

function createElement(e){
	var refPreview = document.getElementById('preview');
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
	var selectedCat = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == type})];
	var color = selectedCat.Color;
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
	rectAttacheEvents(element);

	toggleSelection(element, true);

}


function validate_rectsApplyState(){
	var elements = document.getElementsByClassName("rectangle");
	for (var i = 0; i < elements.length; ++i){
		if(elements[i].rectData.state == 3)
			toggleSelection(elements[i], true);
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
			drawAnchor(elements[i]);
		}
	}
});
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
	var anchorScale = 5;//In px
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
	validate_wipeRectangle();
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

function validate_onAreaValidClicked(){
	hideLeftSub();
	console.log("Area valid");
	//validate_nextImage();
	validate_sendData(1);
}

function validate_onImgValidClicked(){
	hideLeftSub();
	console.log("Img valid");
	//validate_nextImage();
	validate_sendData(2);
}

function validate_sendData(validated){

	areaList = getRectInfoToSend();
	var data= {};
	data["dataSrc"]=validate_srcId;
	data["validateType"]=validated;
	data["areas"] = areaList;
	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;
	console.log(data);
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
function getRectInfoToSend(){
	var fullAreaList = document.getElementsByClassName("rectangle");
	var areaList = [];
	for(var i = 0; i < fullAreaList.length; ++i){
		area = {};
		area.id = fullAreaList[i].rectId;
		area.rectType = fullAreaList[i].rectType;
		area.rectLeft = fullAreaList[i].rectSetLeft/fullAreaList[i].rectSetRatio;
		area.rectTop = fullAreaList[i].rectSetTop/fullAreaList[i].rectSetRatio;
		area.rectRight = (fullAreaList[i].rectSetLeft + fullAreaList[i].rectSetWidth)/fullAreaList[i].rectSetRatio;
		area.rectBottom = (fullAreaList[i].rectSetTop + fullAreaList[i].rectSetHeight)/fullAreaList[i].rectSetRatio;
		area.selected = fullAreaList[i].rectSelected;
		areaList.push(area);
	}
	return areaList;
}
function validate_onNextClicked(){
	hideLeftSub();
	validate_nextImage();
}
function validate_onMoreClicked(){
	hideLeftSub();
	validate_loadImages();
	//validate_loadRects();
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
function updateNbrSelectedAreas(){
	var elements = document.getElementsByClassName("rectangle");
	var elementsArray = Array.from(elements);
	var selectedElements = elementsArray.filter(function(elmt){
	    return elmt.rectSelected == 1;
	});
	document.getElementById('value2').innerHTML = selectedElements.length;
}
window.onscroll = function(){
	var top  = window.pageYOffset || document.documentElement.scrollTop;
	var filler = document.getElementById('filler');
	var leftMenu = document.getElementById('leftMenu');
	var intendedHeight = top-filler.parentElement.offsetTop;
	if(intendedHeight < 0) intendedHeight = 0;
	var heightTest = ( intendedHeight + leftMenu.offsetHeight) < filler.parentElement.offsetHeight;
	if( heightTest > 0 )
		filler.style.paddingTop = (intendedHeight)+ 'px';
};
////////////////////////////////////////////
function validate_onViewLabelClicked(element){
	if (element.checked){
		validate_showBboxLabel(true);
	}
	else{
		validate_showBboxLabel(false);
	}
}
function validate_showBboxLabel(bool){
	if(bool){
		$(".rectangleText").each(function(){
			this.style.display = "initial";
		});
	}else{
		$(".rectangleText").each(function(){
			this.style.display = "none";
		});
	}
}
function validate_onShowAreaClicked(element){
	if (element.checked){
		validate_showBboxRect(true);
	}
	else{
		validate_showBboxRect(false);
	}
}
function validate_showBboxRect(bool){
	if(bool){
		$(".rectangle").each(function(){
			if(this.rectSelected == 1){
				this.style.display = "initial";
				drawAnchor(this);
			}
		});
	}else{
		$(".rectangle").each(function(){
			if(this.rectSelected == 1)
				this.style.display = "none";
		});
	}
}
function validate_onShowUAreaClicked(element){
	if (element.checked){
		validate_showBboxURect(true);
	}
	else{
		validate_showBboxURect(false);
	}
}
function validate_showBboxURect(bool){
	if(bool){
		$(".rectangle").each(function(){
			if(this.rectSelected != 1){
				this.style.display = "initial";
				drawAnchor(this);
			}
		});
	}else{
		$(".rectangle").each(function(){
			if(this.rectSelected != 1)
				this.style.display = "none";
		});
	}
}






//////  Mode button management  ////////


// init
var drawMode =true;
var eraseMode = false;

label_updateButtons();

if(document.getElementById("moveButton"))
	document.getElementById("moveButton").style = "DISPLAY: none;";

function label_onEraseClicked(){
		drawMode =false;
		eraseMode = true;
		label_updateButtons();
};
function label_onDrawClicked(){
		drawMode =true;
		eraseMode = false;
		label_updateButtons();
};
function label_onMoveClicked(){
		drawMode =false;
		eraseMode = false;
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
}
///////////////////////////
var selectAll = true;

function initSelectAll(){
	$("#selectAllButton").toggleClass("selected", selectAll);
	var elements = document.getElementsByClassName("rectangle");
	for (var i = 0; i < elements.length; ++i){
		toggleSelection(elements[i], true);
	}
	$("#selectAllButton").toggleClass("selected", true);
	selectAll = true;
}
function valdiate_onSelectAllClicked(){
	selectAllfunction();
}

function selectAllfunction(){
	var elements = document.getElementsByClassName("rectangle");
	for (var i = 0; i < elements.length; ++i){
		toggleSelection(elements[i], !selectAll);
	}
	$("#selectAllButton").toggleClass("selected", !selectAll);
	selectAll = !selectAll;
}



////  Draw square management  /////////////////////////////////
var resizeMode = false;
var elemMoveMode = false;

var mouse = {
	x: 0,
	y: 0,
	// coord in image ref
	startX: 0,
	startY: 0
};
var element = null;
var minSize = 10;

function label_initSelection(){
	label_initDraw();
};
	

function label_initDraw() {
	var canvas = document.getElementById('preview');
	/////  CLICK  ///////////////////////////
	canvas.onmousedown = function(e){
		onDownHandler(e);
	}
	document.onmousemove = function (e) {
		onMoveHandler(e);
	}
	document.onmouseup = function(e){
		onUpHandler(e);
	}
	canvas.onclick = function(e){
		onClickHandler(e);
	}
}

/////  TOUCH  ////////////////////////////
function label_onTouchStart(e){
	e.preventDefault();
	onClickHandler(e);
	onDownHandler(e);
}
function label_onTouchMove(e){
	e.preventDefault();
	onMoveHandler(e);
}
function label_onTouchEnd(e){
	e.preventDefault();
	onUpHandler(e);
}
function label_onTouchCancel(e){
	e.preventDefault();
}



///////////////////////////////////////////
function onDownHandler(e) {
	var refImage = document.getElementById('image');
	if(drawMode== true && element == null && refImage !== null){
		createElement(e);
	}
}
function onMoveHandler(e) {
	moveCounter++;
	if(moveCounter < 10) return;
	if(drawMode== true){
		if(resizeMode)
			resizeElement(e, element);
		else if(elemMoveMode){
			moveElement(e, element);
		}
		else if (element !== null) {
			sizeElement(e, element);
		}
	}
}
function onUpHandler(e) {
	var canvas = document.getElementById('preview');
	if(drawMode== true){
		endCreateElement(e);
	}
	resizeMode = false;
	elemMoveMode = false;
	updateNbrAreas();
	updateNbrSelectedAreas();
}
function onClickHandler(e) {
	if(eraseMode== true){
		//console.log("go pour effacement"+e.target);
		if(e.target.classList.contains("rectangle")){
			//console.log("effacement");
			e.target.remove();
		}
		else if(e.target.classList.contains("rectangleText")){
			e.target.parentElement.remove();
		}
	}
	else{
		e.target.parentElement.appendChild(e.target);
	}
	updateNbrAreas();
	updateNbrSelectedAreas();
}


function sizeElement(e, element){
	var refImage = document.getElementById('image');
	var refPreview = document.getElementById('preview');
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


function endCreateElement(e){
	var canvas = document.getElementById('preview');
	if(element != null){
		element.rectSetLeft = element.offsetLeft;
		element.rectSetTop = element.offsetTop;
		element.rectSetWidth = element.offsetWidth;
		element.rectSetHeight = element.offsetHeight;
		element.rectSetRatio = validate_getImgRatio();
		resizeMode = false;
		elemMoveMode = false;
		element = null;
		canvas.style.cursor = "default";
	}
}


function onElementDownHandler(e,selection){
	if(drawMode){
		initElementAction(e, selection);
	}
}
function onElementMoveHandler(e,selection){
	onOverChangeCursor(e, selection);
}
function onElementDblClickHandler(e,selection){
	var element = selection.context;
	if(!drawMode)return;
	if(element.rectSelected){
		toggleSelection(element, false);
	}
	else{
		toggleSelection(element, true);
	}
}
function onElementOverHandler(e,selection){
	toggleOverClass(selection.context);
}
function onElementOutHandler(e,selection){
	toggleOutClass(selection.context);
}

function initElementAction(e, selection){
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
function onOverChangeCursor(e,selection){
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
function toggleSelection(element, bool){
	if(bool){
		element.rectSelected = 1;
	}else{
		element.rectSelected = 0;
	}
	updateSelectionElement(element);
}
function updateSelectionElement (element){
	if(element.rectSelected){
		if (element.classList.contains("redHovered") || element.classList.contains("greenHovered") || element.classList.contains("simpleHover")){
			element.classList.toggle("redHovered",false);
			element.classList.toggle("greenHovered",true);
		}
		else{
			element.classList.toggle("red",false);
			element.classList.toggle("green",true);
		}
	}
	else{
		if(element.classList.contains("redHovered") || element.classList.contains("greenHovered")){
			//element.classList.toggle("redHovered",true);
			element.classList.toggle("greenHovered",false);
			element.classList.toggle("simpleHover",true);
		}
		else{
			//element.classList.toggle("red",true);
			element.classList.toggle("green",false);
		}
	}
	updateNbrSelectedAreas();
}

function toggleOverClass(element){
    if(element.classList.contains("green")){
    	element.classList.toggle("greenHovered",true);
    	element.classList.toggle("green",false);
    }else if(element.classList.contains("red")){
    	element.classList.toggle("redHovered",true);
    	element.classList.toggle("red",false);
    }else{
    	element.classList.toggle("simpleHover",true);
    }
}
function toggleOutClass(element){
    if(element.classList.contains("greenHovered")){
    	element.classList.toggle("green",true);
    	element.classList.toggle("greenHovered",false);
    }else if(element.classList.contains("redHovered")){
    	element.classList.toggle("red",true);
    	element.classList.toggle("redHovered",false);
    }else{
    	element.classList.toggle("simpleHover",false);
    }
}