<?php
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

require_once('config.inc.php');

try {
    \shumenxc\Users::handleOAuthLogin($_REQUEST);
    header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
} catch(Exception $e){}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <?php
        $photo = end(\shumenxc\Meteo2::getPhotosForPeriod('-1 hour'));
    ?>
    <meta property="fb:app_id" content="406234999472934"/>
    <meta property="og:url" content="http://stavl.com/meteo2/"/>
    <meta property="og:rich_attachment" content="true"/>
    <meta property="og:title" content="Shumen-XC Meteo"/>
    <meta property="og:site_name" content="Shumen-XC Meteo" />
    <meta property="og:image" content="<?=$photo['url'];?>"/>
    <meta property="og:updated_time" content="<?=$photo['timestamp'];?>"/>
    <meta property="og:description" content="
            Current view at <?=date('d.m.Y H:i',$photo['timestamp']);?>.
            Photos and weather information from Shumen plateau provided by Shumen-XC paragliding club."/>
    <title>Shumen-XC Meteo 2</title>

    <link href="assets/site.css" rel="stylesheet" type="text/css">

    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic' rel='stylesheet'
          type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>

</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-custom">
<script>
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '406234999472934',
            xfbml      : true,
            version    : 'v2.5'
        });
    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>

<nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand" href="#page-top">
                <i class="fa fa-cloud"></i> <span class="light">Shumen-XC</span> Meteo 2
            </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
            <ul class="nav navbar-nav">
                <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
                <li class="hidden">
                    <a href="#page-top"></a>
                </li>
                <li class="page-scroll">
                    <a href="#data">Data</a>
                </li>
                <!--<li class="page-scroll">-->
                <!--<a href="#gallery">Галерия</a>-->
                <!--</li>-->
                <!--<li class="page-scroll">-->
                <!--<a href="#info">Инфо</a>-->
                <!--</li>-->
                <li data-toggle="modal"  data-target="#settingsPanel">
                    <a><i class="fa fa-gears"></i>Settings</a>
                </li>
                <li data-toggle="modal">
                    <a class="fb-login"><i class="fa fa-facebook fa-fw"></i><span class="fb-title">login</span></a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>

<section class="intro">
    <div class="intro-body">
        <div class="container-fluid">

            <div class="row hidden-xs">
                <div class="col-xs-6 text-left">
                    <span class="btn photo-control-prev" viewer=".intro">
                        <i class="fa fa-4x fa-fast-backward animated"></i>
                    </span>
                </div>

                <div class="col-xs-6 text-right">
                    <span class="btn photo-control-next" viewer=".intro">
                        <i class="fa fa-4x fa-fast-forward animated"></i>
                    </span>
                </div>
            </div>


            <div class="row imageLoaderProgress">
                <div class="col-xs-10 col-xs-push-1">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                             aria-valuemax="100" style="width:0%"></div>
                    </div>
                </div>
            </div>


            <div class="row" style="position: absolute; bottom: 10px;">
                <div class="col-sm-12">
                    <blockquote class="text-left">
                        <ul class="list-unstyled list-inline">
                            <li>
                                <span class="btn pause-slideshow btn-download-image"><i class="fa fa-download animated"></i>&nbsp; download</span>
                            </li>
                            <li>
                                <span class="btn pause-slideshow btn-pin-image"><i class="fa fa-heart animated"></i>&nbsp; pin it!</span>
                            </li>
                            <li>
                                <span class="btn pause-slideshow"><i class="fa fa-clock-o animated"></i>&nbsp; <span
                                        class="photoInfo">--.--.---- --:--:--</span></span>
                            </li>
                        </ul>
                    </blockquote>
                </div>
            </div>

        </div>
    </div>
</section>

