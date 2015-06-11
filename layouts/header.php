<!DOCTYPE html>
<html>
  <head>
  	<meta charset="utf-8"> 
    <title><?= $this->_html_title ?></title>
    <meta name="keywords" content="накрутка сердечек лайков мне нравится вконтакте бесплатно онлайн автоматически обмен подписчики в группу паблик программа vkontakte vk.com фотографии видеозаписи записи стена посты опросы вк" />
	<meta name="description" content="Накрутка сердечек и лайков ВК бесплатно онлайн, накрутка подписчиков в группу/паблик, опросов, фотографий и комментариев" />
    <?php
	    // Дебаг
	    if (DEBUG) {
		    echo '<base href="http://localhost:8080'. BASE_ADDON .'">';
	    } else {
		    echo '<base href="'. BASE_ADDON .'">';
	    }
	?>
    <link href="css/copypaste.css" rel="stylesheet">
    <link href="css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="css/jquery.timepicker.css" rel="stylesheet">
	<link rel="stylesheet" href="css/hint.css"></link>
    <link href="css/bootstrap.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/animate.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/style.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/ng-showhide.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<link href="css/nprogress.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<?= $this->_css_additional ?>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/retina.min.js"></script>
	<script type="text/javascript" src="js/nprogress.js"></script>
<!--
	<script type="text/javascript" src="js/mask.js"></script>
	<script type="text/javascript" src="js/inputmask.js"></script>
-->
	<script type="text/javascript" src="js/angular.js"></script>
	<script type="text/javascript" src="js/angular-animate.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootbox.min.js"></script>
	<script type="text/javascript" src="js/notify.js"></script>
	<script type="text/javascript" src="js/moment.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-datepicker.min.js"></script>
<!-- 	<script type="text/javascript" src="js/jquery.datetimepicker.js"></script> -->
	<script type="text/javascript" src="js/jquery.timepicker.js"></script>
	<script type="text/javascript" src="js/main.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/engine.js?ver=<?= settings()->version ?>"></script>


    <?= $this->_js_additional ?>
  </head>
  <body>