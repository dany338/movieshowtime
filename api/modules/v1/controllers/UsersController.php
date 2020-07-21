<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\helpers\Html;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

use dektrium\user\Finder;
use dektrium\user\models\LoginForm;
use dektrium\user\models\User;
use dektrium\user\models\Profile;
use dektrium\user\models\Token;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\db\ActiveQuery;
use backend\models\Configuration;

class UsersController extends ActiveController
{
  public $modelClass = 'dektrium\user\models\User';

  public function behaviors()
  {
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
    $behaviors['verbs'] = [
      'class' => VerbFilter::className(),
      'actions' => [
        'login' => ['POST','OPTIONS'],
        'forgot-password' => ['POST','OPTIONS'],
        'create'=> ['POST'],
        'obtener' => ['GET']
      ],
    ];
    $behaviors['authenticator'] = [
      'class' => CompositeAuth::className(),
      'except' => ['login','options','create','forgot-password', 'obtener'],
      'authMethods' => [
        HttpBasicAuth::className(),
        HttpBearerAuth::className(),
        QueryParamAuth::className(),
      ],
    ];
    return $behaviors;
  }

  public function actions()       // Just read only rest api
  {
    $actions = parent::actions();
    unset($actions['delete'], $actions['create'], $actions['update']);
    return $actions;
  }

  public function beforeAction($action)
  {
      parent::beforeAction($action);

      if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
          Yii::$app->getResponse()->getHeaders()->set('Allow', true);
          Yii::$app->getResponse()->getHeaders()->set('Content-Type', 'application/json');
          Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
          Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Headers','Content-Type');
          Yii::$app->end();
      }

