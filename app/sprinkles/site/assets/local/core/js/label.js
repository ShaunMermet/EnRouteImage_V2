var label_imgPathList=[];
var label_imgPathListIndex = 0;
var label_imgPath = "../img/";
var label_phpPath = "../../php/";
var label_srcName = 0;
if(document.getElementById("openButton"))
	document.getElementById("openButton").style = "DISPLAY: none;";



////////////GET IMG FROM SERVER//////
label_loadImages();
function label_loadImages(){
	var http_req = new XMLHttpRequest();
	var url = label_phpPath+"get_img_clean.php";

	http_req.open("GET", url, true);

	http_req.onreadystatechange = function() {
		if (http_req.readyState == 4 && http_req.status == 200) {
			// Action to be performed when the document is read;
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			//console.log("select img done");
			//console.log(http_req.responseText);
			if(http_req.responseText!=""){
				var res = JSON.parse(http_req.responseText);
				label_imgPathList = res;
			}
			else label_imgPathList = [];
			label_imgPathListIndex = 0;
			label_addImage();
		}
	};
	http_req.send();
}

function label_addImage(){
	if(label_imgPathList.length>0){
		label_srcName = label_imgPathList[label_imgPathListIndex].id;
		var imgName = label_imgPathList[label_imgPathListIndex].path;
		var imgToAdd = label_imgPath+imgName;
		document.getElementById('image').src = imgToAdd;//$('#preview').html("<img id='image' unselectable='on' onresize='"label_onImgResize()"' src='"+imgToAdd+"' />")
		label_initSelection();
		document.getElementById('imgCounter').innerHTML = "Image "+(label_imgPathListIndex+1)+" of "+label_imgPathList.length;
		document.getElementById("moreButton").style = "DISPLAY: none;";
		document.getElementById("nextButton").style = "DISPLAY: initial;";
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
		label_wipeRectangle();
		label_removeImage();
		label_freeImage(label_imgPathList[label_imgPathListIndex].id);
		label_imgPathListIndex++;
		if(label_imgPathListIndex<label_imgPathList.length)
			label_addImage();
		else{
			console.log("no more img");
			document.getElementById("moreButton").style = "DISPLAY: initial;";
			document.getElementById("nextButton").style = "DISPLAY: none;";
			//label_loadImages();
		}
	}
}

function label_removeImage(){
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
}

/////////////////////////


////  COMBO    //////////////////
//creating categories 

var label_catId = [];
var label_catText=[];
var label_catColor= [];


label_loadCategories();
function label_loadCategories(){
	var http_req = new XMLHttpRequest();
	var url = label_phpPath+"get_category.php";

	http_req.open("GET", url, true);

	http_req.onreadystatechange = function() {
		if (http_req.readyState == 4 && http_req.status == 200) {
			// Action to be performed when the document is read;
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			var res = JSON.parse(http_req.responseText);
			for(i = 0; i < res.length; i++){
				label_catId[i] = parseInt(res[i].id);
				label_catText[i] = res[i].Category;
				label_catColor[i] = res[i].Color;
			}
			label_initCombo();
		}
	};
	http_req.send();
}

function label_initCombo(){
	for (i = 0; i < label_catId.length; i++) {
		appendToCombo(label_catText[i],label_catId[i]);
	}


	function appendToCombo(category,type){
		//console.log("creating "+category)
		$("#combo").append("<option value=\""+type+"\">"+category+"</option>");
	}


	$(".js-basic-single").select2({ width: '100px' });
}
///////////////////////////////


//////  Column 3 button management  ////////

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





////  Draw square management  /////////////////////////////////////////////////

function label_initSelection(){
	label_initDraw(document.getElementById('preview'));
};
	
