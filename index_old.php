<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, maximum-scale=1">
<script type="text/javascript">
    BASE_URL = '';
</script>
<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/Server.js"></script>
<script type="text/javascript" src="js/app.js"></script>
<script type="text/javascript" src="js/jquery.storageapi.js"></script>
<script type="text/javascript" src="js/Chart.js"></script>

<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-3807106-7', 'stavl.com');ga('send', 'pageview');</script>

<script type="text/javascript">

var isMobileURL = function(){
    return location.hash.toLowerCase() == '#m' ? true : false;
}

if( App.isMobileBrowser && !isMobileURL()) {
    window.location.href = window.location.href+'#m';
}


var updateDataTimeOut = 0;
var updateBackgroundTimeOut = 0;

$(document).ready(function() {

    var load = function(){};

    var onCSSLoad = function() {


        App.ImgLoader.onProgressUpdate = function(done) {
            $('.progressInfo').text(done+'/'+App.ImgLoader.files.length);
        };


        var updateBackground = function() {
            var img = App.ImgLoader.files.pop();
            if(!img) {
                loadPhotos(App.timeParams);
                return;
            }
            $('#photo').css({backgroundImage:'url("'+img.url+'")'});
            var imageDate = new Date(img.timestamp*1000);
            $('.progressInfo').text(App.getFormatedTime(imageDate));
            updateBackgroundTimeOut = setTimeout(updateBackground,500);
        };

        var loadPhotos = function(timeParams){
            if(!timeParams) return;

            Server.call('Meteo2.getPhotosForPeriod', [timeParams.timeFrom, timeParams.period, false], function(response){
                App.ImgLoader.setFiles(response);
                App.ImgLoader.onFinish = function() {
                    updateBackground();
                };
                App.ImgLoader.startLoading();
            } );

        };

        var loadData = function(timeParams,cb){

            Server.call('Meteo2.getWeatherDataForPeriod', [timeParams.timeFrom, timeParams.period, !isMobileURL() ? 12 : 20, timeParams.asc], function(response){
                if(cb)cb(response);
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

        var createDataBlock = function(data) {
            var $block = $('#dataBlockPrototype').clone().removeAttr('id');

            $block.find('.temperature').text(parseFloat(data.temperature).toFixed(1)+' ℃');
            $block.find('.humidity').text(data.humidity+'% rH');
            $block.find('.pressure').text(parseFloat(data.pressure + baroHeight(500,parseFloat(data.pressure),parseFloat(data.temperature))).toFixed(0)+' mb');
//            $block.find('.windDir').text(data.wind_dir_sym+' '+data.wind_dir+'°');
            $block.find('.windDir').text('---.- °');
//            $block.find('.windSpeed').text(data.wind_count+' m/s');
            $block.find('.windSpeed').text('-.- m/s');

            var date = new Date(data.timestamp*1000);
            $block.find('.time').text(App.getFormatedTime(date));

            return $block;
        };

        var drawDataBlocks = function(response) {
            $('#dataBlocksContainer').html(null);

            $.each(response,function(idx,data){
                $('#dataBlocksContainer').append(createDataBlock(data));
            });
        }


        var updateData = function(cb) {
            loadData(App.timeParams,cb);
            updateDataTimeOut = setTimeout(function(){updateData(cb);},60*1000)
        };


        load = function(){
            clearTimeout(updateBackgroundTimeOut);
            clearTimeout(updateDataTimeOut);

            if(isMobileURL()) {
                App.timeParams.asc = false;
                updateData(function(r){
                    drawDataBlocks(r);
                });
            } else {
                App.timeParams.asc = true;
                loadPhotos(App.timeParams);
                updateData(function(r){
                    drawDataBlocks(r);
                    updateChart(r);
                });
            }
        };

        load();

    };

    var loadCSS = function(cb) {
        $.get('css/' + (isMobileURL() ? 'mobile.css' : 'default.css' )).complete(function(r){
            $('#pageStyle').text(r.responseText);
            if(cb)cb();
        });
    }

    var onHashChange = function(){
        loadCSS(onCSSLoad);
    }



    $(window).bind('hashchange', onHashChange);
    $(window).trigger('hashchange');


    var updateChart = function(response) {

        response = response ? response : $('#charts')[0].response;

        $('#charts')[0].response = response;

        var datasets = {
            windSpeed : {
                fillColor : "rgba(213,0,88,0.3)",
                strokeColor : "rgba(213,0,88,1)",
                pointColor : "rgba(213,0,88,1)",
                pointStrokeColor : "rgba(255,255,255,0.5)",
                data: [],
                label: ' m/s',
                step: 0.5
            },
            temperature : {
                fillColor : "rgba(255,108,0,0.3)",
                strokeColor : "rgba(255,108,0,1)",
                pointColor : "rgba(255,108,0,1)",
                pointStrokeColor : "rgba(255,255,255,0.5)",
                data: [],
                label: ' ℃',
                step: 1
            },
            humidity : {
                fillColor : "rgba(123,165,222,0.5)",
                strokeColor : "rgba(123,165,222,1)",
                pointColor : "rgba(123,165,222,1)",
                pointStrokeColor : "#fff",
                data: [],
                label: '% rH',
                step: 10
            },
            pressure : {
                fillColor : "rgba(186,188,24,0.5)",
                strokeColor : "rgba(186,188,24,1)",
                pointColor : "rgba(186,188,24,1)",
                pointStrokeColor : "#fff",
                data: [],
                label: 'mB',
                step: 1
            }
        };


        var labels = [];

        $.each(response, function(idx,val){
            var date = new Date(val.timestamp*1000);

            labels.push(App.getFormatedTime(date));

            datasets.humidity.data.push(val.humidity);
            datasets.pressure.data.push(val.pressure);
            datasets.temperature.data.push(val.temperature);
            datasets.windSpeed.data.push(val.wind_count);
        });

        for(var key in datasets) {
            $("#"+key+"Chart").attr({
                width: $(document).width()-20
            });

            var ctx = $("#"+key+"Chart").get(0).getContext("2d");

            new Chart(ctx).Line({
                'labels': labels,
                'datasets': [datasets[key]]
            }, {
                scaleOverlay: true
                ,pointDot: true
                ,pointDotRadius: 1
                ,scaleOverride: true
                ,scaleSteps : ((Math.max.apply(Math, datasets[key].data) - Math.min.apply(Math, datasets[key].data)) / datasets[key].step) + 1
                ,scaleStepWidth : datasets[key].step
                ,scaleStartValue : Math.min.apply(Math, datasets[key].data) - (key == 'windSpeed' ? 0 : datasets[key].step)
                ,scaleLabel : "<%=value%>"+datasets[key].label
                ,scaleFontFamily : "Verdana, Arial, Helvetica, sans-serif"
                ,animationSteps : 60
                ,animationEasing : "easeOutQuart"
                ,scaleGridLineColor : "rgba(255,255,255,.05)"
            });
        }
    };


    $('.chartsBtn').click(function(){
        $('#charts').toggle();
        if(!$(this).hasClass('close')) updateChart();
    });



    $('.command').click(function(){
        var valEl = $(this).parent().find('input.value');
        var val = parseInt(valEl.val());
        var adder = $(this).hasClass('plus') ? 1 : -1;
        var nonZero = valEl.hasClass('non-zero');
        var onlyPositive = valEl.hasClass('positive');
        var onlyNegative = valEl.hasClass('negative');


        if(onlyPositive && val + adder < 0) return;

        if(onlyNegative && (val + adder) >= 0) return;

        if(nonZero && (val + adder == 0)) return;

        valEl.val( val + adder );

        updateParams();
    });


    $('.confirm').click(function(){
        load();
    });

    var updateParams = function() {
        timeParams.period = $('input.period').val();
        if(parseInt($('input.days').val())) {
            timeParams.timeFrom = '-'+$('input.days').val() + ' day ' + $('input.hours').val() + 'hour';
        } else {
            timeParams.timeFrom = '-'+$('input.hours').val() + 'hour';
        }
    }

    $(".numeric").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });


});

</script>

<style id="pageStyle"></style>

</head>

<body>
<div id="controls" style="display:none">

    <div>Старт</div>

    <div class="control">
        <div class="command plus">+</div>
        <input type="text" class="value numeric positive days" value="0" readonly="readonly"/>
        <div class="command minus">-</div>
    </div>

    <div class="control">
        <div class="command plus">+</div>
        <input type="text" class="value numeric positive non-zero hours" value="1" readonly="readonly"/>
        <div class="command minus">-</div>
    </div>

    <div style="clear: both;"></div>

    <div>Период</div>

    <div class="control">
        <div class="command plus">+</div>
        <input type="text" class="value numeric positive non-zero period" value="60"/>
        <div class="command minus">-</div>
    </div>

    <div><button class="confirm">Покажи</button></div>

</div>
<div id="charts">
    <span class="chartsBtn close">Затвори</span>
    <canvas id="windSpeedChart" width="1000" height="200"></canvas>
    <canvas id="temperatureChart" width="1000" height="200"></canvas>
    <canvas id="humidityChart" width="1000" height="200"></canvas>
    <canvas id="pressureChart" width="1000" height="200"></canvas>
</div>
<div id="photo"></div>
<div id="container">
    <div id="dataBlocksContainer">Waiting for data...</div>
    <div style="clear:both;"></div>
</div>


<div style="display:none">
    <div id="dataBlockPrototype" class="dataBlock">
        <div class="windSpeed"></div>
        <div class="windDir"></div>
        <div class="pressure"></div>
        <div class="humidity"></div>
        <div class="temperature"></div>
        <div class="time"></div>
    </div>
</div>


<span class="progressInfo"></span>
<span class="chartsBtn">Графики</span>
</body>

</html>
