function tools_freeImage (idImage){
	var data= {};
	data["dataSrc"]=idImage;

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	// Free the images (became available)
	var url = site.uri.public + '/freeimage/'+ idImage;
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

function tools_freeImageNA (idImage){
	var data= {};
	data["dataSrc"]=idImage;

	data[site.csrf.keys.name] = site.csrf.name;
	data[site.csrf.keys.value] = site.csrf.value;

	// Free the images (became available)
	var url = site.uri.public + '/freeimageNA/'+ idImage;
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