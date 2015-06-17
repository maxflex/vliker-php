var task_data=[]
var task_report_ids=[]
var animations=[[],[],[],[],[],[]]
var STATES={ME:'ME',MD:'MD',WB:'WB',WF:'WF',MM:'MM'}
var CORE_ENGINE='0x000'
var TS;var current_task=null;var id_current_block=null;function bindAfterLoad(){$(window).on("focus",function(){if(current_task!=null&&id_current_block!=null){if($.inArray(STATES.WF,animations[id_current_block])===-1){animations[id_current_block].push(STATES.WF)}
task_data.push({"id":current_task.id,"an":animations[id_current_block],"ce":CORE_ENGINE,"ts":new Date().getTime()-TS})
animations[id_current_block]=[]
id_current_block=null
CORE_ENGINE='0x000'
unblockDiv()}}).on("blur",function(){if(id_current_block!=null){console.log(id_current_block)
if($.inArray(STATES.WB,animations[id_current_block])===-1){animations[id_current_block].push(STATES.WB)
TS=new Date().getTime();}}}).on("mousemove",function(){if(CORE_ENGINE=='0x000'){CORE_ENGINE=(1.5*2)+'x'+Math.round(100+Math.random()*(9*100-100))}})
$(".vliker-block").on("mouseenter",function(){id_block=$(this).attr("data-block-id")-1
if($.inArray(STATES.ME,animations[id_block])===-1){animations[id_block].push(STATES.ME)}})}
function blockDiv(){$("#div-blocker").show()
$("#hint-header").html("<span class='text-error'><span class='glyphicon glyphicon-remove'></span> Поставьте лайк в открывшейся вкладке, чтобы продолжить накрутку</span>")}
function unblockDiv(){$("#div-blocker").hide()
$("#hint-header").html("Жмите «мне нравится» на страницах ниже")}
function addLikeHtml(){total_likes=parseInt($("#likes-count").html())
if(isNaN(total_likes)){total_likes=0}
$("#likes-count").html(total_likes+1)}
function clickTask(img,task){div=$(img).parent()
id_block=div.attr("data-block-id")-1
id_current_block=id_block
if($.inArray(STATES.MD,animations[id_block])===-1){animations[id_block].push(STATES.MD)}
current_task=task
openInNewTab(task.url)
addLikeHtml()
blockDiv()
div.trigger("mouseout")
loadNewTask(id_block+1)}
function reportTask()
{if(current_task!=null){if($.inArray(current_task.id,task_report_ids)!==-1){notifySuccess("Вы уже оставили жалобу на эту ссылку")}else{task_report_ids.push(current_task.id)
task_data.pop()
notifySuccess("Жалоба оставлена!")}}else{notifyError("Невозможно оставить жалобу – вы еще ничего не лайкнули")}}
function loadNewTask(id_block){ajaxStart();$.post("task/getNew",{},function(response){ajaxEnd()
$("#block-"+id_block).html(response)})}
function stopConfirm()
{likes_count=task_data.length+task_report_ids.length;if(likes_count<3){bootbox.alert(getIcon("caution")+"Поставьте хотя бы 3 лайка, чтобы завершить накрутку")
return}
bootbox.confirm({message:getIcon("heart")+"Вам будет накручено <span class='text-success'><b>+"
+likes_count+"</b>"+glyphIcon('heart glyphicon-middle')+"</span>",buttons:{confirm:{label:"Завершить"},cancel:{label:"Продолжить накрутку",className:"btn-default opacity-hover high-opacity pull-left"}},callback:function(result){if(result===true){stop()}}})}
function stop(){ajaxStart()
$.post("task/stop",{"task_data":task_data,"task_report_ids":task_report_ids},function(response){ajaxEnd()
task_data=[]
task_report_ids=[]
stopAnimation()})}