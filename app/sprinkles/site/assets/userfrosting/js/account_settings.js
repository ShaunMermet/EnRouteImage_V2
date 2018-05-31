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
	    		$bboxRejected = data["rejectedArea"];
	    		$bboxValidated = data["validatedArea"];
	    		$bboxPending = data["pendingArea"];
	    		$segRejected = data["segRejectedImg"];
	    		$segValidated = data["segValidatedImg"];
	    		$segPending = data["segPendingImg"];
	    		
	    		document.getElementById("account_submitted_textBox").textContent = $bboxPending;
	    		document.getElementById("account_submitted_textBox").title = $bboxPending;

	    		document.getElementById("account_rejected_textBox").textContent = $bboxRejected;
	    		document.getElementById("account_rejected_textBox").title = $bboxRejected;

	    		document.getElementById("account_validated_textBox").textContent = $bboxValidated;
	    		document.getElementById("account_validated_textBox").title = $bboxValidated;

	    		var success = 0;
	    		if(($bboxValidated + $bboxRejected) != 0){
	    			success = 100*$bboxValidated/($bboxValidated + $bboxRejected)
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





	    		document.getElementById("account_segSubmitted_textBox").textContent = $segPending;
	    		document.getElementById("account_segSubmitted_textBox").title = $segPending;

	    		document.getElementById("account_segRejected_textBox").textContent = $segRejected;
	    		document.getElementById("account_segRejected_textBox").title = $segRejected;

	    		document.getElementById("account_segValidated_textBox").textContent = $segValidated;
	    		document.getElementById("account_segValidated_textBox").title = $segValidated;

	    		var success = 0;
	    		if(($segValidated + $segRejected) != 0){
	    			success = 100*$segValidated/($segValidated + $segRejected)
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