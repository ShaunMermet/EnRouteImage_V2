function import_onOtherChanged(){
	//var otherInput = document.getElementById("fileOtherImport");
	//otherInput.value = "";
	//return;
	//TDOD : Finish import
	var imgArray = [];
	
	var promisedImages = document.getElementsByClassName("template-upload fade");
	for(i = 0; i < promisedImages.length; i++){	
		imgArray.push(promisedImages[i].children[4].innerText.split(".")[0]);
	}
	var otherInput = document.getElementById("fileOtherImport");
	var otherList = otherInput.files;
	for(i = 0; i < otherList.length; i++){
		console.log(otherList[i].name);
		if(imgArray.indexOf(otherList[i].name.split(".")[0]) == -1){
			console.log("Error no corresponding img found");
		}
		else{
			for(j = 0; j < promisedImages.length; j++){	
				if(promisedImages[j].children[4].innerText.split(".")[0] == otherList[i].name.split(".")[0]){
					
					//var data = getFileData(otherList[i]);
					var rowFound = promisedImages[j];
					getFileData(otherList[i],rowFound,function(data,row){
						row.children[5].innerText = "Poly : "+data.length;
						row.children[1].value = data;
					});
				}
			}
		}
	}
	otherInput.value = "";
	function getFileData(file,row,callback){
		res = [];
		var textType = /text.*/;
		if (file.type.match(textType)) {
            var reader = new FileReader();

            reader.onload = function(e) {
                console.log (reader.result);
                res = reader.result;
                //res = reader.result.split("\n");
                //res = res.filter(Boolean);
                console.log(res);
                callback(res,row);
            }

            reader.readAsText(file);    
        } else {
            console.log("File not supported!");
        }
	}
}