<?php
    require_once('config.php');
    use shumenxc as xc;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">

    <script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>


    <style>
    html {
    background-color: #2d2d2d;
    background-repeat: no-repeat;
                background-size: cover;
                background-clip: border-box;
            }
    </style>

    <script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-3807106-7', 'stavl.com');ga('send', 'pageview');</script>

    <script type="text/javascript">

    $(document).ready(function() {

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
                fetchFiles();
                return;
            }
            $('html').css({backgroundImage:'url("'+img.url+'")'});
            var time = new Date(img.timestamp*1000);
            $('.progressInfo').text(time.toTimeString());
            setTimeout(updateBackground,400);
        };


        ImgLoader.onFinish = function() {
            updateBackground();
        };

        var fetchFiles = function(){
            $.get('api.php', {method: 'getPhotosForInterval',params:['<?=strtotime("-3 hour");?>',null,0]}).done(function(response){
                ImgLoader.setFiles(response);
                ImgLoader.startLoading();
            });
        };

        fetchFiles();
    });

    </script>

</head>

<body>

<span class="progressInfo" style="background-color:rgba(0,0,0,0.3); padding:5px; color:#ccc; border-radius: 5px;"></span>



</body>

</html>
