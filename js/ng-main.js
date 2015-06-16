
angular.module("VLiker",["ngAnimate"]).filter('reverse',function(){return function(items){return items.slice().reverse();};}).controller("MainCtrl",function($scope){$scope.example_links=["http://vk.com/photo236886_332","http://vk.com/wall123123552"]
$scope.example_link=$scope.example_links[Math.floor(Math.random()*$scope.example_links.length)];$scope.goExample=function(){$scope.example_clicked=true
$scope.url=$scope.example_link}
$scope.goMenu=function(id_menu){if(!$scope.id_menu){topAnimation();}
if($scope.id_menu!=id_menu){$scope.id_menu=id_menu}else{downAnimation()
$scope.id_menu=null
$scope.show_wall=false
$scope.show_stats=false
$scope.show_store=false
$scope.show_instr=false
$scope.$apply()
return}
ajaxStart();switch(id_menu){case 1:{$scope.show_wall=false
$("#content").load("menu/wall",function(response){ajaxEnd()
$("#row-content").show().addClass("fadeInDown")
console.log(response);});break}
case 2:{$("#content").load("menu/construct",function(response){ajaxEnd()
$("#row-content").show().addClass("fadeInDown")
console.log(response);});$scope.show_stats=false
break}
case 3:{$("#content").load("menu/construct",function(response){ajaxEnd()
$("#row-content").show().addClass("fadeInDown")
console.log(response);});$scope.show_store=false
break}
case 4:{$("#content").load("menu/construct",function(response){ajaxEnd()
$("#row-content").show().addClass("fadeInDown")
console.log(response);});$scope.show_instr=false
break}}
$scope.$apply()}
$scope.start=function(){ajaxStart()
$.post("task/blocks",{"url":$scope.url},function(response){ajaxEnd()
object=toJSON(response)
if(object===false){startAnimation()
showContent(response)
bindAfterLoad()}else{$scope.url_error=object.error;$scope.$apply()}})}})