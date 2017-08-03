function import_onOtherChanged(){
	var imgArray = [];
	
	var promisedImages = document.getElementsByClassName("template-upload fade");
	for(i = 0; i < promisedImages.length; i++){	
		fullName = promisedImages[i].getElementsByClassName("hiddenName")[0].value;
		imgArray.push(fullName.split(".")[0]);

	}
	var otherInput = document.getElementById("fileOtherImport");
	var otherList = otherInput.files;
	for(i = 0; i < otherList.length; i++){
		console.log(otherList[i].name);
		if(otherList[i].name == 'filename.txt'){
			console.log('name file detected');
			getFileData(otherList[i],"",function(data,row){
				var names = [];
				for(k = 0; k < data.length; k++){
					line = data[k].split(",");
					names[line[0]] = line[1];
				}
				for(j = 0; j < promisedImages.length; j++){
					row = promisedImages[j];
					nameSlot = row.getElementsByClassName("name")[0];
					oldname = nameSlot.innerText;
					nameSlot.innerText = names[oldname];
					nameSlot.title = oldname;
					oNameSlot = row.getElementsByClassName("hiddenOname")[0];
					oNameSlot.value = names[oldname];
				}
			});
		}
		else if(imgArray.indexOf(otherList[i].name.split(".")[0]) == -1){
			console.log("Error no corresponding img found");
		}
		else{
			for(j = 0; j < promisedImages.length; j++){	
				promisedImageName = promisedImages[j].getElementsByClassName("hiddenName")[0].value;
				if(promisedImageName.split(".")[0] == otherList[i].name.split(".")[0]){
					
					var rowFound = promisedImages[j];
					getFileData(otherList[i],rowFound,function(data,row){
						var cat = getCat(data);
						row.getElementsByClassName("data")[0].innerText = cat+" : "+data.length;
						row.getElementsByClassName("hiddenData")[0].value = data;
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
                res = reader.result.split("\n");
                res = res.filter(Boolean);
                console.log(res);
                callback(res,row);
            }

            reader.readAsText(file);    
        } else {
            console.log("File not supported!");
        }
	}
	function getCat(data){
		cat = "";
		if (data.length > 0) {
			cat = data[0].split(" ")[0];
		}
		return cat;
	}
}