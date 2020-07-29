<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use dektrium\user\models\User;
use dektrium\user\models\Profile;
use dektrium\user\helpers\Password;
use backend\models\Movie;
use backend\models\Subscription;
use api\modules\v1\models\curl;
/**
 * SubscriptionController implements the CRUD actions for Question model.
 */
class SubscriptionController extends ActiveController
{
  public $modelClass = 'backend\models\Subscription';

  public function behaviors()
  {
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
    $behaviors['access'] = [
      'class' => AccessControl::className(),
      'only' => ['create'],
      'rules' => [
        [
          'allow' => true,
          'actions' => ['create'],
          'roles' => ['@'],
        ],
      ]
    ];
    $behaviors['verbs'] = [
      'class' => VerbFilter::className(),
      'actions' => [
        'create' => ['POST'],
      ],
    ];
    $behaviors['authenticator'] = [
      'class' => CompositeAuth::className(),
      //'except' => ['search'],
      'authMethods' => [
        HttpBasicAuth::className(),
        HttpBearerAuth::className(),
        QueryParamAuth::className(),
      ],
    ];
    return $behaviors;
  }

  public function actions()
  {
    $actions = parent::actions();
    unset($actions['create']);
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
    } else if( (Yii::$app->getRequest()->getMethod() === 'POST') || (Yii::$app->getRequest()->getMethod() === 'GET') || (Yii::$app->getRequest()->getMethod() === 'PUT') ) {
      Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
    }

