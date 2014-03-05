<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>

    <script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-3807106-7', 'stavl.com');ga('send', 'pageview');</script>

    <script type="text/javascript">

        $(document).ready(function() {

            var onCSSLoad = function() {

                var timeParams = {
                    timeFrom: '-1 hour',
                    period:60,
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
                    $('html').css({backgroundImage:'url("'+img.url+'")'});
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

                var loadData = function(timeParams){
                    $.get('api.php',{method:'getWeatherDataForPeriod', params:JSON.stringify([timeParams.timeFrom, timeParams.period, 12, timeParams.asc])}).done(function(response){

                        $('#dataBlocksContainer').html(null);

                        $.each(response,function(idx,data){
                            $('#dataBlocksContainer').append(createDataBlock(data));
                        });

                    });
                };

                var createDataBlock = function(data) {
                    var $block = $('#dataBlockPrototype').clone().removeAttr('id');

                    $block.find('.temperature').text(data.temperature+' ℃');
                    $block.find('.humidity').text(data.humidity+'% rH');
                    $block.find('.pressure').text(data.pressure+' mb');
                    $block.find('.windDir').text(data.wind_dir+'°');
                    $block.find('.windSpeed').text(data.wind_count+' m/s');

                    var date = new Date(data.timestamp*1000);
                    $block.find('.time').text(getFormatedTime(date));

                    return $block;
                };


                var getFormatedTime = function(date) {
                    return (date.getHours()< 9 ? '0':'')+date.getHours() + ':' + (date.getMinutes()< 10 ? '0':'') + date.getMinutes();
                };


                var updateData = function() {
                    loadData(timeParams);
                    setTimeout(updateData,60*1000)
                };


                if(isMobileURL()) {
                    timeParams.asc = false;
                    updateData()
                } else {
                    timeParams.asc = true;
                    loadPhotos(timeParams);
                    updateData();
                }

            };


            var isMobileURL = function(){
                return location.hash.toLowerCase() == '#m' ||  /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ? true : false;
            }

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
        });

    </script>

    <style id="pageStyle"></style>

</head>

<body>

<div id="container">
    <div id="dataBlocksContainer">Thinking...</div>
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
</body>

</html>
