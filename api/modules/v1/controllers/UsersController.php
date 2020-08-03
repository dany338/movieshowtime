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
use backend\models\Movie;
use backend\models\Subscription;
use backend\models\Notification;
use api\modules\v1\models\curl;

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
    if(Yii::$app->request->isPost) {
      $requests  = \Yii::$app->request->post();
      if(!isset($requests['login'])) {
        throw new \yii\web\HttpException(412, 'Field login is not defined!');
      }

      $login    = !empty($requests['login']) ? trim($requests['login']) : null;

      if(is_null($login)) {
        throw new \yii\web\HttpException(412, 'Fields they are empty!');
      }

      if(is_numeric($login)){
        $user = User::find()->where(['mobile' => $login])->one();
      }
      else if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $user = User::find()->where(['email' => $login])->one();
      }
      else {
        $user = User::find()->where(['username' => $login])->one();
      }

      if($user) {
        $hash    = $user->password_hash;
        $arremail= explode('@', $user->email);
        if(!\Yii::$app->security->validatePassword($arremail[0], $hash)) {
          throw new \yii\web\NotFoundHttpException('Password incorrect!');
        }

        $roles = \Yii::$app->authManager->getRolesByUser($user->id);
        reset($roles);
        $role = current($roles);

        $user->auth_key   = Yii::$app->security->generateRandomString();
        $user->save(false);

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
            'picture'  => (!empty($user->profile->picture)) ? $user->profile->picture : 'https://www.historiaclinicaduo.com/movieshowtime/backend/web/img/default_avatar_male.jpg',
            'message'  => 'email is already registered',
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
            'mobile'   => $user->mobile,
            'token'    => $user->auth_key,
            'location' => $user->profile->location,
            'fullname' => $user->profile->name,
            'age'      => $user->profile->age
          ]
        ];
      } else {
        throw new \yii\web\NotFoundHttpException('Login incorrect!');
      }
    } else {
      throw new \yii\web\HttpException(405);
    }
  }

  public function actionCreate() {
    date_default_timezone_set('America/Bogota');
    if(Yii::$app->request->isPost) {
      $requests    = \Yii::$app->request->post();

      if(!isset($requests['moviedb_id'])) {
        throw new \yii\web\HttpException(412, 'Field moviedb_id is not defined!');
      }

      if(!isset($requests['name'])) {
        throw new \yii\web\HttpException(412, 'Field name is not defined!');
      }

      if(!isset($requests['moviedb_image'])) {
        throw new \yii\web\HttpException(412, 'Field moviedb_image is not defined!');
      }

      if(!isset($requests['age'])) {
        throw new \yii\web\HttpException(412, 'Field age is not defined!');
      }

      if(!isset($requests['email'])) {
        throw new \yii\web\HttpException(412, 'Field email is not defined!');
      }

      if(!isset($requests['mobile'])) {
        throw new \yii\web\HttpException(412, 'Field mobile is not defined!');
      }

      if(!isset($requests['fullname'])) {
        throw new \yii\web\HttpException(412, 'Field fullname is not defined!');
      }

      if(!isset($requests['location'])) {
        throw new \yii\web\HttpException(412, 'Field location is not defined!');
      }

      $data        = [];
      $description = '';

      $arremail  = explode('@',$requests['email']);
      $name      = utf8_encode($requests['fullname']);
      $age       = $requests['age'];
      $email     = $requests['email'];
      $location  = $requests['location'];
      $rolname   = 'subscriptor';

      $moviedb_id   = $requests['moviedb_id'];
      $moviedb_name = $requests['name'];
      $moviedb_image= $requests['moviedb_image'];

      $user = User::find()->where(['email' => $email])->one();

      if( $user === null ) {
        $username  = $arremail[0];
        $user = new User();
        $user->username      = $username;
        $user->email         = $email;
        $user->password_hash = Yii::$app->security->generatePasswordHash($username, Yii::$app->getModule('user')->cost);
        $user->auth_key      = Yii::$app->security->generateRandomString();
        $user->confirmed_at  = time();
        $user->created_at    = time();
        $user->updated_at    = time();
        $user->flags         = 0;

        if($user->save(false)) {

          $manager = Yii::$app->authManager;
          $assign  = $manager->getAssignment($rolname, $user->id);
          if( $assign === null )
            $manager->assign($manager->getItem($rolname), $user->id);

          $profile                 = $user->profile;
          $profile->user_id        = $user->id;
          $profile->name           = $naemailme;
          $profile->public_email   = $email;
          $profile->gravatar_email = $email;
          $profile->gravatar_id    = 'b95021aad667876effd8c427382edf4c';
          $profile->location       = $location;
          $profile->website        = 'https://www.historiaclinicaduo.com/movieshowtime/backend/web/';
          $profile->picture        = 'https://www.historiaclinicaduo.com/movieshowtime/backend/web/img/default_avatar_male.jpg';
          $profile->timezone       = 'America/Bogota';
          $profile->age            = $age;

          if($profile->save(false)) {
            $data     = [
              'picture'  => (!empty($profile->picture)) ? $profile->picture : 'https://www.historiaclinicaduo.com/movieshowtime/backend/web/img/default_avatar_male.jpg',
              'message'  => 'email is already registered',
              'id'       => $user->id,
              'username' => $user->username,
              'email'    => $user->email,
              'mobile'   => $user->mobile,
              'token'    => $user->auth_key,
              'location' => $profile->location,
              'fullname' => $profile->name,
              'age'      => $profile->age
            ];
          }
        }
        $description = 'Register new user subscriptor & send notification';
      } else {
        $data     = [
          'message'  => 'email is already registered'
        ];
        $description = 'Existing user & send notification';
      }

      $movie = Movie::find()->where(['moviedb_id' => $moviedb_id])->one();
      if($movie === null) {
        $movie = new Movie();
        $movie->moviedb_id = $moviedb_id;
        $movie->moviedb_image = $moviedb_image;
        $movie->name = $moviedb_name;
        $movie->status = 1;
        $movie->user_first_id = $user->id;
        $movie->created_at = date('Y-m-d H:i:s');
      }
      $movie->updated_at = date('Y-m-d H:i:s');
      if($movie->save(false)) {
        $subscription = Subscription::find()->where(['movie_id' => $movie->id, 'uid' => $user->id])->one();
        if($subscription === null) {
          $subscription = new Subscription();
          $subscription->movie_id = $movie->id;
          $subscription->uid = $user->id;
          $subscription->notification = 0;
          $subscription->created_at = date('Y-m-d H:i:s');
        }
        $subscription->updated_at = date('Y-m-d H:i:s');
        if($subscription->save(false)) {
          $mensaje = $user->mailer->sendSubscriptionMessage($user, $movie, $subscription);
          if($mensaje) {
            $subscription->notification = (int)$subscription->notification + 1;
            $subscription->save(false);
            $notification = new Notification();
            $notification->subscription_id = $subscription->id;
            $notification->uid             = $user->id;
            $notification->description     = $description.' # '.$subscription->notification;
            $notification->created_at      = date('Y-m-d H:i:s');
            $notification->updated_at      = date('Y-m-d H:i:s');
            $notification->save(false);
          }
        }
      }

      $response = \Yii::$app->response;
      \Yii::$app->response->statusCode = 200;
      $response->format = \yii\web\Response::FORMAT_JSON;
      $response->getHeaders()->set('Access-Control-Allow-Origin','*');
      $response->data   = [
        'name'     => 'OK',
        'message'  => $response->isSuccessful,
        'code'     => 0,
        'status'   => \Yii::$app->response->statusCode,
        'data'     => $data
      ];
      return $data;
    } else {
      throw new \yii\web\HttpException(405);
    }
  }

  public function actionForgotPassword() {
      if(Yii::$app->request->isPost) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Para Json types resquest
        $requests                    = \Yii::$app->request->post();

        if(!isset($requests['email'])) {
          throw new \yii\web\HttpException(412, 'Field email is not defined!');
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
              'message' => 'Trouble creating the remember password token'
            ];
            $response = \Yii::$app->response;
            \Yii::$app->response->statusCode = 204;
            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->getHeaders()->set('Access-Control-Allow-Origin','*');
            // Uno desde la web y otro desde la web.
            $response->data   = [
              'name'     => 'OK',
              'message'  => 'Trouble creating the remember password token',
              'code'     => 0,
              'status'   => \Yii::$app->response->statusCode,
              'data'     => $data
            ];
            return $data;
          }

          $user->mailer->sendRecoveryMessage($user, $token);

          $data     = [
            'message' => 'Mail successfully sent',
            'url' => Html::encode($token->url)
          ];

          $response = \Yii::$app->response;
          \Yii::$app->response->statusCode = 200;
          $response->format = \yii\web\Response::FORMAT_JSON;
          $response->getHeaders()->set('Access-Control-Allow-Origin','*');
          // Uno desde la web y otro desde la web.
          $response->data   = [
            'name'     => 'OK',
            'message'  => 'Mail successfully sent',
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
