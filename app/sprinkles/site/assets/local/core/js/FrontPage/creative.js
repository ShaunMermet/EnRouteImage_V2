(function($) {
    "use strict"; // Start of use strict

    // Smooth scrolling using jQuery easing
    $('a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: (target.offset().top - 48)
                }, 1000, "easeInOutExpo");
                return false;
            }
        }
    });

    // Activate scrollspy to add active class to navbar items on scroll
    $('body').scrollspy({
        target: '#mainNav',
        offset: 48
    });

    // Closes responsive menu when a link is clicked
    $('.navbar-collapse>ul>li>a').click(function() {
        $('.navbar-collapse').collapse('hide');
    });



    // Scroll reveal calls
    window.sr = ScrollReveal();
    sr.reveal('.sr-icons', {
        duration: 600,
        scale: 0.3,
        distance: '0px'
    }, 200);
    sr.reveal('.sr-button', {
        duration: 1000,
        delay: 200
    });
    sr.reveal('.sr-contact', {
        duration: 600,
        scale: 0.3,
        distance: '0px'
    }, 300);

   

})(jQuery); // End of use strict

$(document).ready(function() {
  $(".js-basic-lang").select2({
    minimumResultsForSearch: Infinity,
  });

  var localeElem = document.getElementById('locale');
  if(localeElem){
    var locale = localeElem.getAttribute("data-locale");
    $(".js-basic-lang").val(locale).trigger('change.select2');
  }
});
$(".js-basic-lang").on('change', function (evt) {
  var locale = evt.target.value;
  var data= {};
  data["locale"]=locale;
  data[site.csrf.keys.name] = site.csrf.name;
  data[site.csrf.keys.value] = site.csrf.value;
  $.ajax({ 
    type: "POST",
    url: site.uri.public + '/translate/index',
    data: data,
    success: function(data) {
        var newDoc = document.open("text/html", "replace");
        newDoc.write(data);
        newDoc.close();
    }
    });
});
