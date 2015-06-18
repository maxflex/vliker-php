<div style="margin-top: 20px">
<?php
	// если есть задачи, то отображаем их
	if ($Tasks) {
		foreach ($Tasks as $Task) {
?>
	<div class="row stats-row">
		<div class="col-sm-4">
			<div class="vliker-block fit-width">
				<a href="<?= $Task->url ?>" target="_blank">
					<?= $Task->statsDisplay() ?>
				</a>
			</div>
		</div>
		<div class="col-sm-8">
			<h4><?= $Task->Percentage->text ?>
				<span class="pull-right label label-default"><?= $Task->Percentage->label ?></span>
				<?= ($Task->Percentage->hint ? 
					"
					<span class='hint--top pull-right' data-hint='". $Task->Percentage->hint ."'>
						<span class='glyphicon glyphicon-info-sign pull-right'></span>
					</span>" : "") 
				?>
<!-- 			<span class="glyphicon glyphicon-option-horizontal pull-right text-primary"></span> -->
			</h4>
			<div class="progress <?= $Task->Percentage->class1 ?>">
			  <div class="progress-bar <?= $Task->Percentage->class2 ?>" 
				  <?= ($Task->Percentage->value ? "style='width: " . $Task->Percentage->value . "%'" : "") ?>>
				  <span><?= ($Task->Percentage->value ? $Task->Percentage->value . "%" : "") ?></span>
			  </div>
			</div>
		</div>
	</div>		
<?php
		}
	} else {
		// если нет задач
		?>
		<div class="well well-lg" style="text-align: center">
			<span class="glyphicon glyphicon-align-left" style="top: 1px; margin-right: 6px"></span>Нет статистики для отображения
			<div class="hint-text">
				Здесь будет отображаться статистика по накрученным лайкам, времени их поступления на страницу, места в очереди и другое.<br> 
				Приступите к накрутке, чтобы посмотреть статистику.
			</div>
		</div>
		<?php
	}
?>
</div>