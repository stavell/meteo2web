<?php
require_once('config.php');
use shumenxc as xc;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
    <script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-3807106-7', 'stavl.com');ga('send', 'pageview');</script>


    <script type="text/javascript">

        $(document).ready(function() {

            var timeParams = {
                timeFrom: '-7 hour',
                period:420
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
                $.get('api.php', {method: 'getPhotosForPeriod',params:[timeParams.timeFrom,timeParams.period,0]}).done(function(response){
                    ImgLoader.setFiles(response);
                    ImgLoader.onFinish = function() {
                        updateBackground();
                    };
                    ImgLoader.startLoading();
                });
            };

            loadPhotos(timeParams);

            var loadData = function(timeParams){
                $.get('api.php',{method:'getWeatherDataForPeriod', params:[timeParams.timeFrom,timeParams.period,12]}).done(function(response){

                    $('#dataBlocksContainer').html(null);

                    $.each(response,function(idx,data){
                        $('#dataBlocksContainer').prepend(createDataBlock(data));
                    });

                });
            }

            var createDataBlock = function(data) {
                var $block = $('#dataBlockPrototype').clone().removeAttr('id');

                $block.find('.temperature').text(data.temperature+' ℃');
                $block.find('.humidity').text(data.humidity+'%');
                $block.find('.pressure').text(data.pressure+' mb');
                $block.find('.windDir').text(data.wind_dir+'°');
                $block.find('.windSpeed').text(data.wind_count+' m/s');

                var date = new Date(data.timestamp*1000);
                $block.find('.time').text(getFormatedTime(date));

                return $block;
            }


            var getFormatedTime = function(date) {
                return (date.getHours()< 9 ? '0':'')+date.getHours() + ':' + (date.getMinutes()< 10 ? '0':'') + date.getMinutes();
            }


            var updateData = function() {
                loadData(timeParams);
                setTimeout(updateData,60*1000)
            }
            updateData();

        });

    </script>

    <style>
        html {
            background-color: #2d2d2d;
            background-color: #2d2d2d;
            background-repeat: no-repeat;
            background-size: cover;
            background-clip:content-box;
            -webkit-transition: opacity 0.25s ease-in-out;
            -moz-transition: opacity 0.25s ease-in-out;
            -o-transition: opacity 0.25s ease-in-out;
            transition: background-image 0.25s ease-in-out;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        #timeButtons {
            height: 30px;

        }

        #dataBlocksContainer {
            height: 100px;
        }

        #container {
            position: fixed;
            overflow: hidden;
            width: 1010px;
            bottom:0;
            left: 0;
            right: 0;
            margin: 0 auto;
            padding: 3px;
            background-color:rgba(255,255,255,0.3);
            border-top-right-radius: 10px;
            border-top-left-radius: 10px;
        }

        #blurMask {
            background: rgba(255,255,255,0.5);
            position: absolute;
            top:-20px;
            bottom: -20px;
            left: -20px;
            right: -20px;
            -webkit-filter: blur(10px);
            filter: blur(10px);

        }

        .dataBlock {
            float:left;
            margin: 3px;
            padding: 3px;
            width: 70px;
            height: 120px;
            border: 1px solid rgba(255,255,255,0.2);
            color: #ccc;
            font-size:14px;
            line-height: 1.4em;
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
        }

        .dataBlock > div {
            text-align: center;
            white-space: nowrap;
        }

        .progressInfo {
            position: fixed;
            top:0px;
            left:10px;
            background-color:rgba(255,255,255,0.3);
            padding:5px;
            color:#ccc;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }

    </style>



</head>

<body>

<div id="container">
    <div id="dataBlocksContainer"></div>
    <div style="clear:both;"></div>
</div>


<div style="display:none">
    <div id="dataBlockPrototype" class="dataBlock">
        <div class="windSpeed"></div>
        <div class="windDir"></div>
        <div class="humidity"></div>
        <div class="pressure"></div>
        <div class="temperature"></div>
        <div class="time"></div>
    </div>
</div>


<span class="progressInfo"></span>
</body>

</html>
