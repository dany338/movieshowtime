<?php
use macgyer\yii2materializecss\lib\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\web\JsExpression;

$url_load_billboard  = Url::to(['load-billboard'],true);

$script = <<< JS
const url_load_billboard = '$url_load_billboard';
const now         = new Date();
let currentMonth= now.getMonth();
currentMonth = ((currentMonth + 1) < 10) ? '0' + (currentMonth + 1) : (currentMonth + 1);
const currentYear = now.getFullYear();
const dayWeek     = now.getDay();
let dayMonth    = now.getDate();
dayMonth  = (dayMonth < 10) ? '0' + dayMonth : dayMonth;
(async () => { await moviebillboard(url_load_billboard, currentMonth, currentYear); })();
JS;
$this->registerJs($script, View::POS_READY, 'script-index');
?>
<div class="row">
  <div class="col s12">
    <div id="moviebillboard">

    </div>
  </div>
</div>
<?php
$this->registerJsFile("@web/js/moviebillboard.js",[
  'depends' => [ \yii\web\JqueryAsset::className() ],
  'position' => \yii\web\View::POS_READY
]);
?>