    return true;
  }

  public function actionCreate()
  {
    date_default_timezone_set('America/Bogota');
    if(Yii::$app->request->isPost) {
      $requests    = \Yii::$app->request->post();
      $requests2   = \Yii::$app->request->get();

      if(!isset($requests['movie_id'])) {
        throw new \yii\web\HttpException(412, 'Field movie id is not defined!');
      }

      if(!isset($requests['movie_name'])) {
        throw new \yii\web\HttpException(412, 'Field movie name is not defined!');
      }

      if(!isset($requests['movie_image'])) {
        throw new \yii\web\HttpException(412, 'Field movie image is not defined!');
      }

      if(!isset($requests['uid'])) {
        throw new \yii\web\HttpException(412, 'Field uid is not defined!');
      }

      $auth_key = $requests2['access-token'];
      $author   = User::find()->where(['auth_key' => $auth_key])->one();
      $uidAuthor= $author->id;

      $data = [];
      $movie_id    = $requests['movie_id']; // Movie ID themoviedb.org
      $movie_name  = $requests['movie_name']; // Movie Name
      $movie_image = $requests['movie_image']; // Movie Image
      $uid         = $requests['uid']; // User subscription
      $user        = User::findOne($uid);

      if($user) {
        $movie = Movie::find()->where(['moviedb_id' => $movie_id])->one();

        if($movie === null) {
          $movie = new Movie();
        }

        $service = new Service();
        $service->uid               = $user->id;
        $service->id_service_type   = $service_type;
        $service->id_service_status = 1;
        $service->id_coupon_code    = $id_coupon_code;
        $service->uid_courier       = $uid_courier;
        $service->id_method         = $metodo_pago;
        $service->start_date        = time();
        $service->end_date          = strtotime($end_date);
        $service->round_trip        = $round_trip;
        $service->start_address     = $start_address;
        $service->description       = $description;
        $service->declared_value    = $declared_value;
        $service->total             = $total;
        $service->distance          = $distance;
        $service->guide_number      = $this->generateRandomInt(100000,999999);//Yii::$app->security->generateRandomString(13);
        $service->state             = 1;
        $service->created_at        = time();
        $service->updated_at        = time();

        if($service->save(false)) {

          $serviceAddress = new ServiceAddress();
          $serviceAddress->id_service  = $service->id;
          $serviceAddress->end_address = $end_address;
          $serviceAddress->observation = '';
          $serviceAddress->state       = 1;
          $serviceAddress->created_at  = time();
          $serviceAddress->updated_at  = time();
          $serviceAddress->save(false);
          $direcciones = [];
          foreach ($toWaypoints as $address) {
            $direcciones[] = $address;

            $serviceAddress = new ServiceAddress();
            $serviceAddress->id_service  = $service->id;
            $serviceAddress->end_address = $address;
            $serviceAddress->observation = '';
            $serviceAddress->state       = 1;
            $serviceAddress->created_at  = time();
            $serviceAddress->updated_at  = time();
            $serviceAddress->save(false);
          }

          $data     = [
            'id'             => $service->id,
            'uid'            => $user->profile->name,
            'username'       => $user->username,
            'password'       => $password,
            'service_type'   => (isset($service->serviceType)) ? $service->serviceType->name : '',
            'service_status' => $service->serviceStatus->name,
            'coupon_code'    => (isset($service->couponCode->code)) ? $service->couponCode->code : '',
            'start_date'     => date('d M, Y g:i A', $service->start_date),
            'end_date'       => date('d M, Y g:i A', $service->end_date),
            'round_trip'     => $service->round_trip,
            'id_method'      => $service->id_method,
            'start_address'  => $service->start_address,
            'description'    => $service->description,
            'declared_value' => $service->declared_value,
            'total'          => number_format($service->total, 0, ',', ','),
            'distance'       => $service->distance,
            'guide_number'   => $service->guide_number,
            'state'          => $service->state,
            'created_at'     => date('d M, Y g:i A', $service->created_at),
            'updated_at'     => date('d M, Y g:i A', $service->updated_at),
            'direcciones'    => $requests['toWaypoints']
          ];
        }

        $params = [
          'email'   => $email,
          'service' => $service,
          'user' => $user,
          'password' => $password
        ];
        // Enviando el correo de confirmación del servicio
        $user->mailer->sendServiceMessage($params);

        // Enviar la notificación a los mensajeros registrados de que se ha creado un nuevo servicio
        $mensajeros = Profile::find()->where(['idTipo' => 3])->all();
        foreach ($mensajeros as $key => $mensajero) {
          $params = [
            'email'   => $mensajero->user->email,
            'service' => $service,
            'user'    => $mensajero->user,
            'password' => 0
          ];
          // Enviando el correo de confirmación del servicio
          $user->mailer->sendServiceMensajeroMessage($params);
        }

        $twilioService = Yii::$app->Yii2Twilio->initTwilio();

        //
        try {
          $message = $twilioService->account->messages->create(
            "+57".$user->profile->mobile, // To a number that you want to send sms
            array(
              "from" => "+17177077105",   // From a number that you are sending
              "body" => "CV Express # Guia: ".$service->guide_number." Ingresar: https://www.cvexpress.club",
          ));
        } catch (\Twilio\Exceptions\RestException $e) {
          echo $e->getMessage();
        }

        $result_send  = \Yii::$app->onesignal->notifications()->create(
          ['en' => 'CV Express, hay un nuevo servicio: '.$service->guide_number],
          [
            "app_id" => "073dab20-d9fb-4fb5-b226-6e1ee09acfdc",
            "included_segments" => ["All"],
            "data" => ["foo" => "bar"],
            "isChromeWeb" => true,
            "isChrome" => true
          ]
        );

        $ch=curl_init();
        $post = [
          'account' => '10017919', //número de usuario
          'apiKey' => '0RglUrhFkVdlqviDxyD9Z5hndgWGVt', //clave API del usuario
          'token' => '41e2e15ec6e63a1abd7f0e810492dce2', // Token de usuario
          'toNumber' => '57'.$user->profile->mobile, //número de destino
          'sms' => 'CVExpress Consulta la Guia: https://www.cvexpress.club' , // mensaje de texto
          'flash' => '0', //mensaje tipo flash
          'sendDate'=> time(), //fecha de envío del mensaje
          'isPriority' => 0, //mensaje prioritario
          'sc'=> '899991', //código corto para envío del mensaje de texto
          'request_dlvr_rcpt' => 0, //mensaje de texto con confirmación de entrega al celular
        ];

        $url = "https://api101.hablame.co/api/sms/v2.1/send/"; //endPoint: Primario
        // $url = "https://api102.hablame.co/api/sms/v2.1/send/";
        curl_setopt ($ch,CURLOPT_URL,$url) ;
        curl_setopt ($ch,CURLOPT_POST,1);
        curl_setopt ($ch,CURLOPT_POSTFIELDS, $post);
        curl_setopt ($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch,CURLOPT_CONNECTTIMEOUT ,3);
        curl_setopt ($ch,CURLOPT_TIMEOUT, 20);
        $response= curl_exec($ch);
        curl_close($ch);
        $response= json_decode($response ,true) ;

        //La respuesta estará alojada en la variable $response

        if ($response["status"]== '1x100' ){
          // echo 'El SMS se ha enviado exitosamente:'.PHP_EOL;
          $sms = 'El SMS se ha enviado exitosamente:'.PHP_EOL;
        } else {
          // echo 'Ha ocurrido un error:'.$response["error_description"].'('.$response ["status" ]. ')'. PHP_EOL;
          $sms = 'Ha ocurrido un error:'.$response["error_description"].'('.$response ["status" ]. ')'. PHP_EOL;
        }

        $data['sms'] = $sms;
        $data['post'] = $post;
        return $data;

      } else {
        throw new \yii\web\NotFoundHttpException;
      }
    } else {
      throw new \yii\web\HttpException(405);
    }
  }
}
