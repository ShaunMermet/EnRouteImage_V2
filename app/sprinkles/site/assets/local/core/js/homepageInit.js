label_initpage("homepage");

$(document).ready(function() {
  $(".js-basic-lang").select2({
  	minimumResultsForSearch: Infinity,
  });

  var localeElem = document.getElementById('locale');
  var locale = localeElem.getAttribute("data-locale");
  $(".js-basic-lang").val(locale).trigger('change.select2');
});
$(".js-basic-lang").on('change', function (evt) {
  var locale = evt.target.value;
  var data= {};
  data["locale"]=locale;
  data[site.csrf.keys.name] = site.csrf.name;
  data[site.csrf.keys.value] = site.csrf.value;
  $.ajax({ 
    type: "POST",
  	url: site.uri.public + '/translate/set',
  	data: data,
  	success: function(data) {
        var newDoc = document.open("text/html", "replace");
		newDoc.write(data);
		newDoc.close();
    }
	});
});