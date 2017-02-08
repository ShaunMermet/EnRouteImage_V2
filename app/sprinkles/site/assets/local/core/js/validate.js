var validate_imgPathList=[];
var validate_imgPathListIndex = 0;
var validate_imgPath = "../img/";
var validate_phpPath = "../../php/";
var validate_srcId = 0;

var validate_imageLoaded = false;
var validate_rectanglesLoaded = false;
var validate_rectanglesList = [];
var validate_currentRectangle = null;

////////////GET IMG FROM SERVER//////
validate_loadImages();
validate_loadRects();
function validate_onload(){
	validate_loadImages();
	validate_loadRects();
}

function validate_handlerLoadsDone(){
	if(validate_imageLoaded && validate_rectanglesLoaded){
		if (validate_imgPathList.length>0)
			validate_addImage();
		validate_imageLoaded = false;
		validate_rectanglesLoaded = false;
	}
}
function validate_loadRects(){
	var http_req = new XMLHttpRequest();
	var url = validate_phpPath+"get_rects_tobeVerified.php";

	http_req.open("GET", url, true);

	http_req.onreadystatechange = function() {
		if (http_req.readyState == 4 && http_req.status == 200) {
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			console.log("reponse rect done");
			console.log(http_req.responseText);
			if(http_req.responseText!=""){
				var res = JSON.parse(http_req.responseText);
				validate_rectanglesList = res;
			}
			else validate_rectanglesList = [];
			validate_rectanglesLoaded = true;
			validate_handlerLoadsDone();
		}
	};
	http_req.send();
}
function validate_loadImages(){
	var http_req = new XMLHttpRequest();
	var url = validate_phpPath+"get_img_tobeVerified.php";

	http_req.open("GET", url, true);

	http_req.onreadystatechange = function() {
		if (http_req.readyState == 4 && http_req.status == 200) {
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			// Action to be performed when the document is read;
			console.log("select img done");
			console.log(http_req.responseText);
			if(http_req.responseText!=""){
				var res = JSON.parse(http_req.responseText);
				validate_imgPathList = res;
			}
			else validate_imgPathList = [];
			validate_imgPathListIndex = 0;
			validate_imageLoaded = true;
			validate_handlerLoadsDone();//addImage();
		}
	};
	http_req.send();
}

function validate_addImage(){
	validate_srcId = validate_imgPathList[validate_imgPathListIndex].id;
	var imgName = validate_imgPathList[validate_imgPathListIndex].path;
	var imgToAdd = validate_imgPath+imgName;
	document.getElementById('image').src = imgToAdd;//$('#preview').html("<img id='image' unselectable='on' src='"+imgToAdd+"' />")
	
	var img = document.getElementById('image');

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
	
	document.getElementById('imgCounter').innerHTML = "Image "+(validate_imgPathListIndex+1)+" of "+validate_imgPathList.length;
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
			var str = reviewedRect.Category;
			var type = reviewedRect.rectType;
			var color = reviewedRect.Color;
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
		validate_freeImage(validate_imgPathList[validate_imgPathListIndex].id);
		validate_imgPathListIndex++;
		if(validate_imgPathListIndex<validate_imgPathList.length)
			validate_addImage();
		else{
			console.log("no more img");
			document.getElementById("moreButton").style = "DISPLAY: initial;";
			document.getElementById("RejectButton").style = "DISPLAY: none;";
			document.getElementById("ValidateButton").style = "DISPLAY: none;";
		}
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
	validate_sendData(true);
}

function validate_onRejectClicked(){
	console.log("Reject");
	//validate_nextImage();
	validate_sendData(false);
}

function validate_sendData(validated){
	var data= {};
	data["dataSrc"]=validate_srcId;
	data["validated"]=validated;
	console.log(data);
	
	////////////////////// POST  //////////////
	var http_req = new XMLHttpRequest();
	var url = validate_phpPath+"post_check.php";

	http_req.open("POST", url, true);

	//Send the proper header information along with the request
	http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	
	http_req.onreadystatechange = function() {//Call a function when the state changes.
		if(http_req.readyState == 4 && http_req.status == 200) {
			//alert(http_req.responseText);
			if(http_req.responseText == "session_closed")
				window.location.replace("http://"+location.hostname+"/login.php?location="+location.pathname);
			console.log(http_req.responseText);
			validate_nextImage();
		}
	}
	var json = JSON.stringify(data);
	http_req.send("data=" +json);
	//////////////////////////////////////////////
}
function validate_freeImage (idImage){
		var data= {};
		data["dataSrc"]=idImage;
		////////////////////// POST  //////////////
		var http_req = new XMLHttpRequest();
		var url = validate_phpPath+"post_freeImage.php";

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
function validate_onMoreClicked(){
	validate_loadImages();
	validate_loadRects();
	console.log("Load more");
}
window.onbeforeunload = function(e) {
	for(var i = validate_imgPathListIndex; i < validate_imgPathList.length; ++i){
		validate_freeImage (validate_imgPathList[i].id);
		console.log("Free " +validate_imgPathList[i].id);
	}
};
////////////////////////////////////////////