      return true;
  }

  public function actionObtener() {
    if(Yii::$app->request->isGet) {
      $requests  = \Yii::$app->request->get();

      if(!isset($requests['documento'])) {
        throw new \yii\web\HttpException(412, 'El campo documento no esta definido!');
      }

      $profile = Profile::find()->where(['identification_card' => $requests['documento']])->one();

      if($profile) {
        $data = [
          'documento' => $profile->identification_card,
          'celular' => $profile->mobile,
          'nombres' => $profile->name,
          'correo' => $profile->public_email,
        ];
        return $data;
      } else {
        throw new \yii\web\NotFoundHttpException;
      }
    } else {
        throw new \yii\web\HttpException(405);
    }
  }

  public function actionLogin() {
    $requests  = \Yii::$app->request->post();
    $login     = isset($requests['login']) ? (!empty($requests['login']) ? trim($requests['login']) : null ) : null; // username or login
    $password  = isset($requests['password']) ? (!empty($requests['password']) ? trim($requests['password']) : null ) : null;

    if( is_null($login) || is_null($password) ) {
      throw new \yii\web\HttpException(412);
    }
    else {
      if(is_numeric($login)){
        $user = User::find()->where(['mobile' => $login])->one();
      }
      else if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $user = User::find()->where(['email' => $login])->one();
      }
      else {
        $user = User::find()->where(['username' => $login])->one();
      }

      if( $user === null ) {
        //throw new \yii\web\NotFoundHttpException;
        $response = \Yii::$app->response;
        \Yii::$app->response->statusCode = 200;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data   = [
          'name'     => 'error',
          'message'  => $response->isSuccessful,
          'code'     => 0,
          'status'   => \Yii::$app->response->statusCode,
          'data'     => [
            'message'  => 'contraseña incorrecta'
          ]
        ];
      } else {
        $hash = $user->password_hash;
        if(!\Yii::$app->security->validatePassword($password, $hash)) {
          throw new \yii\web\NotFoundHttpException;
        }

        //$user->auth_key = \Yii::$app->security->generateRandomString();
        //$user->save();

        $configuration = Configuration::find()->where(['idType'=>1])->one();
        $hostapi       = $configuration->description;
        $roles = \Yii::$app->authManager->getRolesByUser($user->id);
        reset($roles);
        $role = current($roles);

        $response = \Yii::$app->response;
        \Yii::$app->response->statusCode = 200;
        $response->format = \yii\web\Response::FORMAT_JSON;

        $response->getHeaders()->set('Access-Control-Allow-Origin','*');
        // Uno desde la web y otro desde la web.
        $response->data   = [
          'name'     => 'OK',
          'message'  => $response->isSuccessful,
          'code'     => 0,
          'status'   => \Yii::$app->response->statusCode,
          'data'     => [
            'uid'                 => $user->id,
            'username'            => $user->username,
            'email'               => $user->email,
            'fullname'            => $user->profile->name,
            'picture'             => (!empty($user->profile->bio)) ? $user->profile->bio : 'https://www.cvexpress.club/mensajeros/backend/web/img/default_avatar_male.jpg',
            'accessToken'         => $user->auth_key, // en los otros servicios se manda el token asi: access-token=xxxx
            'type'                => $role->name, //$user->profile->idTipo0->name//
            'hostapi'             => $hostapi
          ]
        ];
      }
    }
  }

  public function actionCreate() {
    date_default_timezone_set('America/Bogota');
    if(Yii::$app->request->isPost) {
      $requests    = \Yii::$app->request->post();

      if(!isset($requests['fullname']) || !isset($requests['age']) || !isset($requests['email']) || !isset($requests['city']) || !isset($requests['school']) || !isset($requests['password']) || !isset($requests['type'])) {
        throw new \yii\web\HttpException(412);
      }

      $data      = [];

      $arremail  = explode('@',$requests['email']);
      $name      = utf8_encode($requests['fullname']);
      $age       = $requests['age'];
      $email     = $requests['email'];
      $city      = $requests['city'];
      $school    = $requests['school'];
      //$university= $requests['university'];
      $password  = $requests['password'];
      $career    = $requests['career'];
      $type      = $requests['type']; // mobile, web

      if( $type == 'mobile' ) {
        $rolname   = 'student';
      } else if( $type == 'web' ) {
        $rolname   = 'adviser';
      }

      $user = User::find()->where(['email' => $email])->one();

      if( $user === null ) {
        $username  = $arremail[0];
        $userCount = User::find()->where(['username' => $username])->count();

        if( $userCount > 0 ) {
          $username .= $userCount;
        }
        $user = new User();
        $user->username      = $username;
        $user->email         = $email;
        $user->password_hash = Yii::$app->security->generatePasswordHash($password, Yii::$app->getModule('user')->cost);
        $user->auth_key      = Yii::$app->security->generateRandomString();
        $user->confirmed_at  = time();
        $user->created_at    = time();
        $user->updated_at    = time();
        $user->flags         = 0;

        if($user->save(false)) {

          $manager = Yii::$app->authManager;
          $assign  = $manager->getAssignment($rolname,$user->id);
          if( $assign === null )
            $manager->assign($manager->getItem($rolname),$user->id);

          $profile                 = $user->profile;//$profile = new Profile();
          $profile->user_id        = $user->id;
          $profile->name           = $name;
          $profile->public_email   = $email;
          $profile->gravatar_email = $email;
          $profile->gravatar_id    = 'b95021aad667876effd8c427382edf4c';
          $profile->location       = 'Bogota';
          $profile->website        = 'https://cvexpress.club';
          $profile->picture        = 'https://cvexpress.club/diamond/backend/web/img/default_avatar_male.jpg';
          $profile->timezone       = 'America/Bogota';
          $profile->idTipo         = 4; // student
          $profile->school         = $school;
          $profile->city           = $city;
          $profile->age            = $age;
          $profile->career         = $career;

          if($profile->save(false)) {
            $data     = [
              'id'       => $user->id,
              'username' => $user->username,
              'email'    => $user->email,
              'auth_key' => $user->auth_key,
              'picture'  => $profile->picture,
              'school'   => $profile->school,
              'city'     => $profile->city,
              'age'      => $profile->age
            ];
          }
        }
        $response = \Yii::$app->response;
        \Yii::$app->response->statusCode = 200;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->getHeaders()->set('Access-Control-Allow-Origin','*');
        // Uno desde la web y otro desde la web.
        $response->data   = [
          'name'     => 'OK',
          'message'  => $response->isSuccessful,
          'code'     => 0,
          'status'   => \Yii::$app->response->statusCode,
          'data'     => $data
        ];
        return $data;
      } else {
        $data     = [
          'message'  => 'email ya se encuentra registrado'
        ];
        $response = \Yii::$app->response;
        \Yii::$app->response->statusCode = 200;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->getHeaders()->set('Access-Control-Allow-Origin','*');
        // Uno desde la web y otro desde la web.
        $response->data   = [
          'name'     => 'error',
          'message'  => 'email ya se encuentra registrado',
          'code'     => 0,
          'status'   => \Yii::$app->response->statusCode,
          'data'     => $data
        ];
        return $data;
      }
    } else {
      throw new \yii\web\HttpException(405);
    }
  }

  public function actionForgotPassword()
    {
      if(Yii::$app->request->isPost) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Para Json types resquest
        $requests                    = \Yii::$app->request->post();

        if(!isset($requests['email']) ) {
          throw new \yii\web\HttpException(412);
        }

        $email = $requests['email'];

        $user  = User::find()->where(['email'=>$email])->one();

        if($user) {
          /*Espacio para enviar el correo*/

          $token = \Yii::createObject([
              'class' => Token::className(),
              'user_id' => $user->id,
              'type' => Token::TYPE_RECOVERY,
          ]);

          if (!$token->save(false)) {
            $data     = [
              'message' => 'Problemas para crear el token de recordar contraseña'
            ];
            $response = \Yii::$app->response;
            \Yii::$app->response->statusCode = 204;
            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->getHeaders()->set('Access-Control-Allow-Origin','*');
            // Uno desde la web y otro desde la web.
            $response->data   = [
              'name'     => 'OK',
              'message'  => 'Problemas para crear el token de recordar contrase���a',
              'code'     => 0,
              'status'   => \Yii::$app->response->statusCode,
              'data'     => $data
            ];
            return $data;
          }

          $user->mailer->sendRecoveryMessage($user, $token);

          $data     = [
            'message' => 'Correo enviado satisfactoriamente',
            'url' => Html::encode($token->url)
          ];

          $response = \Yii::$app->response;
          \Yii::$app->response->statusCode = 200;
          $response->format = \yii\web\Response::FORMAT_JSON;
          $response->getHeaders()->set('Access-Control-Allow-Origin','*');
          // Uno desde la web y otro desde la web.
          $response->data   = [
            'name'     => 'OK',
            'message'  => 'Correo enviado satisfactoriamente',
            'code'     => 0,
            'status'   => \Yii::$app->response->statusCode,
            'data'     => $data
          ];
          return $data;

        } else {
          throw new \yii\web\NotFoundHttpException;
        }

      } else {
        throw new \yii\web\HttpException(405);
      }
    }

}
