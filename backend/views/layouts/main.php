<?php

/* @var $this \yii\web\View */
/* @var $content string */
use yii\web\View;
use yii\helpers\Url;
use macgyer\yii2materializecss\lib\Html;
use macgyer\yii2materializecss\widgets\navigation\Nav;
use macgyer\yii2materializecss\widgets\navigation\NavBar;
use macgyer\yii2materializecss\widgets\navigation\Breadcrumbs;
use macgyer\yii2materializecss\widgets\navigation\SideNav;
use macgyer\yii2materializecss\widgets\Alert;
use backend\assets\AppAsset;
rmrevin\yii\fontawesome\AssetBundle::register($this);
use rmrevin\yii\fontawesome\FA;
use dektrium\user\models\Profile;
use backend\models\Notification;
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <!--Import Google Icon Font-->
    <!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
    <link rel="shortcut icon" href="<?=Url::to('@hostback/img/favicon.ico', true) ?>" type="image/x-icon">
    <link rel="icon" href="<?=Url::to('@hostback/img/favicon.ico', true) ?>" type="image/x-icon">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
if(!Yii::$app->user->isGuest)
{
$script = <<< JS

jQuery('.button-collapse').sidenav({
    menuWidth: 300, // Default is 300
    edge: 'left', // Choose the horizontal origin
    closeOnClick: true, // Closes side-nav on <a> clicks, useful for Angular/Meteor
    draggable: true // Choose whether you can drag to open on touch screens
  }
);

jQuery('#menu-dashboard2').sidenav({
    menuWidth: 300, // Default is 300
    edge: 'right', // Choose the horizontal origin
    closeOnClick: true, // Closes side-nav on <a> clicks, useful for Angular/Meteor
    draggable: true // Choose whether you can drag to open on touch screens
  }
);

jQuery('.tooltipped').tooltip({delay: 50, html: true});

yii.confirm = function (message, okCallback, cancelCallback) {
    swal({
        title: message,
        type: 'warning',
        showCancelButton: true,
        closeOnConfirm: true,
        allowOutsideClick: true
    }, okCallback);
};

JS;
$this->registerJs($script, View::POS_READY, 'init-menu');

$profile   = Profile::findOne(Yii::$app->user->identity->id);
$picture   = !(empty($profile->picture)) ? $profile->picture : '@hostback/img/default_avatar_male.jpg';
$userlogo  = Url::to($picture,true);
$notifies   = Notification::find()->count();
}
?>

