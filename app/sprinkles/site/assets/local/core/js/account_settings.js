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
	    		document.getElementById("account_submitted_textBox").textContent = data['pendingImg'];
	    		document.getElementById("account_submitted_textBox").title = data['pendingImg'];

	    		document.getElementById("account_rejected_textBox").textContent = data['rejectedImg'];
	    		document.getElementById("account_rejected_textBox").title = data['rejectedImg'];

	    		document.getElementById("account_validated_textBox").textContent = data['validatedImg'];
	    		document.getElementById("account_validated_textBox").title = data['validatedImg'];

	    		var success = 0;
	    		if((data['validatedImg'] + data['rejectedImg']) != 0){
	    			success = 100*data['validatedImg']/(data['validatedImg'] + data['rejectedImg'])
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





	    		document.getElementById("account_segSubmitted_textBox").textContent = data['segPendingImg'];
	    		document.getElementById("account_segSubmitted_textBox").title = data['pendingImg'];

	    		document.getElementById("account_segRejected_textBox").textContent = data['segRejectedImg'];
	    		document.getElementById("account_segRejected_textBox").title = data['segRejectedImg'];

	    		document.getElementById("account_segValidated_textBox").textContent = data['segValidatedImg'];
	    		document.getElementById("account_segValidated_textBox").title = data['segValidatedImg'];

	    		var success = 0;
	    		if((data['segValidatedImg'] + data['segRejectedImg']) != 0){
	    			success = 100*data['segValidatedImg']/(data['segValidatedImg'] + data['segRejectedImg'])
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
	    		

	    		document.getElementById("account_segSuccessRate_textBox").textContent = rate;
	    		document.getElementById("account_segSuccessRate_textBox").title = rate;
	    		document.getElementById("account_segSuccessRate_icone").setAttribute("class", ico);
	    		document.getElementById("account_segSuccessRate_textBox").style.color = color;
	    		document.getElementById("account_segSuccessRate_icone").style.color = color;
	    		
    		}
	    },
	    // Fetch failed
	    function (data) {
	        
	    }
	);
}