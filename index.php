<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">


<style>
html {
background-color: #2d2d2d;            
background-repeat: no-repeat;
            background-size: cover;
            background-clip: border-box;
        }
</style>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-3807106-7', 'stavl.com');
  ga('send', 'pageview');

</script>

<script type="text/javascript" src="jquery-1.9.1.min.js"></script>

<script type="text/javascript">


$(document).ready(function() {

function preload(arrayOfImages) {
    $(arrayOfImages).each(function(){
        $('<img/>')[0].src = this;
    });
}

<?php
$aFiles = scandir('upload/',true);
$sFiles = '"upload/'.implode('","upload/',array_slice($aFiles,0,10)).'"';

?>


 images = [<?php echo $sFiles;?>];
 
preload(images);

i=10;

t = setInterval(function(){
if(i==-1) i=10;
//$('#time').text(i+' minutes ago');
//$('#image').attr({'src':images[i]});
$('html').css({'background-image':'url("'+images[i]+'")'});

i--;
},600);


});

</script>


<body>
<span id="time"></span>
<br/>
<img id="image" />
</body>
</html>
