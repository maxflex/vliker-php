<style>
	.likes-iframe, iframe {
		display: none !important;
	}
	a:hover {
		color: white !important;
		text-decoration: none !important;
	}
</style>
<div class="banned-div">
	<div class="banned-div-content">
		<h2>Вы заморожены на <?= User::fromSession()->bannedTimeText() ?></h2>
		<div class="picture-div animated bounce">
			<img src="img/pictures/cry_dog.png">
		</div>
			<div>Вы временно заморожены за подозрительную активность</div>
			<div>Скорее всего, вы не ставили «мне нравится» пользователям</div>
			<div style="margin-top: 25px"><a href="http://vk.com/write<?= SUPPORT_ID ?>" target="_blank">
				Написать в поддержку клиентов</a>
			</div>
	</div>
</div>