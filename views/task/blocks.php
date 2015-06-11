<h4 style="text-align: center; margin-top: 7px" id="hint-header">
	<?php
		if ($Task->isNewRecord) {
	?>
	Жмите «мне нравится» на страницах ниже
	<?php
		} else {
	?> 
	<span class="text-success"><span class="glyphicon glyphicon-ok"></span> Проект успешно загружен! Продолжайте накрутку.</span>
	<?php
		}
	?>
</h4>
<hr style="margin-bottom: 30px">

<div id="like-blocks">
	<div id="div-blocker"></div>
	<div class="block-line">
		<div class="vliker-block pull-left" id="block-1" data-block-id="1">
			<?= $Tasks[0]->display() ?>
		</div>
		<div class="vliker-block" id="block-2" data-block-id="2">
			<?= $Tasks[1]->display() ?>
		</div>
		<div class="vliker-block pull-right" id="block-3" data-block-id="3">
			<?= $Tasks[2]->display() ?>
		</div>
	</div>	
	
	<div class="block-line">
		<div class="vliker-block pull-left" id="block-4" data-block-id="4">
			<?= $Tasks[3]->display() ?>
		</div>
		<div class="vliker-block" id="block-5" data-block-id="5">
			<?= $Tasks[4]->display() ?>
		</div> 
		<div class="vliker-block pull-right" id="block-6" data-block-id="6">
			<?= $Tasks[5]->display() ?>
		</div>
	</div>
</div>

<div class="row" id="row-process-control">
	<div class="col-sm-12" style="margin-top: 20px; text-align: center">
		<div class="btn-group btn-group-justified">
			<div class="btn-group" role="group">
	        <a href="#" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
	          <span class="glyphicon glyphicon-flag"></span>Пожаловаться <span class="caret"></span>
	        </a>
	        <ul class="dropdown-menu" role="menu">
	          <li><a onclick="reportTask()">Страница была недоступна</a></li>
	          <li><a onclick="reportTask()">Страница уже отображалась</a></li>
			  <li><a onclick="reportTask()">Спам</a></li>
	        </ul>
	      </div>
			<a class="btn btn-primary likes-count" style="cursor: default">
				<span class="glyphicon glyphicon-heart"></span><b id="likes-count"><?= $Task->needed ?></b>
			</a>
			<a onclick="stopConfirm()" class="btn btn-primary"><span class="glyphicon glyphicon-stop"></span>Завершить</a>
		</div>
	</div>
</div>