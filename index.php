<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/jquery.storageapi.js"></script>
<script type="text/javascript" src="js/Chart.js"></script>

<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-3807106-7', 'stavl.com');ga('send', 'pageview');</script>

<script type="text/javascript">

var isMobileURL = function(){
    return location.hash.toLowerCase() == '#m' ? true : false;
}

if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && !isMobileURL()) {
    window.location.href = window.location.href+'#m';
}

$(document).ready(function() {

    var onCSSLoad = function() {

        var timeParams = {
            timeFrom: '-2 hour',
            period:120,
            asc: false
        };

        var ImgLoader = {
            files: [],
            readyFiles:[],
            cache:{},
            setFiles: function(files) {
                ImgLoader.init();
                ImgLoader.files = files;
            },
            init: function(){
                ImgLoader.readyFiles = [];
                ImgLoader.cache = {};
            },
            startLoading: function() {
                for(var idx in ImgLoader.files) {
                    var file = ImgLoader.files[idx];
                    var src = file.url;
                    ImgLoader.cache[src] = new Image();
                    ImgLoader.cache[src].onload = ImgLoader.onImageLoaded;
                    ImgLoader.cache[src].src = src;
                }
            },
            onImageLoaded: function() {
                ImgLoader.readyFiles.push(this.src);
                if(ImgLoader.onProgressUpdate) ImgLoader.onProgressUpdate(ImgLoader.readyFiles.length);
                if(ImgLoader.onFinish && ImgLoader.readyFiles.length == ImgLoader.files.length) ImgLoader.onFinish();
            },
            onFinish: null,
            onProgressUpdate: null
        };


        ImgLoader.onProgressUpdate = function(done) {
            $('.progressInfo').text(done+'/'+ImgLoader.files.length);
        };


        var updateBackground = function() {
            var img = ImgLoader.files.pop();
            if(!img) {
                loadPhotos(timeParams);
                return;
            }
            $('#photo').css({backgroundImage:'url("'+img.url+'")'});
            var imageDate = new Date(img.timestamp*1000);
            $('.progressInfo').text(getFormatedTime(imageDate));
            setTimeout(updateBackground,500);
        };

        var loadPhotos = function(timeParams){
            if(!timeParams) return;
            $.get('api.php', {method: 'getPhotosForPeriod',params:JSON.stringify([timeParams.timeFrom, timeParams.period, false])}).done(function(response){
                ImgLoader.setFiles(response);
                ImgLoader.onFinish = function() {
                    updateBackground();
                };
                ImgLoader.startLoading();
            });
        };

        var loadData = function(timeParams,cb){
            $.get('api.php',{method:'getWeatherDataForPeriod', params:JSON.stringify([timeParams.timeFrom, timeParams.period, 12, timeParams.asc])}).done(function(response){
                if(cb)cb(response);
            });
        };

        var createDataBlock = function(data) {
            var $block = $('#dataBlockPrototype').clone().removeAttr('id');

            $block.find('.temperature').text(data.temperature+' ℃');
            $block.find('.humidity').text(data.humidity+'% rH');
            $block.find('.pressure').text(data.pressure+' mb');
            $block.find('.windDir').text(data.wind_dir_sym+' '+data.wind_dir+'°');
            $block.find('.windSpeed').text(data.wind_count+' m/s');

            var date = new Date(data.timestamp*1000);
            $block.find('.time').text(getFormatedTime(date));

            return $block;
        };

        var drawDataBlocks = function(response) {
            $('#dataBlocksContainer').html(null);

            $.each(response,function(idx,data){
                $('#dataBlocksContainer').append(createDataBlock(data));
            });
        }


        var updateData = function(cb) {
            loadData(timeParams,cb);
            setTimeout(function(){updateData(cb);},60*1000)
        };

        if(isMobileURL()) {
            timeParams.asc = false;
            updateData(function(r){
                drawDataBlocks(r);
            });
        } else {
            timeParams.asc = true;
            loadPhotos(timeParams);
            updateData(function(r){
                drawDataBlocks(r);
                updateChart(r);
            });
        }

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


    var getFormatedTime = function(date) {
        return (date.getHours()< 9 ? '0':'')+date.getHours() + ':' + (date.getMinutes()< 10 ? '0':'') + date.getMinutes();
    };


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

        $.each(response,function(idx,val){
            var date = new Date(val.timestamp*1000);

            labels.push(getFormatedTime(date));

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
    }

    $('.chartsBtn').click(function(){
        $('#charts').toggle();
        if(!$(this).hasClass('close')) updateChart();
    });

});

</script>

<style id="pageStyle"></style>

</head>

<body>
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
