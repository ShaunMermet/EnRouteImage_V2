function hideLeftSub(){
	$("#leftSubMenu").hide();
	$("#fetchFeedback").hide();
}

function showLeftSub(){
	$("#leftSubMenu").show();
}
function showFiler(){
	$("#fetchFeedback").show();
}

//////////////////////////////////////////////////////////////////////


function genericAddImage(content,srcsPath){
	//Needed in html.twig
	var img = document.getElementById('image');
	var imgContainer = document.getElementsByClassName('labelimg-container');
	var preview = document.getElementById('preview');
	
	var nativeWidth = content.naturalWidth;
	var nativeHeight = content.naturalHeight;
	if(nativeWidth/nativeHeight > 16/9){
		console.log("wide image");
		img.style.height = "100%";
		img.style.width = "";
		imgContainer[0].style.height = "calc(100vh - 168px)";
		imgContainer[0].style.width = "calc(100% - 100px)";
		preview.style.overflowX = 'auto';
	}else{
		console.log("classic image");
		img.style.height = "";
		img.style.width = "100%";
		imgContainer[0].style.height = "";
		imgContainer[0].style.width = "";
		preview.style.overflowX = '';
	}
	img.src = srcsPath+content.path;
	if (img.complete) {
		  loaded();
	} else {
	  img.addEventListener('load', loaded)
	  img.addEventListener('error', error)
	}
	///Load complete
	function loaded() {
		showLeftSub()
		img.removeEventListener('load', loaded);
		img.removeEventListener('load', error);
		onImageLoaded();
	}
	function error() {
		img.removeEventListener('load', loaded);
  		img.removeEventListener('error', error);
	}
}

///////////////////////////////////////////////////////////////////////

