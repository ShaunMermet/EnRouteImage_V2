var validate_imgPathList=[];
var validate_imgPathListIndex = 0;
var validate_imgPath = site.uri.public +"/efs/img/segmentation/light/";
var validate_phpPath = "../../php/";
var validate_srcId = 0;

var validate_AreasList = [];
var validate_currentRectangle = null;

var mainContainer = {};

///  COMBO    //////////////////
//creating categories 



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
	    	mainContainer.catData = data;
	    	validate_loadset();
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
			validate_updateComboCat();
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


	$("#combo4").select2({width: '100px',placeholder: 'Select a set'})
	.on("change", function(e) {
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
//validate_loadset();

function validate_loadImages(){
	validate_removeImage();
	// Fetch and render the images
	var combo4 = document.getElementById("combo4");
	var imgSet;
	imgSet = combo4.options[combo4.selectedIndex].value;
	var data= {};
	data["setID"]=imgSet;
	var url = site.uri.public + '/segImages/annotated';
	$('#progressBlock .stdLoaderButton').show();
	document.getElementById('progress').innerHTML = "0%...";
	$('#nextButton').prop('disabled', true);
	$.ajax({
	  xhr: function()
	  {
	    var xhr = new window.XMLHttpRequest();
	    //Upload progress
	    xhr.upload.addEventListener("progress", function(evt){
	      if (evt.lengthComputable) {
	        var percentComplete = evt.loaded / evt.total;
	        //Do something with upload progress
	        console.log(percentComplete);
	      }
	    }, false);
	    //Download progress
	    xhr.addEventListener("progress", function(evt){
	      if (evt.lengthComputable) {
	        var percentComplete = evt.loaded / evt.total;
	        //Do something with download progress
	        console.log(parseInt(percentComplete*100, 10)+"%");
	      }
	    }, false);
	    return xhr;
	  },
	  type: "GET",
	  url: url,
	  data: data,
	})
	.success(
	    // Fetch successful
	    function (datas) {
	    	$('#progressBlock .stdLoaderButton').hide();
	    	document.getElementById('progress').innerHTML = "";
	    	$('#nextButton').prop('disabled', false);
	    	if(datas!=""){
	    		validate_imgPathList = datas;//res;
	    		for (data of datas) {
				    var ar1d = LZW.decompress(JSON.parse(data.mask.slicStr)).split(" ");
					var ar2d = [], size = data.naturalWidth;
					while (ar1d.length > 0) ar2d.push(ar1d.splice(0, size));
					var tag1d = LZW.decompress(JSON.parse(data.mask.segInfo)).split(" ");
					data.mask.udata = ar2d;
					data.mask.segInfo = JSON.parse(tag1d[0]);
				}
				//console.log(datas);
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
	  validate_drawSlic();
	  validate_drawLegend(validate_srcId);
	  validate_drawAreas(validate_srcId);//initSelection();
	  validate_drawSegments();
	  img.removeEventListener('load', loaded);
	  img.removeEventListener('error', error);
	  updateNbrAreas();
	  validate_initDraw();
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
	
	document.getElementById('imgCounter').style = "DISPLAY: none;";//"Image "+(validate_imgPathListIndex+1)+" of "+validate_imgPathList.length;
	document.getElementById("moreButton").style = "DISPLAY: none;";
	document.getElementById("RejectButton").style = "DISPLAY: initial;";
	document.getElementById("ValidateButton").style = "DISPLAY: initial;";
}

function validate_drawSlic(){
	data = validate_imgPathList[validate_imgPathListIndex];
	gridarray = data.mask.udata;
	//console.log("draw grid");
	//console.log(gridarray);
	var refImage = document.getElementById('image');
	var slicCanvas = document.getElementById("slicCanvas");
	slicCanvas.width = parseInt(data.naturalWidth);
	slicCanvas.height = parseInt(data.naturalHeight);
	var segmentCanvas = document.getElementById("segmentCanvas");
	segmentCanvas.width = parseInt(data.naturalWidth);
	segmentCanvas.height = parseInt(data.naturalHeight);
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
	document.getElementById('value2').innerHTML = data.mask.NbrSeg;
	document.getElementById('value3').innerHTML = data.mask.compactness;
}
function redrawSlic(){

	var initRatio = validate_getImgRatio();

	data = validate_imgPathList[validate_imgPathListIndex];
	if(typeof data === 'undefined'){
		return;
	}
	gridarray = data.mask.udata;
	//console.log("draw grid");
	//console.log(gridarray);
	var refImage = document.getElementById('image');
	var slicCanvas = document.getElementById("slicCanvas");
	slicCanvas.style.width = refImage.width+'px';
	slicCanvas.style.height = refImage.height+'px';
	var segmentCanvas = document.getElementById("segmentCanvas");
	segmentCanvas.style.width = refImage.width+'px';
	segmentCanvas.style.height = refImage.height+'px';
	var lineCanvas = document.getElementById("lineCanvas");
	lineCanvas.style.width = refImage.width+'px';
	lineCanvas.style.height = refImage.height+'px';
	
}
function validate_drawSegments(){
	var initRatio = validate_getImgRatio();
	var refPreview = document.getElementById('preview');
	var segmentCanvas = document.getElementById("segmentCanvas");
	data = validate_imgPathList[validate_imgPathListIndex];
	segInfo = data.mask.segInfo;
	for(var i = 0; i < segInfo.length; i++){
		if(segInfo[i] != -1){
			//color segment
			selectedCat = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == segInfo[i]})];
			color = selectedCat.Color;
			validate_segFillCollor(segmentCanvas, i, color, data);
		}
	}
}
function validate_segFillCollor(canva, segment, color, data){
	gridarray = data.mask.udata;
	
	var initRatio = validate_getImgRatio();
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

function validate_drawAreas(idImage){
	var areaCanvas = document.getElementById("areaCanvas");
	var refImage = document.getElementById('image');
	areaCanvas.width = refImage.width;
	areaCanvas.height = refImage.height;
	var initRatio = validate_getImgRatio();
	for(var i = 0; i < validate_AreasList.length; ++i){
		reviewedArea = validate_AreasList[i];
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
		}
	}
}
function validate_drawLegend(idImage){
	var legendDiv = document.getElementById("legend");
	/*var legend = {};
	for(var i = 0; i < validate_AreasList.length; ++i){
		reviewedArea = validate_AreasList[i];
		if(parseInt(reviewedArea.source) == idImage){
			legend[reviewedArea.category.id] = reviewedArea.category;
		}
	}*/

	legendDiv.innerHTML = "";
	var legend = {};

	data = validate_imgPathList[validate_imgPathListIndex];
	segdata = data.mask.segInfo;
	for(var i = 0; i < segdata.length; ++i){
		var segmentCatID = segdata[i];
		if(segmentCatID != -1){
			legend[segmentCatID] = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == segmentCatID})];;
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
	validate_wipeSegment();
	validate_wipeGrid();
}
function validate_wipeSegment(){
	var segmentCanvas = document.getElementById("segmentCanvas");
	slicCtx = segmentCanvas.getContext("2d");
	slicCtx.clearRect(0, 0, segmentCanvas.width, segmentCanvas.height);
	validate_wipeLegend();
}
function validate_wipeLegend(){
	var elements = document.getElementsByClassName("legend");
	while(elements.length>0){
		elements[0].remove();
		
	}
}
function validate_wipeGrid(){
	var slicCanvas = document.getElementById("slicCanvas");
	slicCtx = slicCanvas.getContext("2d");
	slicCtx.clearRect(0, 0, slicCanvas.width, slicCanvas.height);
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

	var imageData = validate_imgPathList[validate_imgPathListIndex];
	imageData.mask.udata = [];
	
	var stringedTag = JSON.stringify(imageData.mask.segInfo);
	var lzwTagCompressed = JSON.stringify(LZW.compress(stringedTag));
	var data= {};
	data["dataSrc"]=validate_srcId;
	data["validateType"]=validated;
	data["updated"]= validate_imgPathList[validate_imgPathListIndex].updated_at;
	data["segInfo"] = lzwTagCompressed;
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

//window.addEventListener("resize", function(){
//  	validate_drawAreas(validate_srcId);
//});

function redrawAll(){
	redrawSlic();
}

/*window.addEventListener("resize", function(){
	redrawAll();
});

//chrome fix
var globImage = document.getElementById('image');
new ResizeObserver(redrawAll).observe(globImage);*/

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

function validate_onViewSegmentClicked(element){

	if (element.checked){
		validate_showSegSegment(true);
	}
	else{
		validate_showSegSegment(false);
	}
}
function validate_showSegSegment(bool){
	var segmentCanvas = document.getElementById("segmentCanvas");
	if(bool){
		segmentCanvas.style.display = "initial";
	}else{
		segmentCanvas.style.display = "none";
	}
}

function validate_onViewGridClicked(element){

	if (element.checked){
		validate_showSegGrid(true);
	}
	else{
		validate_showSegGrid(false);
	}
}
function validate_showSegGrid(bool){
	var slicCanvas = document.getElementById("slicCanvas");
	if(bool){
		slicCanvas.style.display = "initial";
	}else{
		slicCanvas.style.display = "none";
	}
}

function validate_onViewImgClicked(element){

	if (element.checked){
		validate_showSegImg(true);
	}
	else{
		validate_showSegImg(false);
	}
}
function validate_showSegImg(bool){
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

function validate_initDraw(){
	var canvas = document.getElementById('imageDiv');
	canvas.onclick = function(e){
		onClickHandler(e);
	}
}

function onClickHandler(e) {
	var initRatio = validate_getImgRatio();
	var refPreview = document.getElementById('preview');
	
	data = validate_imgPathList[validate_imgPathListIndex];
	gridarray = data.mask.udata;
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

	var segmentCanvas = document.getElementById("segmentCanvas");

	
	//color get color
	var combo = document.getElementById("combo");
	var type = combo.options[combo.selectedIndex].value;
	var selectedCat = mainContainer.catData[mainContainer.catData.findIndex(function(x){return x.id == type})];
	var color = selectedCat.Color;
	var colorId = parseInt(type);
	segdata = data.mask.segInfo;
	if(segdata[segmentId] == colorId){
		segdata[segmentId] = -1;
		color = "null";
	}else{
		segdata[segmentId] = colorId;
	}
	
	// launch function

	validate_segFillCollor(segmentCanvas, segmentId, color, data);

	validate_drawLegend(validate_imgPathList[validate_imgPathListIndex].id);
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