<?php if (!Yii::$app->user->isGuest) { ?>
  <header class="">
    <?php
    $userView  = '<div class="user-view">';
    $userView .= '<div class="background blue-grey darken-2" style="text-align:right;"></div>';
    $userView .= '<a href="'.Url::to('@hostback/user/settings/profile', true).'">'.Html::img($userlogo, ["title" => "Imagen de Perfil", "class" => "circle"]).'</a>';
    $userView .= '<a href="'.Url::to('@hostback/user/settings/profile', true).'"><span class="white-text text-darken-4 name">'.Yii::$app->user->identity->username.'</span></a>';
    $userView .= '<a href="'.Url::to('@hostback/user/settings/profile', true).'"><span class="white-text text-darken-4 email">'.Yii::$app->user->identity->email.'</span></a>';
    $userView .= '</div>';

    $menuItems2 = [
      $userView,
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">account_balance</i> Home',
        'url'     => Url::to('@hostback/site/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Inicio <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ],
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">group</i> Users',
        'url'     => Url::to('@hostback/user/admin/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Users <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">account_circle</i> Profile',
        'url'     => Url::to('@hostback/user/settings/profile', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">My Profile <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">notification_important</i> Notifications <span class="badge red lighten-2 white-text">'.$notifies.'</span>',
        'url'     => Url::to('@hostback/notification/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">My Notifications <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Options * Movies</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">cloud_download</i> Movies',
        'url'     => Url::to('@hostback/movie/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Movies <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">ballot</i> Theaters',
        'url'     => Url::to('@hostback/movietheater/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Movie Theaters <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Subscriptions</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">school</i> Subscriptions',
        'url'     => Url::to('@hostback/subscription/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Subscriptions <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">group</i> Billboards',
        'url'     => Url::to('@hostback/moviebillboard/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Billboard <span class="red-text text-darken-1"><b>Movie Show Time Finder</b></span></p>'
        ]
      ],
    ];

      NavBar::begin([
          'brandLabel'    => Html::img('@logo', ['title' => Yii::t('yii', 'Movie Show Time Finder'), 'width'=>'65','height'=>'45']),
          'brandOptions'  => ['style' => 'left: 45% !Important'],
          'brandUrl'      => Yii::$app->homeUrl,
          'fixed'         => true,
          'renderSidenav' => true,
          'sidenavItems'  => $menuItems2,
          'sidenavToggleButtonOptions' => [
            'options' => [
              'title' => 'Menu options Movie Show Time Finder',
              'class' => 'sidenav-trigger btn btn-flat waves-effect white-text',
              'style' => 'height: 100% !important;',
            ]
          ],
          'wrapperOptions' => [
            'class' => 'container blue-grey darken-2',
            'style' => 'width: 100% !important; max-width: 100% !important;',
          ],
      ]);

      if (Yii::$app->user->isGuest) {
          $menuItems = [
            ['label' =>  Yii::t('app', 'Contacto'), 'url' => ['/site/contact']],
            ['label' =>  Yii::t('app', 'Nosotros'), 'url' => ['/site/about']],
          ];
          $menuItems[] = ['label' => Yii::t('app', 'Login'), 'url' => ['/user/security/login']];
      } else {
          $menuItems = [];
          $menuItems[] = '<li>'
              . Html::beginForm(['/user/security/logout'], 'post')
              . Html::submitButton(
                  '<span class="left" style="vertical-align: middle; height: 64px; line-height: 64px; margin-right: 10px;">'.(Yii::$app->user->identity->username).'</span> <i class="material-icons left">exit_to_app</i>',
                  ['class' => 'btn btn-flat waves-light white-text', 'style' => 'height: initial !important;', 'title' => 'Logout']
              )
              . Html::endForm()
              . '</li>';
      }


        echo Nav::widget([
        'options' => ['class' => 'right'],
        'items' => $menuItems,
        ]);
      NavBar::end();
    ?>
  </header>
  <?php } ?>

  <main class="content">
    <div class="container" style="width:100% !important; padding: 0px !important; min-width: 100% !important;">
        <?php if (!Yii::$app->user->isGuest) { ?>
        <?= Breadcrumbs::widget([
            'containerOptions'   => ['class'=>'nav-wrapper blue-grey darken-2'],
            'homeLink'           => ['url' => Url::to('@hostback/site/index', true), 'label' => 'Home', 'title' => 'Go to home'],
            'activeItemTemplate' => '<span class="breadcrumb active" style="text-decoration: underline;" title="Current page"><i class="material-icons amber-text text-accent-3" style="float: none !important; vertical-align: middle;">arrow_forward</i>{link}</span>',
            //'itemTemplate'       => '{link} <i class="material-icons amber-text text-accent-3">arrow_forward</i>',

            'links'              => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'options'            => ['style'=>'padding: 0 15px;']
        ]) ?>
        <?php } ?>
        <?= Alert::widget() ?>
        <div class="row">
          <div class="col s12">
            <?= $content ?>
          </div>
        </div>
    </div>
  </main>
<?php
if(!Yii::$app->user->isGuest)
{
?>
<?php
}
?>

<?php if (!Yii::$app->user->isGuest) { ?>
  <footer class="page-footer blue-grey darken-2">
    <div class="container">
      <?php if(!Yii::$app->user->can('punto_bioseguro')) { ?>
      <div class="row">
        <div class="col l6 s12">
          <h5 class="white-text">Movie Show Time Finder</h5>
          <p class="grey-text text-lighten-4" id="bloque-ayuda">Always remember to check the generation of time schedules through the billboard option or <a class="waves-effect amber-text text-accent-3 tooltipped" data-position="right" data-delay="50" data-tooltip="<p style='text-align:justify;'>Ver Auditorias <span class='red-text text-darken-1'><b>E & T</b></span></p>" href="<?=Url::to('@hostback/moviebillboard/index', true) ?>">Here</a></p>
        </div>
        <div class="col l4 offset-l2 s12">
            <h5 class="white-text">Shortcuts</h5>
            <ul>
                <li><a class="waves-effect grey-text text-lighten-3 tooltipped" data-position="right" data-delay="50" data-tooltip="<p style='text-align:justify;'>Consulta Certificados <span class='red-text text-darken-1'><b>E & T</b></span></p>" href="<?=Url::to('@hostback/movie/index', true) ?>"><i class="material-icons amber-text text-accent-3" style="vertical-align: middle;">spellcheck</i> Search Movies</a></li>
                <li><a class="waves-effect grey-text text-lighten-3 tooltipped" data-position="right" data-delay="50" data-tooltip="<p style='text-align:justify;'>Inscripción Cursos <span class='red-text text-darken-1'><b>E & T</b></span></p>" href="<?=Url::to('@hostback/subscription/index', true) ?>"><i class="material-icons amber-text text-accent-3" style="vertical-align: middle;">supervised_user_circle</i> Search Subscriptions</a></li>
            </ul>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="footer-copyright">
      <div class="container" style="width:100% !important;">
      &copy; <?= date('Y') ?> Support by Daniel Gallo & dany338@gmail.com
      <a class="grey-text text-lighten-4 right" href="https://www.historiaclinicaduo.com/movieshowtime/backend/web/"><?= Html::img('@logo', ['title' => 'Movie Show Time Finder', 'width'=>'25','height'=>'25']) ?></a>
      </div>
    </div>
  </footer>
<?php } ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
