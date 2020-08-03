<?php

use macgyer\yii2materializecss\lib\Html;
use macgyer\yii2materializecss\widgets\form\Select;
use yii\web\View;
use yii\web\JsExpression;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use kartik\widgets\DynaGrid;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use kartik\grid\EditableColumn;
use yii2mod\alert\Alert;
use kartik\select2\Select2;
use kartik\editable\Editable;
use dektrium\user\models\User;
use backend\models\Movie;

$this->title = 'Movies';
$this->params['breadcrumbs'][] = $this->title;

$url_export          = Url::to(['export'], true);
$url_subscriptions   = Url::to(['get-subscriptions'],true);
$url_moviebillboards = Url::to(['get-moviebillboards'],true);

$script = <<< JS
const url_export          = '$url_export';
const url_subscriptions   = '$url_subscriptions';
const url_moviebillboards = '$url_moviebillboards';

const htmlLoader = () => {
  let html  = '<div id="loader" class="progress" style="background-color: #E57373 !important;">';
      html += ' <div class="indeterminate"></div>';
      html += '</div>';
  return html;
};

jQuery('.tooltipped').tooltip({delay: 50, html: true});

jQuery('#export-all-movies').on('click', function() {
  const year = jQuery('#export-date').val();
  window.location.href = url_export + '?year=' + year;
});

