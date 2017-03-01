accountSettings_getAreasValidated();
function accountSettings_getAreasValidated(){
	var url = site.uri.public + '/areas/areauserstats';
	$.ajax({
		type: "GET",
		url: url
	})
	.then(
	    // Fetch successful
	    function (data) {
	    	if(data!=""){
	    		document.getElementById("account_submitted_textBox").textContent = data['pendingArea']+data['deletedArea']+data['validatedArea'];
	    		document.getElementById("account_submitted_textBox").title = data['pendingArea']+data['deletedArea']+data['validatedArea'];

	    		document.getElementById("account_rejected_textBox").textContent = data['deletedArea'];
	    		document.getElementById("account_rejected_textBox").title = data['deletedArea'];

	    		document.getElementById("account_validated_textBox").textContent = data['validatedArea'];
	    		document.getElementById("account_validated_textBox").title = data['validatedArea'];

	    		var success = 0;
	    		if((data['validatedArea'] + data['deletedArea']) != 0){
	    			success = 100*data['validatedArea']/(data['validatedArea'] + data['deletedArea'])
	    		}
	    		success = Math.floor(success * 100) / 100;
	    		var rate = success + "%";
	    		var color = "#000000"
	    		var ico = "";
	    		if(success < 75){
	    			ico = "fa fa-frown-o";
	    			color = "#dd4b39 "
	    		}else if(success < 90){
					ico = "fa fa-meh-o";
					color = "#f39c12"
	    		}else{
					ico = "fa fa-smile-o";
					color = "#00a65a "
	    		}
	    		

	    		document.getElementById("account_successRate_textBox").textContent = rate;
	    		document.getElementById("account_successRate_textBox").title = rate;
	    		document.getElementById("account_successRate_icone").setAttribute("class", ico);
	    		document.getElementById("account_successRate_textBox").style.color = color;
	    		document.getElementById("account_successRate_icone").style.color = color;
	    		
    		}
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}