function label_initDraw(canvas) {
	var mouse = {
		x: 0,
		y: 0,
		// coord in image ref
		startX: 0,
		startY: 0
	};

	var element = null;
	var minSize = 10;
	

	function onMoveHandler(e) {
		var refImage = document.getElementById('image');
		var refPreview = document.getElementById('preview');
		if(drawMode== true){
			if (element !== null) {

			
				if(e.type == "mousemove"){
					var pageX = e.pageX;
					var pageY = e.pageY;
				}
				else if(e.type == "touchmove"){
					var pageX = e.targetTouches[0].pageX;
					var pageY = e.targetTouches[0].pageY;
				}
				else{
					var pageX = 0;
					var pageY = 0;
					console.log("no event recognized");
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
					canvas.appendChild(element);
				else if(element.parentElement)
					canvas.removeChild(element);
				//document.getElementById('value5').innerHTML = "left "+element.style.left+" top "+element.style.top;
				adaptText();
			}
		}
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
	}
	
	function onDownHandler(e) {
		var refImage = document.getElementById('image');
		var refPreview = document.getElementById('preview');
		if(drawMode== true && element == null && refImage !== null){
			if(e.type == "mousedown"){
				var pageX = e.pageX;
				var pageY = e.pageY;
			}
			else if(e.type == "touchstart"){
				var pageX = e.targetTouches[0].pageX;
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
			var color = label_catColor[label_catId.indexOf(parseInt(type))]
			element.rectType = type;
			//Don't use it as we do not display rect when too small (it's a dot at the start)
			//element.style.left = pageX + 'px';
			//element.style.top = pageY + 'px';
			element.style.border= "3px solid "+color;
			element.style.color= color;
			var text = document.createElement('div');
			var t = document.createTextNode(str);
			text.className = 'rectangleText';
			text.appendChild(t);
			element.appendChild(text);
			//canvas.appendChild(element);
			//adaptText();
			element.style.width = 0;
			element.style.height = 0;
			canvas.style.cursor = "crosshair";
			element.onmouseover = function(e){
				//console.log("mouse over");
			}
			element.onmouseout = function(e){
				//console.log("mouse out");
			}
			
		}
	}
	
	function onUpHandler(e) {
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
			//console.log("mouse up");
		}
	}
	function adaptText(){
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
	
	/////  TOUCH  ////////////////////////////
	
	image.addEventListener("touchstart", handleStart, false);
	image.addEventListener("touchend", handleEnd, false);
	image.addEventListener("touchcancel", handleCancel, false);
	image.addEventListener("touchmove", handleMove, false);
	
	
	function handleStart(e) {
		if(!moveMode)
			e.preventDefault();
		console.log("touchstart");
		onClickHandler(e);
		onDownHandler(e);
	}
	function handleEnd(e) {
		if(!moveMode)
			e.preventDefault();
		console.log("touchend");
		onUpHandler(e);
	}
	function handleCancel(e) {
		e.preventDefault();
		console.log("touchcancel");
	}
	function handleMove(e) {
		if(!moveMode)
			e.preventDefault();
		onMoveHandler(e);
		console.log("touchmove");
	}
	///////////////////////////////////////////

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
		
		////////////////////// POST  //////////////
		var http_req = new XMLHttpRequest();
		var url = label_phpPath+"post_data.php";

		http_req.open("POST", url, true);

		//Send the proper header information along with the request
		http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		
		http_req.onreadystatechange = function() {//Call a function when the state changes.
			if(http_req.readyState == 4 && http_req.status == 200) {
				//alert(http_req.responseText);
				if(http_req.responseText == "session_closed")
					window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
				console.log(http_req.responseText);
				label_nextImage();
			}
		}
		var json = JSON.stringify(data);
		http_req.send("data=" +json);
		//////////////////////////////////////////////
	}
	else{
		label_nextImage();
	}
}
function label_freeImage (idImage){
		var data= {};
		data["dataSrc"]=idImage;
		////////////////////// POST  //////////////
		var http_req = new XMLHttpRequest();
		var url = label_phpPath+"post_freeImage.php";

		http_req.open("POST", url, true);

		//Send the proper header information along with the request
		http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		
		http_req.onreadystatechange = function() {//Call a function when the state changes.
			if(http_req.readyState == 4 && http_req.status == 200) {
				//alert(http_req.responseText);
				if(http_req.responseText == "session_closed")
					window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
				console.log(http_req.responseText);
			}
		}
		var json = JSON.stringify(data);
		http_req.send("data=" +json);
		//////////////////////////////////////////////
}
function label_onMoreClicked(){
	label_loadImages();
	console.log("Load more");
}
window.onbeforeunload = function(e) {
	for(var i = label_imgPathListIndex; i < label_imgPathList.length; ++i){
		label_freeImage (label_imgPathList[i].id);
		console.log("Free " +label_imgPathList[i].id);
	}
};
////////////////////////////////////////////


