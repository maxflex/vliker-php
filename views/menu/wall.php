<style>
	.comments-iframe {
		width: 700px; 
		height: 1725px;
	}
</style>
<center>
<!-- 	    <a href="#" onClick="gopage('instr',4)"><h2>Как пользоваться сайтом</h2></a> -->
<!--
	<br>
	
	<span style="font-size: 18px;">
	Если возникнут вопросы – 
	    <a href="http://vk.com/write<?=$_support_id?>" class="link">пишите в поддержку клиентов</a>
	    <img src="img/pm.png">
	
	<div id="vk_comments" style="padding: 10px"></div>
-->
	
	<iframe class="comments-iframe" scrolling="no" frameborder="no" src="http://graffitistudio.ru/vliker_comments.php"></iframe>
	</center>

<script>
// Айфрейм комментариев
comments_iframe = $(".comments-iframe");

// Как только айфрейм загрузился
comments_iframe.on("load", function() {

	$(window).on("scroll", function() {
		var scrolled =  ($(window).scrollTop() + $(window).height()) / $(document).height();
		
		if (scrolled > 0.8) {
			comments_iframe.height(comments_iframe.height() + 300);
		//	comments_iframe[0].contentWindow.WComments.showMore();
		}
	});
});
</script>