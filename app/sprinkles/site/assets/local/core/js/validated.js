var validated_imgPath = "../../img/";

var dataHandler = {};


validated_loadCategories();
function validated_loadCategories(){
	// Fetch and render the categories
	var url = site.uri.public + '/category/all2';
	$.ajax({
	  type: "GET",
	  url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	        
				console.log(data);
				validated_initCombos(data.rows);
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}

function validated_initCombos(data){
	var x = document.getElementsByClassName("js-basic-single");
	var i;
	for (i = 0; i < x.length; i++) {
	   validated_initCombo(x[i],data);
	}
}
function validated_initCombo(comboElem,data){
	function emptyCombo(comboElem){
		while (comboElem.childElementCount != 0){
			comboElem.removeChild(comboElem.firstChild);
		}
	}
	function catReworked(catSprunje){
		var cat = {};
		for (i = 0; i < catSprunje.length; i++) {
		   cat[catSprunje[i].id] = [];
		   cat[catSprunje[i].id]['Category'] = catSprunje[i].Category;
		   cat[catSprunje[i].id]['Color'] = catSprunje[i].Color;
		}
		return cat;
	}
	/////// ALL combos ///////
	emptyCombo(comboElem);
	

	if(comboElem.id == 'comboValidatedCategory'){
		$(comboElem).append("<option value=-1>ALL</option>");
		for (i = 0; i < data.length; i++) {
			appendToCombo(data[i].Category,data[i].id);
		}

		function appendToCombo(category,type){
			$(comboElem).append("<option value=\""+type+"\">"+category+"</option>");
		}

		dataHandler.categories = catReworked(data);
		//$(comboElem).select2(/*{placeholder: 'Select a category'}*/)
		//.on("change", function(e) {
	    //      console.log(comboElem.value+ " selected");
	          //validated_loadImages(cat);
        //});
	}else if(comboElem.id == 'comboValidatedValidated'){
		$(comboElem).append("<option value=-1>ALL</option>");
		$(comboElem).append("<option value=0>Non-validated</option>");
		$(comboElem).append("<option value=1>Validated</option>");
	}else if(comboElem.id == 'comboValidatedNbrResult'){
		$(comboElem).append("<option value=5>5</option>");
		$(comboElem).append("<option value=25>25</option>");
		$(comboElem).append("<option value=50>50</option>");
		$(comboElem).append("<option value=100>100</option>");
		$(comboElem).val(100);
	}
	
	
}


////////////GET IMG FROM SERVER//////
function validated_loadImages(categories,filter){
	
	console.log(filter);
	// Fetch and render the images
	var url = site.uri.public + '/images/imgSprunje';
	$.ajax({
		type: "GET",
		data: filter,
		url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
	    		emptyPreview();
	    		console.log(data);
	    		document.getElementById('imgFound').innerHTML = "<b>"+data.count_filtered+" image(s) found</b>";
	    		$("#page-nav").pagination({
			        items: data.count_filtered,
			        itemsOnPage: dataHandler.filter['size'],
			        cssStyle: 'light-theme',
			        currentPage: dataHandler.filter['page']+1,
			        onPageClick: function(pageNum) {
			        	console.log("pageClicked");
			        	console.log(pageNum);
			        	dataHandler.pageToRequest = pageNum;
			        	validated_sendSearch(pageNum-1);
			            // Which page parts do we show?
			            //var start = perPage * (pageNum - 1);
			            //var end = start + perPage;

			            // First hide all page parts
			            // Then show those just for our page
			            //pageParts.hide()
			                     //.slice(start, end).show();
			        }
			    });
			    $("#page-nav2").pagination({
			        items: data.count_filtered,
			        itemsOnPage: dataHandler.filter['size'],
			        cssStyle: 'light-theme',
			        currentPage: dataHandler.filter['page']+1,
			        onPageClick: function(pageNum) {
			        	console.log("pageClicked");
			        	console.log(pageNum);
			        	dataHandler.pageToRequest = pageNum;
			        	validated_sendSearch(pageNum-1);
			            // Which page parts do we show?
			            //var start = perPage * (pageNum - 1);
			            //var end = start + perPage;

			            // First hide all page parts
			            // Then show those just for our page
			            //pageParts.hide()
			                     //.slice(start, end).show();
			        }
			    });
				for(var i = 0; i < data.rows.length; ++i){
					var img = data.rows[i];
	    			$('#preview').append("<div  style='position:relative;' id='imgdiv"+img.id+"' ><img id='img"+img.id+"' data-id="+img.id+" class='imgDisp' unselectable='on' src='"+validated_imgPath+img.path+"' /></div>");
					//console.log("add "+data.rows[i].path);
					var imgElem = document.getElementById("img"+img.id);
					//imgElem.onclick = function(e){
						//onClickHandler(e);
					//	console.log("img left clicked");
					//	console.log(e);
					//	toggleMenuOn();
					//}

					var url = site.uri.public + '/areas/areaSprunje';
					$.ajax({
						type: "GET",
						data: { 
						    filters: {source : img.id, alive : 1},
					  	},
						url: url
					})
					.then(
					    // Fetch successful
					    function (data) {
					    	console.log(data);
					    	if (data.rows[0]){
					    		var imgElem = document.getElementById("img"+data.rows[0].source);
					    		drawRects(imgElem,data.rows,categories);
					    	}
					    },
					    // Fetch failed
					    function (data) {
					        
					    }
				    );
				}
	    		
			}
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
	//////////////////////////////////////////////////////////////////////////////
  	//////////////////////////////////////////////////////////////////////////////
  	//
  	// H E L P E R    F U N C T I O N S
  	//
	//////////////////////////////////////////////////////////////////////////////
  	//////////////////////////////////////////////////////////////////////////////

	  /**
	   * Function to check if we clicked inside an element with a particular class
	   * name.
	   * 
	   * @param {Object} e The event
	   * @param {String} className The class name to check against
	   * @return {Boolean}
	   */
	  function clickInsideElement( e, className ) {
	    var el = e.srcElement || e.target;
	    
	    if ( el.classList.contains(className) ) {
	      return el;
	    } else {
	      while ( el = el.parentNode ) {
	        if ( el.classList && el.classList.contains(className) ) {
	          return el;
	        }
	      }
	    }

	    return false;
	  }

	  /**
	   * Get's exact position of event.
	   * 
	   * @param {Object} e The event passed in
	   * @return {Object} Returns the x and y position
	   */
	  function getPosition(e) {
	    var posx = 0;
	    var posy = 0;

	    if (!e) var e = window.event;
	    
	    if (e.pageX || e.pageY) {
	      posx = e.pageX;
	      posy = e.pageY;
	    } else if (e.clientX || e.clientY) {
	      posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
	      posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	    }

	    return {
	      x: posx,
	      y: posy
	    }
	  }

	  //////////////////////////////////////////////////////////////////////////////
	  //////////////////////////////////////////////////////////////////////////////
	  //
	  // C O R E    F U N C T I O N S
	  //
	  //////////////////////////////////////////////////////////////////////////////
	  //////////////////////////////////////////////////////////////////////////////
	  
	  /**
	   * Variables.
	   */
	  var contextMenuClassName = "context-menu";
	  var contextMenuItemClassName = "context-menu__item";
	  var contextMenuLinkClassName = "context-menu__link";
	  var contextMenuActive = "context-menu--active";

	  var taskItemClassName = "imgDisp";
	  var taskItemInContext = "";

	  var clickCoords;
	  var clickCoordsX;
	  var clickCoordsY;

	  var menu = document.querySelector(".context-menu");
	  var menuItems = menu.querySelectorAll(".context-menu__item");
	  var menuState = 0;
	  var menuWidth;
	  var menuHeight;
	  var menuPosition;
	  var menuPositionX;
	  var menuPositionY;

	  var windowWidth;
	  var windowHeight;

	  /**
	   * Initialise our application's code.
	   */
	  function init() {
	    clickListener();
	    keyupListener();
	    resizeListener();
	  }
	  function __onImgLeftClicked(e) {
	      var clickeElIsLink = clickInsideElement( e, contextMenuLinkClassName );

	      if ( clickeElIsLink ) {
	        e.preventDefault();
	        menuItemListener( clickeElIsLink );
	      } else {
	        taskItemInContext = clickInsideElement( e, taskItemClassName );
	          if ( taskItemInContext ) {
		        document.getElementById("context_menu_title").childNodes[1].textContent = " Image "+taskItemInContext.getAttribute("data-id");
		    	e.preventDefault();
		        toggleMenuOn();
		        positionMenu(e);
		      } else {
		        taskItemInContext = null;
		        toggleMenuOff();
		      }
	      }
	    }
	  /**
	   * Listens for click events.
	   */
	  function clickListener() {
	  	document.removeEventListener( "click",__onImgLeftClicked);
	    document.addEventListener( "click", __onImgLeftClicked);
	  }

	  /**
	   * Listens for keyup events.
	   */
	  function keyupListener() {
	    window.onkeyup = function(e) {
	      if ( e.keyCode === 27 ) {
	        toggleMenuOff();
	      }
	    }
	  }

	  /**
	   * Window resize event listener
	   */
	  function resizeListener() {
	    window.onresize = function(e) {
	      toggleMenuOff();
	    };
	  }

	  /**
	   * Turns the custom context menu on.
	   */
	  function toggleMenuOn() {
	    if ( menuState !== 1 ) {
	      menuState = 1;
	      menu.classList.add( contextMenuActive );
	    }
	  }

	  /**
	   * Turns the custom context menu off.
	   */
	  function toggleMenuOff() {
	    if ( menuState !== 0 ) {
	      menuState = 0;
	      menu.classList.remove( contextMenuActive );
	    }
	  }

	  /**
	   * Positions the menu properly.
	   * 
	   * @param {Object} e The event
	   */
	  function positionMenu(e) {
	    clickCoords = getPosition(e);
	    clickCoordsX = clickCoords.x;
	    clickCoordsY = clickCoords.y;

	    menuWidth = menu.offsetWidth + 4;
	    menuHeight = menu.offsetHeight + 4;

	    windowWidth = window.innerWidth+window.scrollX;
	    windowHeight = window.innerHeight+window.scrollY;

	    if ( (windowWidth - clickCoordsX) < menuWidth ) {
	      menu.style.left = windowWidth - menuWidth + "px";
	    } else {
	      menu.style.left = clickCoordsX + "px";
	    }

	    if ( (windowHeight - clickCoordsY) < menuHeight ) {
	      menu.style.top = windowHeight - menuHeight + "px";
	    } else {
	      menu.style.top = clickCoordsY + "px";
	    }
	  }

	  /**
	   * Dummy action function that logs an action when a menu item link is clicked
	   * 
	   * @param {HTMLElement} link The link that was clicked
	   */
	  function menuItemListener( link ) {
	  	console.log( "Task ID - " + taskItemInContext.getAttribute("data-id") + ", Task action - " + link.getAttribute("data-action"));
	  	if( link.getAttribute("data-action") == "Delete"){
	    	var data= {};
			data["dataSrc"]=taskItemInContext.getAttribute("data-id");
			data["validated"]=0;
			data[site.csrf.keys.name] = site.csrf.name;
			data[site.csrf.keys.value] = site.csrf.value;
			var url = site.uri.public + '/validate/evaluate';
			$.ajax({
			  type: "PUT",
			  url: url,
			  data: data
			})
			.then(
			    // Fetch successful
			    function (data) {
			    	
			    },
			    // Fetch failed
			    function (data) {
			        
			    }
			);
	    }
	    validated_sendSearch();
	    toggleMenuOff();
	  }

	  /**
	   * Run the app.
	   */
	  init();

	function emptyPreview(){
		var preview = document.getElementById('preview');
		while (preview.childElementCount != 0){
			preview.removeChild(preview.firstChild);
		}
	}
	function getImgRatio(imgElem){
		return imgElem.clientWidth/imgElem.naturalWidth;
	}
	function adaptText(imgElem,rects){
		var textWidth = rects.childNodes[0].scrollWidth;
		var leftImage = imgElem.offsetLeft - 0;
		if((parseFloat(rects.style.left) + textWidth) >= (leftImage+imgElem.width)){
			rects.childNodes[0].style.left = -textWidth + 'px';
		}
		else{
			rects.childNodes[0].style.left = 0 + 'px';
		}
		var textHeight = rects.childNodes[0].scrollHeight;
		var topImage = imgElem.offsetTop;
		if((parseFloat(rects.style.top) + textHeight) >= (topImage+imgElem.height)){
			rects.childNodes[0].style.top = -textHeight + 'px';
		}
		else{
			rects.childNodes[0].style.top = 0 + 'px';
		}
	}
	function drawRects(imgElem,rects,categories){
		for(var i = 0; i < rects.length; ++i){
			reviewedRect = rects[i];
			
			var initRatio = getImgRatio(imgElem);
			currentRectangle = document.createElement('div');
			currentRectangle.className = 'rectangleView';
			var str = categories[reviewedRect.rectType].Category;
			var type = reviewedRect.rectType;
			var color = categories[reviewedRect.rectType].Color;
			currentRectangle.rectType = type;
			currentRectangle.rectSetRatio = 1;
			currentRectangle.rectSetLeft = parseInt(reviewedRect.rectLeft);
			currentRectangle.rectSetTop = parseInt(reviewedRect.rectTop);
			currentRectangle.rectSetWidth = reviewedRect.rectRight - reviewedRect.rectLeft;
			currentRectangle.rectSetHeight = reviewedRect.rectBottom - reviewedRect.rectTop;
			currentRectangle.style.left = (parseInt(reviewedRect.rectLeft)*initRatio+imgElem.offsetLeft) + 'px';
			currentRectangle.style.top = (parseInt(reviewedRect.rectTop)*initRatio+imgElem.offsetTop) + 'px';
			currentRectangle.style.border= "2px solid "+color;
			currentRectangle.style.color= color;
			var text = document.createElement('div');
			var t = document.createTextNode(str);
			text.className = 'rectangleText';
			text.appendChild(t);
			currentRectangle.appendChild(text);
			currentRectangle.style.width = (reviewedRect.rectRight - reviewedRect.rectLeft)*initRatio + 'px';
			currentRectangle.style.height = (reviewedRect.rectBottom - reviewedRect.rectTop)*initRatio + 'px';
			imgElem.parentElement.appendChild(currentRectangle);
			adaptText(imgElem,currentRectangle);


			
		}
	}
}

function validated_onSearchClicked(){
	console.log("start search");
	
	validated_sendSearch(0);
}
function validated_sendSearch(page = null){
	if (page == null){
		page = $("#page-nav").pagination('getCurrentPage')-1;
	}
	var filter= {};
	filter['filters'] = {};
	filter['size'] = document.getElementById("comboValidatedNbrResult").value;
	filter['page'] = page;
	filter['sorts'] = {validated_at : 'desc'};
	validatedType = document.getElementById("comboValidatedValidated").value;
	if(validatedType != -1 ){
	    filter['filters']['validated'] = validatedType;
	}
	categoryType = document.getElementById("comboValidatedCategory").value;
	if(categoryType != -1 ){
	    filter['filters']['category'] = categoryType;
	}

	dataHandler.filter = filter;
	validated_loadImages(dataHandler.categories,filter);
}