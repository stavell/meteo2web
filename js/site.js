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


    var updateTimeParams = function(){
        App.timeParams.timeFrom = App.getUrlVar('timeFrom') || '-1 hour';
        App.timeParams.timeTo = App.getUrlVar('timeTo') || 60;
    };
    updateTimeParams();

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
        Server.call('Meteo2.getPhotosForPeriod', [timeParams.timeFrom, timeParams.timeTo], function (response) {
            App.ImgLoader.setFiles(response);
            App.ImgLoader.startLoading();
            App.ImgLoader.onFinish = function () {
                imgViewer.setFiles(response);
                imgViewer.startSlideshow();
            };
        });
    };


    App.initCameraViewer('.intro', {

        onImageChanged: function (file, index) {
            $('.photoInfo').html(App.getFormatedDateTime(new Date(file.timestamp*1000)));
        }

    });

    $('.pause-slideshow').hover(function(){
        imgViewer.stopSlideshow();
    }, function(){
        imgViewer.startSlideshow();
    });

    $('.photo-control-prev').click(function () {
        $($(this).attr('viewer'))[0].showPrev(true);
    });

    $('.photo-control-next').click(function () {
        $($(this).attr('viewer'))[0].showNext(true);
    });

    $('.btn-download-image').click(function(){
        window.open(imgViewer.getCurrentFile()['url']);
    });



    $('[name=dateFrom],[name=dateTo]').datepicker({
        format: "dd.mm.yyyy",
        endDate: "today",
        weekStart: 1,
        autoclose: true,
        todayHighlight: true
    });

    var initModalFields = function(){
        $('[name=dateFrom],[name=dateTo]').datepicker('setDate',[Date()]);

        var startTime = new Date(new Date().getTime()-(60*60*1000));

        if(new Date().getDate() - startTime.getDate()) $('[name=dateFrom]').datepicker('setDate',[new Date(new Date().getTime()-(24*60*60*1000))]);

        $('[name=timeFrom]').val(App.getFormatedTime(startTime));
        $('[name=timeTo]').val(App.getFormatedTime(new Date()));

        $('[name=slideshowInterval]').val(imgViewer.delay);

    };
    initModalFields();


    $('.modal-settings-ok-btn').click(function(){
        var timeFrom = $('[name=dateFrom]').val()+' '+$('[name=timeFrom]').val();
        var timeTo   = $('[name=dateTo]').val()+' '+$('[name=timeTo]').val();

        imgViewer.setDelay($('[name=slideshowInterval]').val());

        history.pushState(null, null, location.origin+location.pathname+'?timeFrom='+timeFrom+'&timeTo='+timeTo);

        updateTimeParams();
        loadData();
    });


    var loadWeatherData = function(timeParams) {
        if (!timeParams) return;
        Server.call('Meteo2.getWeatherDataForPeriod', [timeParams.timeFrom, timeParams.period, 20, timeParams.asc], function(response){
            $('#weather_data').html(null);
            $.each(response,function(idx,data){
                $('#weather_data').append(makeWeatherTableRow(data));
            });
        });
    };

    var baroHeight = function baroHeight(height,obs,temp) {
        height *=3.2808;
        temp = temp * 1.8 + 32;
        temp += 459.67;
        // Calculate altitude correction
        var result = 29.92126 * (1 - (1 / Math.pow(10, ((0.0081350 * height) / (temp + (0.00178308 * height))))));
        return result * 33.8637526
    };

    var makeWeatherTableRow = function(data) {
        var $block = $('#weather_row_template').clone().removeAttr('id');

        $block.find('.wr-temperature').text(parseFloat(data.temperature).toFixed(1)+' ℃');
        $block.find('.wr-humidity').text(data.humidity+'% rH');
        $block.find('.wr-pressure').text(parseFloat(data.pressure + baroHeight(500,parseFloat(data.pressure),parseFloat(data.temperature))).toFixed(0)+' mb');
//            $block.find('.windDir').text(data.wind_dir_sym+' '+data.wind_dir+'°');
        $block.find('.wr-windDir').text('---.- °');
//            $block.find('.windSpeed').text(data.wind_count+' m/s');
        $block.find('.wr-windSpeed').text('-.- m/s');

        var date = new Date(data.timestamp*1000);
        $block.find('.wr-time').text(App.getFormatedDateTime(date));

        return $block;
    };


    var loadUserInfo = function() {
        Server.call('FbUsers.getCurrentUserInfo',null,function(info){
            if(info['login_url']) {
                $(".fb-login").click(function(){
                    Server.call('FbUsers.getLoginURL',null,function(url){
                        window.open(url['login_url'],'_blank');
                    });
                });
            } else {
                $(".fb-login").off('click');
                $(".fb-login").click(function(){
                    if(confirm("Logout?")) Server.call('FbUsers.logOut',null,function(){});
                });
                $(".fb-title").text(info['user']['name']);
            }
        });
    };

    var loadData = function(){
        loadPhotos(App.timeParams);
        loadWeatherData(App.timeParams);
        loadUserInfo();
    };

    loadData();


    var ws = new WebSocket('ws://stavl.com:10080');

    ws.onopen= function(){
        ws.send('weatherData');
    };

    ws.onerror = function(){
        console.log(arguments);
    };

    ws.onmessage = function(m){
        try{
            var data = JSON.parse(m.data);
            var date = new Date(data.timestamp*1000);

            $('.live-data').css({opacity: 0.8});
            $('.ws-time').text('Live data from: ' + (date.getHours()< 9 ? '0':'')+date.getHours() + ':' + (date.getMinutes()< 10 ? '0':'') + date.getMinutes() + ':' + (date.getSeconds()< 10 ? '0':'') + date.getSeconds());
            $('.ws-windSpeed').html($("<strong></strong>").text('-.- m/s'));
            $('.ws-windDir').html($("<strong></strong>").text('---.- °'));
            $('.ws-temperature').html($("<strong></strong>").text(parseFloat(data.temperature).toFixed(1)+' ℃'));
            $('.live-data').animate({opacity: 0.5}, 500);
        } catch(e){}
    };



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
    title: 'Камерата!',
    icon: './assets/img/photo-marker.png'
});

poly.setMap(map);
map.setZoom(10);

