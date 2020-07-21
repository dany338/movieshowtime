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
use backend\models\Notificacion;
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
$notifys   = Notificacion::find()->count();
}
?>

<?php if (!Yii::$app->user->isGuest) { ?>
  <header class="page-header">
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
        'label'   => '<i class="material-icons amber-text text-accent-3">account_balance</i> Inicio',
        'url'     => Url::to('@hostback/site/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Inicio <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ],
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">group</i> Usuarios',
        'url'     => Url::to('@hostback/user/admin/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Usuarios <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">account_circle</i> Perfil',
        'url'     => Url::to('@hostback/user/settings/profile', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Mi Peril <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">notification_important</i> Notificaciones <span class="badge red lighten-2 white-text">'.$notifys.'</span>',
        'url'     => Url::to('@hostback/notificacion/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Mis Notificaciones <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Certificados</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">cloud_download</i> Certificados',
        'url'     => Url::to('@hostback/certificado/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Certificados Generados <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">ballot</i> Auditoria',
        'url'     => Url::to('@hostback/auditoria/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Auditoria Certificados <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Cursos</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">school</i> Entrenadores',
        'url'     => Url::to('@hostback/entrenador/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Entrenadores <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">group</i> Cursos',
        'url'     => Url::to('@hostback/curso/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Cursos <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">group</i> Programación',
        'url'     => Url::to('@hostback/entrenadorcurso/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Programación <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">group</i> Inscritos Cursos',
        'url'     => Url::to('@hostback/inscritocurso/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Inscriptos Curso <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Empresas</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">location_city</i> Empresas',
        'url'     => Url::to('@hostback/entidad/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Empresas/Fabricas <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">face</i> Inscritos',
        'url'     => Url::to('@hostback/inscripto/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Inscritos <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Configuraciones</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">settings</i> Configuración',
        'url'     => Url::to('@hostback/configuracion/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Configuración <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">class</i> Diplomas',
        'url'     => Url::to('@hostback/diploma/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Diplomas <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">dns</i> Posiciones',
        'url'     => Url::to('@hostback/posiciones/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Posiciones Datos en Diplomas <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">backup</i> Importaciones',
        'url'     => Url::to('@hostback/importacion/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Importaciones de datos realizadas <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Público</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">spellcheck</i> Consulta',
        'url'     => Url::to('@hostback/site/consulta', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Consulta Certificados <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">supervised_user_circle</i> Inscripción',
        'url'     => Url::to('@hostback/site/inscripcion', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Inscripción Cursos <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      '<div class="divider"></div>',
      '<a class="subheader white-text"><i class="material-icons amber-text text-accent-3">keyboard_arrow_down</i> Opciones * Cuestionario</a>',
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">how_to_reg</i> Participantes',
        'url'     => Url::to('@hostback/participante/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Consulta Participantes <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">create</i> Preguntas',
        'url'     => Url::to('@hostback/pregunta/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Preguntas registradas <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
      [
        'label'   => '<i class="material-icons amber-text text-accent-3">ballot</i> Cuestionarios',
        'url'     => Url::to('@hostback/cuestionario/index', true),
        'encode'  => false,
        'visible' => !Yii::$app->user->can('punto_bioseguro'),
        'linkOptions' => [
          'class' => 'waves-effect white-text tooltipped',
          'data-position' => 'right',
          'data-delay'    => '50',
          'data-tooltip'  => '<p style="text-align:justify;">Cuestionarios registrados <span class="red-text text-darken-1"><b>E & T</b></span></p>'
        ]
      ],
    ];

    if(!Yii::$app->user->can('punto_bioseguro')) {
      NavBar::begin([
          'brandLabel'    => Html::img('@logo', ['title' => Yii::t('yii', 'Educación Salud Y Seguridad E & T'), 'width'=>'65','height'=>'45']),
          'brandOptions'  => ['style' => 'left: 45% !Important'],
          'brandUrl'      => Yii::$app->homeUrl,
          'fixed'         => true,
          'renderSidenav' => true,
          'sidenavItems'  => $menuItems2,
          'sidenavToggleButtonOptions' => [
            'options' => [
              'title' => 'Menu de opciones E & T',
              'class' => 'sidenav-trigger btn btn-flat waves-effect white-text',
              'style' => 'height: 100% !important;',
            ]
          ],
          'wrapperOptions' => [
            'class' => 'container blue-grey darken-2',
            'style' => 'width: 100% !important; max-width: 100% !important;',
          ],
      ]);

      /*$menuItems = [
        ['label' => 'Consulte Certificado', 'url' => ['/site/consulta']],
        ['label' => 'Inscripción Curso', 'url' => ['/site/inscripcion']],
      ];**/
      if (Yii::$app->user->isGuest) {
          $menuItems = [
            ['label' =>  Yii::t('app', 'Contacto'), 'url' => ['/site/contact']],
            ['label' =>  Yii::t('app', 'Nosotros'), 'url' => ['/site/about']],
          ];
          $menuItems[] = ['label' => Yii::t('app', 'Login'), 'url' => ['/user/security/login']];
      } else {
          //if( Yii::$app->user->can('administrator') ) {
            //$menuItems[] = ['label' => Yii::t('app', 'Funcionarios'), 'url' => ['/user/admin/index']];
          //}
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
    } else {
      NavBar::begin([
        'brandLabel'    => Html::img('@logo', ['title' => Yii::t('yii', 'Educación Salud Y Seguridad E & T'), 'width'=>'65','height'=>'45']),
        'brandOptions'  => ['style' => 'left: 45% !Important'],
        'brandUrl'      => Yii::$app->homeUrl,
        'fixed'         => true,
        'renderSidenav' => false,
        'sidenavToggleButtonOptions' => [
          'options' => [
            'title' => 'Menu de opciones E & T',
            'class' => 'sidenav-trigger btn btn-flat waves-effect white-text',
            'style' => 'height: 100% !important;',
          ]
        ],
        'wrapperOptions' => [
          'class' => 'container blue-grey darken-2',
          'style' => 'width: 100% !important; max-width: 100% !important;',
        ],
      ]);

      $menuItems = [];
      $menuItems[] = '<li>'
          . Html::beginForm(['/user/security/logout'], 'post')
          . Html::submitButton(
              '<span class="left" style="vertical-align: middle; height: 64px; line-height: 64px; margin-right: 10px;">'.(Yii::$app->user->identity->username).'</span> <i class="material-icons left">exit_to_app</i>',
              ['class' => 'btn btn-flat waves-light white-text', 'style' => 'height: initial !important;', 'title' => 'Logout']
          )
          . Html::endForm()
          . '</li>';

      echo Nav::widget([
        'options' => ['class' => 'right'],
        'items' => $menuItems,
        ]);
      NavBar::end();
    }
    ?>
  </header>
  <?php } ?>

  <main class="content">
    <div class="container" style="width:100% !important; padding: 0px !important; min-width: 100% !important;">
        <?php if (!Yii::$app->user->isGuest) { ?>
        <?= Breadcrumbs::widget([
            'containerOptions'   => ['class'=>'nav-wrapper blue-grey darken-2'],
            'homeLink'           => ['url' => Url::to('@hostback/site/index', true), 'label' => 'Página Principal', 'title' => 'Ir al Inicio'],
            'activeItemTemplate' => '<span class="breadcrumb active" style="text-decoration: underline;" title="Opción Actual"><i class="material-icons amber-text text-accent-3" style="float: none !important; vertical-align: middle;">arrow_forward</i>{link}</span>',
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
          <h5 class="white-text">Educación Salud Y Seguridad E & T</h5>
          <p class="grey-text text-lighten-4" id="bloque-ayuda">Recuerde constantemente verificar la descarga de los certificados a través de la opción de auditoria ó <a class="waves-effect amber-text text-accent-3 tooltipped" data-position="right" data-delay="50" data-tooltip="<p style='text-align:justify;'>Ver Auditorias <span class='red-text text-darken-1'><b>E & T</b></span></p>" href="<?=Url::to('@hostback/auditoria/index', true) ?>">Aquí</a></p>
        </div>
        <div class="col l4 offset-l2 s12">
            <h5 class="white-text">Accesos Directos</h5>
            <ul>
                <li><a class="waves-effect grey-text text-lighten-3 tooltipped" data-position="right" data-delay="50" data-tooltip="<p style='text-align:justify;'>Consulta Certificados <span class='red-text text-darken-1'><b>E & T</b></span></p>" href="<?=Url::to('@hostback/site/consulta', true) ?>"><i class="material-icons amber-text text-accent-3" style="vertical-align: middle;">spellcheck</i> Consulta Certificados</a></li>
                <li><a class="waves-effect grey-text text-lighten-3 tooltipped" data-position="right" data-delay="50" data-tooltip="<p style='text-align:justify;'>Inscripción Cursos <span class='red-text text-darken-1'><b>E & T</b></span></p>" href="<?=Url::to('@hostback/site/inscripcion', true) ?>"><i class="material-icons amber-text text-accent-3" style="vertical-align: middle;">supervised_user_circle</i> Inscripción Cursos</a></li>
            </ul>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="footer-copyright">
      <div class="container" style="width:100% !important;">
      &copy; <?= date('Y') ?> Support by Daniel Gallo & dany338@gmail.com
      <a class="grey-text text-lighten-4 right" href="https://www.educacionsaludyseguridad.com/"><?= Html::img('@logo', ['title' => 'Movie Show Time Finder', 'width'=>'25','height'=>'25']) ?></a>
      </div>
    </div>
  </footer>
<?php } ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
