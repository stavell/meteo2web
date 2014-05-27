$(window).scroll(function() {
    if ($(".navbar").offset().top > 50) {
        $(".navbar-fixed-top").addClass("top-nav-collapse");
    } else {
        $(".navbar-fixed-top").removeClass("top-nav-collapse");
    }
});


$(function() {

    $('.page-scroll a').bind('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top
        }, 1500, 'easeInOutExpo');
        event.preventDefault();
    });


    App.timeParams.timeFrom = App.getUrlVar('timeFrom') || '-1 hour';
    App.timeParams.period = App.getUrlVar('period') || 60;



    var updateProgressBar = function(done,total) {
        var progressBar = $('.imageLoaderProgress').find('.progress-bar');
        if(done == total) $('.imageLoaderProgress').hide();
        else $('.imageLoaderProgress').show();

        progressBar.text(done+'/'+total).css('width',parseInt((done/total)*100)+'%');
    };


    App.ImgLoader.imageFilter = function(){ return this.width > 800;};
    App.ImgLoader.onProgressUpdate = function(done,total) {
        updateProgressBar(done,total);
    };


    var imgViewer = $('.intro')[0];

    var loadPhotos = function (timeParams) {
        if (!timeParams) return;
        imgViewer.stopSlideshow();
        Server.call('Meteo2.getPhotosForPeriod', [timeParams.timeFrom, timeParams.period], function (response) {
            App.ImgLoader.setFiles(response);
            App.ImgLoader.startLoading();
            App.ImgLoader.onFinish = function () {
                imgViewer.setFiles(response);
                imgViewer.startSlideshow(600);
            };
        });
    };


    App.initCameraViewer('.intro', {
        onImageChanged: function (file, index) {
            $('.photoInfo')[0].file = file;
            $('.photoInfo').html(App.getFormatedDateTime(new Date(file.timestamp*1000)));
        }
    });


    $('.photoInfo').click(function(){
        if(!this.file) return;
        window.open(this.file.url);
    });

    $('.photo-control-prev').click(function () {
        $($(this).attr('viewer'))[0].showPrev(true);
    });

    $('.photo-control-next').click(function () {
        $($(this).attr('viewer'))[0].showNext(true);
    });


    loadPhotos(App.timeParams);


});

//Google Map Skin - Get more at http://snazzymaps.com/
var myOptions = {
    zoom: 10,
    center: new google.maps.LatLng(43.358617472998986, 27.128695869445767),
    mapTypeId: google.maps.MapTypeId.HYBRID,
    disableDefaultUI: true,
    styles: [{
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 17
        }]
    }, {
        "featureType": "landscape",
        "elementType": "geometry",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 20
        }]
    }, {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 17
        }]
    }, {
        "featureType": "road.highway",
        "elementType": "geometry.stroke",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 29
        }, {
            "weight": 0.2
        }]
    }, {
        "featureType": "road.arterial",
        "elementType": "geometry",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 18
        }]
    }, {
        "featureType": "road.local",
        "elementType": "geometry",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 16
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 21
        }]
    }, {
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }, {
            "lightness": 16
        }]
    }, {
        "elementType": "labels.text.fill",
        "stylers": [{
            "saturation": 36
        }, {
            "color": "#000000"
        }, {
            "lightness": 40
        }]
    }, {
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "transit",
        "elementType": "geometry",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 19
        }]
    }, {
        "featureType": "administrative",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 20
        }]
    }, {
        "featureType": "administrative",
        "elementType": "geometry.stroke",
        "stylers": [{
            "color": "#000000"
        }, {
            "lightness": 17
        }, {
            "weight": 1.2
        }]
    }]
};


var map = new google.maps.Map(document.getElementById('map'), myOptions);


var poly = new google.maps.Polygon({
    paths: [
        new google.maps.LatLng(43.264378, 26.935230),
        new google.maps.LatLng(43.451313, 27.063384),
        new google.maps.LatLng(43.326556, 27.316752),
        new google.maps.LatLng(43.255500, 26.945280),
        new google.maps.LatLng(43.264378, 26.935230)
    ],
    strokeColor: '#FFFFFF',
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: '#FFFFFF',
    fillOpacity: 0.35
});

var marker = new google.maps.Marker({
    position: new google.maps.LatLng(43.252662,26.927483),
    map: map,
    title: 'Камера',
    icon: './assets/img/photo-marker.png'
});

poly.setMap(map);
map.setZoom(10);