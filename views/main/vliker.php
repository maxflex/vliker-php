<div id="animation-block">
	<div class="row" style="height: 200px" id="row-ads">
		<div class="col-sm-12">
		</div>
	</div>
	<div class="row" id="row-logo">
		<div class="col-sm-2"></div>
		<div class="col-sm-8" style="text-align: center">
			<img src="img/logo/logo.png" id="main-logo">
		</div>
	</div>
	<div class="row" id="row-controls">
		<div class="col-sm-2"></div>
		<div class="col-sm-8" id="#main-controls-div">
			<div class="input-group">
				<input type="text" class="form-control" id="main-input" ng-model="url">
				<span class="input-group-btn">
					<button class="btn btn-primary" type="button" id="start-button" ng-click="start()">Накрутить!</button>
				</span>
	    	</div>
		</div>
	</div>
	<div class="row" id="row-example">
		<div class="col-sm-2"></div>
		<div class="col-sm-8 example-text">
			<span ng-hide="url_error || example_clicked" class="text-primary animate-show">Например, <span id="example-link" ng-click="goExample()">{{example_link}}</span></span>
			<span ng-show="url_error" class="text-error animate-show">
				<span class="glyphicon glyphicon-remove"></span>{{url_error}}
			</span>
		</div>
	</div>
</div>
<div class="row row-menu" id="row-menu">
	<div class="col-sm-2"></div>
	<div class="col-sm-2">
		<img src="img/icons/Message-Edit.png" 
			ng-src="{{(show_wall && id_menu == 1) && 'img/icons/ArrowUp@2x.png' || 'img/icons/Message-Edit@2x.png'}}"
			ng-mouseenter="show_wall=true" ng-mouseleave="show_wall=false" ng-click="goMenu(1)" 
			ng-class="{'no-opacity': id_menu == 1, 'hovered' : show_wall}">
		<div ng-show="show_wall" class="animate-show-down menu-label" id="menu-label-1">{{id_menu == 1 && 'Назад' || 'Стена'}}</div>
	</div>
	<div class="col-sm-2">
		<img src="img/icons/Heart.png" 
			ng-src="{{(show_stats && id_menu == 2) && 'img/icons/ArrowUp@2x.png' || 'img/icons/Heart@2x.png'}}"
			ng-mouseenter="show_stats=true" ng-mouseleave="show_stats=false" ng-click="goMenu(2)" 
			ng-class="{'no-opacity': id_menu == 2, 'hovered' : show_stats}">
		<div ng-show="show_stats" class="animate-show-down menu-label" id="menu-label-2">{{id_menu == 2 && 'Назад' || 'Статистика'}}</div>
	</div>
	<div class="col-sm-2">
		<img src="img/icons/Shopping-Cart.png" 
			ng-src="{{(show_store && id_menu == 3) && 'img/icons/ArrowUp@2x.png' || 'img/icons/Shopping-Cart@2x.png'}}"
			ng-mouseenter="show_store=true" ng-mouseleave="show_store=false" ng-click="goMenu(3)" 
			ng-class="{'no-opacity': id_menu == 3, 'hovered' : show_store}">
		<div ng-show="show_store" class="animate-show-down menu-label" id="menu-label-3">{{id_menu == 3 && 'Назад' || 'Магазин'}}</div>
	</div>
	<div class="col-sm-2">
		<img src="img/icons/Library-Books.png" 
			ng-src="{{(show_instr && id_menu == 4) && 'img/icons/ArrowUp@2x.png' || 'img/icons/Library-Books@2x.png'}}"
			ng-mouseenter="show_instr=true" ng-mouseleave="show_instr=false" ng-click="goMenu(4)" 
			ng-class="{'no-opacity': id_menu == 4, 'hovered' : show_instr}">
		<div ng-show="show_instr" class="animate-show-down menu-label" id="menu-label-4">{{id_menu == 4 && 'Назад' || 'Как пользоваться'}}</div>
	</div>
</div>

<div class="row animated" id="row-content">
	<div class="col-sm-2"></div>
	<div class="col-sm-8" id="content">
	</div>
</div>