<section id="data" class="container content-section text-center">
<!---->
<!--    <div class="row">-->
<!---->
<!--        <div class="row">-->
<!--            <div class="col-lg-8 col-lg-offset-2">-->
<!--                <ul class="list-unstyled live-data">-->
<!--                    <li class="ws-time"></li>-->
<!--                    <li class="ws-windSpeed"><strong>...</strong></li>-->
<!--                    <li class="ws-windDir"><strong >...</strong></li>-->
<!--                    <li class="ws-temperature"><strong>...</strong></li>-->
<!--                </ul>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!---->
<!--        <div class="col-lg-8 col-lg-offset-2">-->
<!--            <table class="table" id="weather_data">-->
<!--            </table>-->
<!--        </div>-->
<!---->
<!--        <table style="display: none">-->
<!--            <tr id="weather_row_template">-->
<!--                <th scope="row" class="wr-time">12:37</th>-->
<!--                <td class="wr-windDir"> NNW (123.4°)</td>-->
<!--                <td class="wr-windSpeed">--.- m/s</td>-->
<!--                <td class="wr-temperature">12.3 °C</td>-->
<!--                <td class="wr-pressure">1034 mb</td>-->
<!--            </tr>-->
<!--        </table>-->
<!--    </div>-->


    <div class="modal fade" id="settingsPanel" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-left">Настройки</h4>
                </div>

                <div class="modal-body">

                    <div class="form form-horizontal timePickers">

                        <div class="form-group">
                            <div class="col-sm-3 text-right">
                                <label class="control-label">Начално време</label>
                            </div>


                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="time" class="form-control" name="timeFrom" placeholder="HH:MM"
                                           maxlength="5"/>
                                    <span class="input-group-addon"> <i class="fa fa-clock-o"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="dateFrom"/>
                                    <span class="input-group-addon"> <i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 text-right">
                                <label class="control-label">Крайно време</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="time" class="form-control" name="timeTo" placeholder="HH:MM"
                                           maxlength="5"/>
                                    <span class="input-group-addon"> <i class="fa fa-clock-o"></i></span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="dateTo"/>
                                    <span class="input-group-addon"> <i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 text-right">
                                <label class="control-label">Времезадържане</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="slideshowInterval" placeholder="500"
                                           value="500"/>
                                    <span class="input-group-addon">ms</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Затвори</button>
                    <button type="button" class="btn btn-primary modal-settings-ok-btn" data-dismiss="modal">Ок</button>
                </div>
            </div>
        </div>
    </div>

</section>
<!---->
<!---->
<!--<section id="info" class="container-fluid content-section text-center">-->
<!---->
<!--    <div class="row">-->
<!--        <div class="col-lg-8 col-lg-offset-2">-->
<!--            <ul class="list-inline banner-social-buttons">-->
<!--                <li><a href="http://forums.shumen-xc.org" target="_blank" class="btn btn-default btn-lg"><i-->
<!--                            class="fa fa-link fa-fw"></i> <span class="network-name">forums.shumen-xc.org</span></a>-->
<!--                </li>-->
<!--                <li><a href="http://facebook.com/ShumenXC.Paragliding" target="_blank" class="btn btn-default btn-lg"><i-->
<!--                            class="fa fa-facebook fa-fw"></i> <span class="network-name">ShumenXC.Paragliding</span></a>-->
<!--                </li>-->
<!--            </ul>-->
<!---->
<!--        </div>-->
<!--        <div id="map" class="hidden-xs"></div>-->
<!--    </div>-->
<!---->
<!--</section>-->


<!-- Core JavaScript Files -->
<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
<script src="assets/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="assets/datepicker/js/bootstrap-datepicker.min.js"></script>

<script type="text/javascript"
        src="//maps.googleapis.com/maps/api/js?key=AIzaSyA8TEv7TWyNr_eB8EXPEfA1PSeCnxsY0OY&sensor=false"></script>
<script>(function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
        a = s.createElement(o), m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-3807106-7', 'stavl.com');
    ga('send', 'pageview');</script>

<!-- Custom Theme JavaScript -->
<script src="js/Server.js"></script>
<script src="js/site.js"></script>
<script src="js/app.js"></script>


</body>

</html>