jQuery('#modal-subscriptions').modalm({
  onOpenStart: async function(modal, trigger) {
    jQuery('#modal-subscriptions').removeAttr("tabindex");
    let html = htmlLoader();
    jQuery('#table-subscriptions').html('<tr><td colspan="3">' + html + '</td></tr>');
    const movie = jQuery(trigger).data('movie');
    await jQuery.ajax({
      type: "POST",
      url: url_subscriptions,
      data: { movie: movie },
      dataType: 'json',
      success: function(data) {
        var obj    = JSON.parse(JSON.stringify(data));
        console.log(obj);
        if(obj.exito == 1) {
          let html = '';
          for(var index = 0; index < obj.data.length; index++) {
            const element = obj.data[index];
            html += '<tr><td>' + element.user + '</td>';
            html += '<td>' + element.created_at + '</td>';
            html += '<td>' + element.notification + '</td></tr>';
          }
          jQuery('#table-subscriptions').html(html);
        } else {
          const html = '<tr><td colspan="3">No records</td></tr>';
          jQuery('#table-subscriptions').html(html);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        toastr.error('Error loading the subscriptions !Please contact to administrator¡ ', 'ERROR', {closeButton: true, 'progressBar': true, preventDuplicates: true, positionClass: 'toast-bottom-right'});
      }
    });
  }
});

jQuery('#modal-moviebillboards').modalm({
  onOpenStart: async function(modal, trigger) {
    jQuery('#modal-moviebillboards').removeAttr("tabindex");
    let html = htmlLoader();
    jQuery('#table-moviebillboards').html('<tr><td colspan="3">' + html + '</td></tr>');
    const movie = jQuery(trigger).data('movie');
    await jQuery.ajax({
      type: "POST",
      url: url_moviebillboards,
      data: { movie: movie },
      dataType: 'json',
      success: function(data) {
        var obj    = JSON.parse(JSON.stringify(data));
        console.log(obj);
        if(obj.exito == 1) {
          let html = '';
          for(var index = 0; index < obj.data.length; index++) {
            const element = obj.data[index];
            html += '<tr><td>' + element.start_date + '</td>';
            html += '<td>' + element.end_date + '</td>';
            html += '<td>' + element.statusLabel + '</td></tr>';
          }
          jQuery('#table-moviebillboards').html(html);
        } else {
          const html = '<tr><td colspan="3">No records</td></tr>';
          jQuery('#table-moviebillboards').html(html);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        toastr.error('Error loading the moviebillboards !Please contact to administrator¡ ', 'ERROR', {closeButton: true, 'progressBar': true, preventDuplicates: true, positionClass: 'toast-bottom-right'});
      }
    });
  }
});

// jQuery('.subscriptions').on('click', async function(e) {
//   e.preventDefault();
//   jQuery('.tooltipped').tooltip();

//   e.stopPropagation();
// });

// jQuery('.moviebillboards').on('click', async function(e) {
//   e.preventDefault();
//   jQuery('.tooltipped').tooltip();

//   e.stopPropagation();
// });

JS;
$this->registerJs($script, View::POS_READY, 'init-list');
echo Alert::widget([]);
$date = (int)date('Y');
$arrays_dates[""] = 'Export all the years';
for ($i=$date - 1; $i <= $date; $i++) {
  $arrays_dates[$i] = $i;
}
?>

<!-- Modal subscriptions -->
<div id="modal-subscriptions" class="modal modal-fixed-footer">
  <div class="modal-content">
    <h4>Subscriptions</h4>
    <table class="responsive-table">
      <thead>
        <tr>
            <th>User</th>
            <th>Created at</th>
            <th>Notification</th>
        </tr>
      </thead>
      <tbody id="table-subscriptions">
        <tr>
          <td colspan="3">No records</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="modal-footer">
    <a href="javascript:void(0);" class="modal-close waves-effect waves-green btn-flat">Aceptar</a>
  </div>
</div>

<!-- Modal moviebillboards -->
<div id="modal-moviebillboards" class="modal modal-fixed-footer">
  <div class="modal-content">
    <h4>Movie billboards</h4>
    <table class="responsive-table">
      <thead>
        <tr>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
        </tr>
      </thead>
      <tbody id="table-moviebillboards">
        <tr>
          <td colspan="3">No records</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="modal-footer">
    <a href="javascript:void(0);" class="modal-close waves-effect waves-green btn-flat">Aceptar</a>
  </div>
</div>

<div class="movie-index">
  <h4 class="header"><?= Html::encode($this->title) ?></h4>
  <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
  <div class="row">
    <div class="col s12 m3 l3">
      <?= Html::a('EXPORT ALL THE MOVIES <i class="material-icons" style="vertical-align: middle;">cloud_download</i>', null, [
        'type'          => 'btn btn-default',
        'title'         => Yii::t('yii', 'Export all the movies'),
        'class'         => 'waves-effect waves-light btn green lighten-2 tooltipped',
        'data-position' => 'top',
        'data-delay'    => '50',
        'data-tooltip'  => 'Export data',
        'href'          => 'javascript:void(0);',
        'id'            => 'export-all-movies',
        'data' => [
          'method' =>'post',
          'params'=>['id'=>0],
          'href' => 'http://localhost/movieshowtime/backend/web/movie/export'
        ]
      ]); ?>
    </div>
    <div class="col s12 m3 l3">
      <?= Select2::widget([
          'name'    => 'export-date',
          'data'    => $arrays_dates,
          'language' => 'es',
          'theme' => Select2::THEME_KRAJEE,
          'options' => [
            'id'          => 'export-date',
            'width'       => '200',
            'style'       => 'margin: 1.52rem 0 .912rem 0; !important; width: 200px !important;',
            'placeholder' => 'Export all the years...',
          ],
          'pluginOptions' => [
            'multiple' => false,
            'allowClear' => true
          ],
      ]); ?>
    </div>
  </div>

</div>
<?php
$gridColumns = [
  [
    'class' => 'kartik\grid\ExpandRowColumn',
    'width' => '3%',
    'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
    },
    'detail' => function ($model, $key, $index, $column) {
        return Yii::$app->controller->renderPartial('_expand-row-details', ['model' => $model]);
    },
    'headerOptions' => ['class' => 'kartik-sheet-style'],
    'expandOneOnly' => true,
    'expandIcon'    => '<i class="material-icons">arrow_right</i>',
    'collapseIcon'  => '<i class="material-icons">arrow_drop_down</i>',
    'expandTitle'   => 'Open Detail Movie',
    'collapseTitle' => 'Close Detail Movie'
  ],
  [
    'class'         => '\kartik\grid\ActionColumn',
    'viewOptions'   => ['label'=>'<i class="material-icons">visibility</i>'],
    'updateOptions' => ['label'=>'<i class="material-icons">edit</i>'],
    'deleteOptions' => ['label'=>'<i class="material-icons">delete</i>'],
    'width'         => '5%',
    'template'      => '{view}',
    'buttons' => [
      'view' => function ($url, $model) {
        return Html::a('<i class="material-icons circle">visibility</i>', $url, [
                  'class' => 'btn-floating waves-effect cyan tooltipped',
                  'data-position' => 'top',
                  'data-delay' => '50',
                  'data-tooltip' => '<p style="text-align:justify;">View movie:<br><span class="amber-text text-accent-3"><b>'.$model->id.'</b></span></p>'
        ]);
      },
    ],
    'urlCreator' => function ($action, $model, $key, $index) {
        switch ($action) {
          case 'view':
            $url = Url::to('@hostback/movie/view?id='.$model->id, true);
          break;
      }
      return $url;
    },
    'visibleButtons' => [
      'view'   => true,
    ]
  ],
  [
    'vAlign'         => 'middle',
    'format'         => 'raw',
    'attribute'      => 'id',
    'label'          => Yii::t('yii', '# Movie'),
    'noWrap'         => true,
    'contentOptions' => function ($model, $key, $index, $column) {
      $class = $model->getColorRow();
      return ['width'=>'5%', 'style' => 'text-align: justify; white-space: normal !important; overflow-wrap: break-word; width: 5%; padding: 1rem; border-radius: 10px; border-top: 0.5px solid #ce8e7b; border-bottom: 0.5px solid #ce8e7b; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23); position: relative; overflow: hidden; transition: 0.2s ease-in-out all;', 'class' => $class ];
    },
    'width'          => '5%',
    'value'    => function($model, $key, $index, $widget) {
      return $model->id.' themoviedb ID: '.$model->moviedb_id;
    },
    'filter' => true,
    'filterInputOptions'=>['placeholder'=>'Search by ID...', 'tab-index' => 2],
  ],
  [
    'format'         => 'raw',
    'attribute'      => 'name',
    'label'          => Yii::t('yii', 'Name'),
    'noWrap'         => true,
    'contentOptions' => function ($model, $key, $index, $column) {
      $class = $model->getColorRow();
      return ['width'=> '8%', 'style' => 'text-align: justify; white-space: normal !important;', 'class' => $class ];
    },
    'width'          => '8%',
    'value'          => function($model, $key, $index, $widget) {
      $linkEye = Html::a('<i class="material-icons">visibility</i> '.$model->name, null, [
        'class'  => 'amber-text text-accent-4 waves-effect waves-light',
        'target' => '_blank',
        'href'   => $model->moviedb_image
      ]);
      return $linkEye;
    },
    'filter' => true,
    'filterInputOptions'=>['placeholder'=>'Search by name...', 'tab-index' => 3],
  ],
  [
    'format'         => 'raw',
    'attribute'      =>'user_first_id',
    'label'          => Yii::t('yii', 'User first subscription'),
    'noWrap'         => true,
    'contentOptions' => function ($model, $key, $index, $column) {
      $class = $model->getColorRow();
      return ['width'=>'8%', 'style' => 'text-align: justify; white-space: normal !important;', 'class' => $class ];
    },
    'width'          => '8%',
    'value'          => function($model, $key, $index, $widget) {
      $user_first = '<span class="red-text text-lighten-3">(no asignado)</span>';
      if($model->user_first_id !== null) {
        $user       = User::findOne($model->user_first_id);
        $user_first = $user->profile->name;
      }
      return $user_first;
    },
    'filterType' => GridView::FILTER_SELECT2,
    'filter'     => User::getUsersMoviesShowTimeFinder(),
    'filterWidgetOptions'=>[
        'pluginOptions'=>['allowClear'=>true],
    ],
    'filterInputOptions'=>['placeholder'=>'Search by user...'],
  ],
  [
    'format'         => 'raw',
    'attribute'      => 'created_at',
    'label'          => Yii::t('yii', 'Created at / Updated at'),
    'noWrap'         => true,
    'contentOptions' => function ($model, $key, $index, $column) {
      $class = $model->getColorRow();
      return ['width'=> '15%', 'style' => 'text-align: justify; white-space: normal !important;', 'class' => $class ];
    },
    'width'          => '15%',
    'value'          => function ($model, $key, $index, $widget) {
        $html = '<b>Created at:</b><br><span class="grey-text">'.date('d M, Y g:i A', strtotime($model->created_at)).'</span>';
        $html .= '<br><b>Updated at:</b><br><span class="grey-text">'.date('d M, Y g:i A', strtotime($model->updated_at)).'</span>';
        return $html;
    },
    'filterType' => GridView::FILTER_DATE,
    'filter'     => true,
    'filterWidgetOptions'=>[
        'pluginOptions'=>[
          'autoclose'=>true,
          'format' => 'yyyy-mm-dd',
          'todayHighlight' => true,
        ],
    ],
    'filterInputOptions'=>['placeholder'=>'Search by Created at...', 'tab-index' => 6],
  ],
  [
    'vAlign'         => 'middle',
    'format'         => 'raw',
    'attribute'      => 'status',
    'label'          => Yii::t('yii', 'Status'),
    'noWrap'         => true,
    'contentOptions' => function ($model, $key, $index, $column) {
      $class = $model->getColorRow();
      return ['width'=> '10%', 'style' => 'text-align: justify; white-space: normal !important;', 'class' => $class ];
    },
    'width'          => '10%',
    'value'          => function($model, $key, $index, $widget) {
      return $model->getStatus();
    },
    'filterType' => GridView::FILTER_SELECT2,
    'filter'     => Movie::getStatusMovies(),
    'filterWidgetOptions'=>[
        'pluginOptions'=>['allowClear'=>true, 'tab-index' => 7],
    ],
    'filterInputOptions'=>['placeholder'=>'Search by status...', 'tab-index' => 7],
  ],
];
$fullExportMenu = ExportMenu::widget([
  'dataProvider' => $dataProvider,
  'columns' => $gridColumns,
  'target' => ExportMenu::TARGET_BLANK,
  'pjaxContainerId' => 'kv-pjax-container',
  'exportContainer' => [
      'class' => 'btn-group mr-2'
  ],
  'columnSelectorOptions' => [
    'class' => 'waves-effect waves-light btn red lighten-2',
  ],
  'noExportColumns' => [
    0 => 'Columna 1',
    1 => 'Acciones',
  ],
  'dropdownOptions' => [
    'label' => 'Full',
    'class' => 'waves-effect waves-light btn red lighten-2',
    'data-target' => 'w4',
    'itemsBefore' => [
        '<div class="dropdown-header">Export all the data</div>',
    ],
    'menuOptions' => [
      'class' => 'dropdown-menu dropdown-menu-position',
    ],
  ],
  'exportConfig' => [
    ExportMenu::FORMAT_HTML    => [
      'label' => '<i class="material-icons teal-text text-darken-1" style="vertical-align: middle;">description</i> HTML',
      'icon' => '',
    ],
    ExportMenu::FORMAT_CSV     => [
      'label' => '<i class="material-icons light-blue-text text-darken-1" style="vertical-align: middle;">file_copy</i> CSV',
      'icon' => '',
    ],
    ExportMenu::FORMAT_TEXT    => [
      'label' => '<i class="material-icons grey-text text-darken-1" style="vertical-align: middle;">file_copy</i> Text',
      'icon' => '',
    ],
    ExportMenu::FORMAT_PDF     => [
      'label' => '<i class="material-icons red-text text-darken-1" style="vertical-align: middle;">picture_as_pdf</i> PDF',
      'icon' => '',
    ],
    ExportMenu::FORMAT_EXCEL   => [
      'label' => '<i class="material-icons green-text text-darken-1" style="vertical-align: middle;">cancel_presentation</i> Excel 95 +',
      'icon' => '',
    ],
    ExportMenu::FORMAT_EXCEL_X => [
      'label' => '<i class="material-icons green-text text-darken-1" style="vertical-align: middle;">cancel_presentation</i> Excel 2007+',
      'icon' => '',
    ],
  ],
]);
?>
<?= GridView::widget([
    'id'              => 'kv-grid-movies',
    'options'         => ['style' => 'width:150%;'],
    'tableOptions'    => ['style' => 'width:150%;'],
    'dataProvider'    => $dataProvider,
    'autoXlFormat'    => true,
    'filterModel'     => $searchModel,
    'columns'         => $gridColumns,
    'resizableColumns'=> true,
    'resizeStorageKey'=> Yii::$app->user->identity->id . '-' . date("m"),
    'panelTemplate'   => "{pager}\n{panelHeading}\n{panelBefore}\n{items}\n{panelAfter}\n{panelFooter}",
    'containerOptions'=> ['style'=>'overflow: auto'], // only set when $responsive = false
    'headerRowOptions'=> ['class'=>'kartik-sheet-style'],
    'filterRowOptions'=> ['class'=>'kartik-sheet-style'],
    'showPageSummary' => true,
    'pjax'            => true,
    'bordered'        => true,
    'striped'         => true,
    'condensed'       => true,
    'responsive'      => true,
    'hover'           => true,
    'pager' => [
      'firstPageLabel' => '<i class="material-icons" style="display: inline-block; font-size: 1.2rem; padding: 0 10px; line-height: 30px;">first_page</i>',
      'lastPageLabel'  => '<i class="material-icons" style="display: inline-block; font-size: 1.2rem; padding: 0 10px; line-height: 30px;">last_page</i>'
    ],
    'toolbar' => [
      '{export}',
      '{toggleData}',
      $fullExportMenu,
      [
        'content' =>
          Html::a('<i class="material-icons" class="">autorenew</i>', ['index'], [
            'class'         => 'waves-effect waves-light btn red lighten-2 tooltipped',
            'title'         => Yii::t('yii', 'Reset list'),
            'data-pjax'     => 0,
            'data-position' => 'top',
            'data-delay'    => '50',
            'data-tooltip'  => '<p style="text-align:justify;">Reset list</p>'
          ]),
      ],
    ],
    'panel'=>[
        'type'=>GridView::TYPE_DANGER,
        'headingOptions' => ['class'=>'panel-heading blue-grey darken-1 white-text'],
        'heading'        => '<i class="tiny material-icons">settings</i> '.Yii::t('yii', 'Movies'),
        'before'         => ''
    ],
    'toolbarContainerOptions' => [
      'class' => 'btn-toolbar kv-grid-toolbar toolbar-container pull-left',
      'style' => 'float: left !important;'
    ],
    'toggleDataOptions'=>[
      'minCount' => 10,
      'all' => [
        'class' => 'waves-effect waves-light btn red lighten-2'
      ],
      'page' => [
        'class' => 'waves-effect waves-light btn red lighten-2'
      ]
    ],
    'export' => [
      'options' => ['class' => 'dropdown-trigger waves-effect waves-light btn red lighten-2', 'data-target' => 'w6'],
      'menuOptions' => ['class' => 'dropdown-menu dropdown-menu-position']
    ],
    'exportConfig' => [
      GridView::HTML => [
        'filename' => 'Movies-'.date('Y-m-d'),
        'options'  => ['title' => 'Download File HTML - Web Movies'],
        'icon'     => '',
        'label'    => '<i class="material-icons teal-text text-darken-1" style="vertical-align: middle;">description</i> HTML',
      ],
      GridView::CSV => [
        'filename' => 'Movies-'.date('Y-m-d'),
        'options'  => ['title' => 'Download File CSV - Excel Movies'],
        'icon'     => '',
        'label'    => '<i class="material-icons light-blue-text text-darken-1" style="vertical-align: middle;">file_copy</i> CSV',
        'config'   => [
          'colDelimiter' => ";",
          'rowDelimiter' => "\r\n",
        ],
      ],
      GridView::TEXT => [
        'filename' => 'Movies-'.date('Y-m-d'),
        'options'  => ['title' => 'Download File Texto-plano Movies'],
        'icon'     => '',
        'label'    => '<i class="material-icons grey-text text-darken-1" style="vertical-align: middle;">file_copy</i> Text',
        'mime'     => 'text/plain',
        'config'   => [
            'colDelimiter' => "\t",
            'rowDelimiter' => "\r\n",
        ]
      ],
      GridView::EXCEL => [
        'filename' => 'Movies-'.date('Y-m-d'),
        'options'  => ['title' => 'Download File EXCEL Movies'],
        'icon'     => '',
        'label'    => '<i class="material-icons green-text text-darken-1" style="vertical-align: middle;">cancel_presentation</i> Excel',
        'mime'     => 'application/vnd.ms-excel',
        'config'   => [
          'worksheet' => 'Movies-'.date('Y-m-d'),
          'cssFile' => ''
        ]
      ],
      GridView::PDF => [
        'filename' => 'Movies-'.date('Y-m-d'),
        'options' => ['title' => 'Download File PDF Movies'],
        'icon' => '',
        'label' => '<i class="material-icons red-text text-darken-1" style="vertical-align: middle;">picture_as_pdf</i> PDF',
        'mime' => 'application/pdf',
        'config' => [
          'options' => [
            'title' => 'Movies register in the platform Movie Show Time Finder',
            'subject' => 'PDF export create by Movie Show Time Finder',
            'keywords' => 'Movies, Movie Show Time Finder, pdf'
          ],
        ]
      ],
      GridView::JSON => [
        'filename' => 'Movies-'.date('Y-m-d'),
        'options' => ['title' => 'Download File JSON Movies'],
        'icon' => '',
        'label' => '<i class="material-icons yellow-text text-darken-1" style="vertical-align: middle;">code</i> JSON',
        'mime' => 'application/json',
        'config' => [
            'colHeads' => [],
            'slugColHeads' => false,
            'jsonReplacer' => new JsExpression("function(k,v){return typeof(v)==='string'?$.trim(v):v}"),
            'indentSpace' => 4,
        ],
      ]
    ]
]);
?>
