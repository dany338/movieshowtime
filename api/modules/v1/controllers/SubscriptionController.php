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
      'only' => ['create','update','search','solicitar'],
      'rules' => [
        [
          'allow' => true,
          'actions' => ['create','update','search','solicitar'],
          'roles' => ['@'],
        ],
      ]
    ];
    $behaviors['verbs'] = [
      'class' => VerbFilter::className(),
      'actions' => [
        'search' => ['GET'],
        'create' => ['POST'],
        'solicitar' => ['POST'],
        'update' => ['PUT']
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
    unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view'], $actions['search'], $actions['solicitar']); // $actions['update']
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

      if(!isset($requests['metodo_pago'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['service_type'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['end_date'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['start_address'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['end_address'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['toWaypoints'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['round_trip'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['declared_value'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['description'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['coupon_code'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['distance'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['total'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['fullName'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['mobile'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['identification'])) {
        throw new \yii\web\HttpException(412);
      }

      if(!isset($requests['email'])) {
        throw new \yii\web\HttpException(412);
      }

      $auth_key = $requests2['access-token'];
      $author   = User::find()->where(['auth_key' => $auth_key])->one();
      $uidAuthor= $author->id;

      $data = [];
      $metodo_pago    = $requests['metodo_pago']; // Metodo de Pago
      $service_type   = $requests['service_type']; // Tipo de servicio
      $end_date       = $requests['end_date']; // Fecha Recogida
      $start_address  = $requests['start_address']; // Dirección Recogida
      $end_address    = $requests['end_address']; // Dirección Entrega Principal
      $toWaypoints    = $requests['toWaypoints']; // Direcciones de Entrega Secundarias
      $round_trip     = $requests['round_trip']; // Volver a la primera dirección?
      $declared_value = $requests['declared_value']; // Valor declarado de la mercancia
      $description    = $requests['description']; // Descripción de la mercancia a recoger
      $coupon_code    = $requests['coupon_code']; //Código promocional de descuento
      $distance       = $requests['distance']; // Distancia en kilometros del recorrido
      $total          = $requests['total']; // Valor total del servicio de acuerdo a la distancia a recorrer
      $fullName       = $requests['fullName']; // Nombre del cliente completo
      $mobile         = $requests['mobile']; // Teléfono celular del cliente
      $identification = $requests['identification']; // Número de identificación del cliente
      $email          = $requests['email']; // Correo Electronico del cliente

      $id_coupon_code = NULL;
      $uid_courier    = NULL;
      $couponCode     = CouponCode::find()->where(['code' => $coupon_code])->one();

      if($couponCode) {
        $id_coupon_code = $couponCode->id;
        $valorDescuento = $couponCode->value;
        if($total > $valorDescuento )
          $total -= $valorDescuento;
      }

      // Proceso para verificar si el cliente tiene usuario si no lo crea para asociarlo al servicio solicitado
      $user = User::find()->where(['email' => $email])->one();
      $password = 0;
      if( $user === null ) {
        $arremail  = explode('@',$email);
        $name      = $fullName;
        $username  = $arremail[0];
        $userCount = User::find()->where(['username' => $username])->count();

        if( $userCount > 0 ) {
          $username .= $userCount;
        }

        $password = Password::generate(8);

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
          $assign  = $manager->getAssignment('cliente',$user->id);
          if( $assign === null )
            $manager->assign($manager->getItem('cliente'),$user->id);
        }

        $profile                      = $user->profile;//$profile = new Profile();
        $profile->idTipo              = 2;
        $profile->user_id             = $user->id;
        $profile->name                = $name;
        $profile->public_email        = $email;
        $profile->gravatar_email      = $email;
        $profile->gravatar_id         = 'b95021aad667876effd8c427382edf4c';
        $profile->location            = 'Bogota';
        $profile->website             = 'https://www.cvexpress.club';
        $profile->picture             = 'https://www.cvexpress.club/mensajeros/backend/web/img/default_avatar_male.jpg';
        $profile->bio                 = 'Cliente Registrado a traves de CV Express';
        $profile->timezone            = 'America/Bogota';
        $profile->mobile              = $mobile;
        $profile->balance             = 0;
        $profile->identification_card = $identification;

        if($profile->save(false)) {
          ;
        }
      }
      // Fin del proceso

      if($user) {
        $password = Password::generate(8);
        $user->password_hash = Yii::$app->security->generatePasswordHash($password, Yii::$app->getModule('user')->cost);
        $user->auth_key      = Yii::$app->security->generateRandomString();
        $user->save(false);
        $profile                    = $user->profile;
        $profile->name              = $fullName;
        $profile->mobile            = $mobile;
        if($profile->save(false)) {
          ;
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
//
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

  public function actionSolicitar()
  {
    date_default_timezone_set('America/Bogota');
    if(Yii::$app->request->isPost) {
      $requests    = \Yii::$app->request->post();
      $requests2   = \Yii::$app->request->get();

      if(!isset($requests['start_address'])) {
        throw new \yii\web\HttpException(412, 'El campo start_address no esta definido!');
      }

      if(!isset($requests['end_address'])) {
        throw new \yii\web\HttpException(412, 'El campo end_address no esta definido!');
      }

      if(!isset($requests['toWaypoints'])) {
        throw new \yii\web\HttpException(412, 'El campo toWaypoints no esta definido!');
      }

      if(!isset($requests['distance'])) {
        throw new \yii\web\HttpException(412, 'El campo distance no esta definido!');
      }

      if(!isset($requests['total'])) {
        throw new \yii\web\HttpException(412, 'El campo total no esta definido!');
      }

      $auth_key = $requests2['access-token'];
      $author   = User::find()->where(['auth_key' => $auth_key])->one();
      $uidAuthor= $author->id;

      $data = [];
      $metodo_pago    = 1; // Metodo de Pago
      $service_type   = 3; // Tipo de servicio
      $end_date       = time(); // Fecha Recogida
      $start_address  = $requests['start_address']; // Dirección Recogida
      $end_address    = $requests['end_address']; // Dirección Entrega Principal
      $toWaypoints    = $requests['toWaypoints']; // Direcciones de Entrega Secundarias
      $round_trip     = 0; // Volver a la primera dirección?
      $declared_value = 0; // Valor declarado de la mercancia
      $description    = 'Solicitud de servicio a traves de whatsapp'; // Descripción de la mercancia a recoger
      $coupon_code    = 0; //Código promocional de descuento
      $distance       = $requests['distance']; // Distancia en kilometros del recorrido
      $total          = $requests['total']; // Valor total del servicio de acuerdo a la distancia a recorrer
      $fullName       = 'Cliente Solicitando Servicio'; // Nombre del cliente completo
      $mobile         = ''; // Teléfono celular del cliente
      $identification = 0; // Número de identificación del cliente
      $email          = ''; // Correo Electronico del cliente

      $id_coupon_code = NULL;
      $uid_courier    = NULL;
      $couponCode     = CouponCode::find()->where(['code' => $coupon_code])->one();

      if($couponCode) {
        $id_coupon_code = $couponCode->id;
        $valorDescuento = $couponCode->value;
        if($total > $valorDescuento )
          $total -= $valorDescuento;
      }

      // Proceso para verificar si el cliente tiene usuario si no lo crea para asociarlo al servicio solicitado
      // $user = User::find()->where(['email' => $email])->one();
      // $password = 0;
      // if( $user === null ) {
      //   $arremail  = explode('@',$email);
      //   $name      = $fullName;
      //   $username  = $arremail[0];
      //   $userCount = User::find()->where(['username' => $username])->count();

      //   if( $userCount > 0 ) {
      //     $username .= $userCount;
      //   }

      //   $password = Password::generate(8);

      //   $user = new User();
      //   $user->username      = $username;
      //   $user->email         = $email;
      //   $user->password_hash = Yii::$app->security->generatePasswordHash($password, Yii::$app->getModule('user')->cost);
      //   $user->auth_key      = Yii::$app->security->generateRandomString();
      //   $user->confirmed_at  = time();
      //   $user->created_at    = time();
      //   $user->updated_at    = time();
      //   $user->flags         = 0;

      //   if($user->save(false)) {
      //     $manager = Yii::$app->authManager;
      //     $assign  = $manager->getAssignment('cliente',$user->id);
      //     if( $assign === null )
      //       $manager->assign($manager->getItem('cliente'),$user->id);
      //   }

      //   $profile                      = $user->profile;//$profile = new Profile();
      //   $profile->idTipo              = 2;
      //   $profile->user_id             = $user->id;
      //   $profile->name                = $name;
      //   $profile->public_email        = $email;
      //   $profile->gravatar_email      = $email;
      //   $profile->gravatar_id         = 'b95021aad667876effd8c427382edf4c';
      //   $profile->location            = 'Bogota';
      //   $profile->website             = 'https://www.cvexpress.club';
      //   $profile->picture             = 'https://www.cvexpress.club/mensajeros/backend/web/img/default_avatar_male.jpg';
      //   $profile->bio                 = 'Cliente Registrado a traves de CV Express';
      //   $profile->timezone            = 'America/Bogota';
      //   $profile->mobile              = $mobile;
      //   $profile->balance             = 0;
      //   $profile->identification_card = $identification;

      //   if($profile->save(false)) {
      //     ;
      //   }
      // }
      // Fin del proceso

      // if($user) {
        // $profile                    = $user->profile;
        // $profile->name              = $fullName;
        // $profile->mobile            = $mobile;
        // if($profile->save(false)) {
        //   ;
        // }

        $service = new Service();
        $service->uid               = 1;
        $service->id_service_type   = $service_type;
        $service->id_service_status = 1;
        $service->id_coupon_code    = $id_coupon_code;
        $service->uid_courier       = $uid_courier;
        $service->id_method         = $metodo_pago;
        $service->start_date        = time();
        $service->end_date          = $end_date;
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

          $user = User::findOne(1);
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

        // $params = [
        //   'email'    => $email,
        //   'service'  => $service,
        //   'user'     => $user,
        //   'password' => $password
        // ];
        // // Enviando el correo de confirmación del servicio
        // $user->mailer->sendServiceMessage($params);

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

        // $twilioService = Yii::$app->Yii2Twilio->initTwilio();

        // try {
        //     $message = $twilioService->account->messages->create(
        //         "+573127776585", // To a number that you want to send sms
        //         array(
        //         "from" => "+17177077105",   // From a number that you are sending
        //         "body" => "CV Express guia: ".$service->guide_number." Ingresar: http://bit.ly/2Gfyxqa",
        //     ));
        // } catch (\Twilio\Exceptions\RestException $e) {
        //         echo $e->getMessage();
        // }

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

        return $data;

      // } else {
      //   throw new \yii\web\NotFoundHttpException;
      // }
    } else {
      throw new \yii\web\HttpException(405);
    }
  }

  public function actionUpdate()
  {
    date_default_timezone_set('America/Bogota');
    if(Yii::$app->request->isPut) {
      $requests    = \Yii::$app->request->post();
      $requests2   = \Yii::$app->request->get();

    } else {
      throw new \yii\web\HttpException(405);
    }
  }

  public function actionSearch()
  {
    if(Yii::$app->request->isGet) {
      $requests    = \Yii::$app->request->get();

      if(!isset($requests['guide'])) {
        throw new \yii\web\HttpException(412);
      }

      $auth_key = $requests['access-token'];
      $author   = User::find()->where(['auth_key' => $auth_key])->one();
      $uidAuthor= $author->id;

      $data = [];
      $guide_number   = $requests['guide']; // Número de guia

      $service = Service::find()->where(['guide_number' => $guide_number])->one();
      if($service) {
        $user        = User::findOne($service->uid);
        $courier     = User::findOne($service->uid_courier);
        $dataAddress = [];
        foreach ($service->serviceAddresses as $key => $serviceAddres) {
          $dataAddress[] = [
            'end_address' => $serviceAddres->end_address
          ];
        }

        $data     = [
          'id'             => $service->id,
          'uid'            => $user->profile->name,
          'service_type'   => $service->serviceType->name,
          'service_status' => $service->serviceStatus->name,
          'coupon_code'    => (isset($service->couponCode->code)) ? $service->couponCode->code : '',
          'courier_name'   => ($courier) ? $courier->profile->name : 'No Asignado, contacte via whatsapp al administrador',
          'courier_mobile' => ($courier) ? $courier->profile->mobile : '3127776585',
          'courier_picture'=> ($courier) ? $courier->profile->picture : 'https://www.cvexpress.club/mensajeros/backend/web/img/default_avatar_male.jpg',
          'start_date'     => date('d M, Y g:i A', $service->start_date),
          'end_date'       => date('d M, Y g:i A', $service->end_date),
          'round_trip'     => $service->round_trip,
          'start_address'  => $service->start_address,
          'description'    => $service->description,
          'declared_value' => $service->declared_value,
          'total'          => number_format($service->total, 0, ',', ','),
          'distance'       => $service->distance,
          'guide_number'   => $service->guide_number,
          'picture_service'=> $service->picture_service,
          'state'          => $service->state,
          'created_at'     => date('d M, Y g:i A', $service->created_at),
          'updated_at'     => date('d M, Y g:i A', $service->updated_at),
          'service_address'=> $dataAddress
        ];

        return $data;
      } else {
        throw new \yii\web\NotFoundHttpException;
      }
    } else {
      throw new \yii\web\HttpException(405);
    }
  }

  /**
   * Returns a random integer in the range $min..$max inclusive.
   *
   * Substitutes for the random_int() in PHP 7.
   *
   * @param int $min Minimum value of the returned integer.
   * @param int $max Maximum value of the returned integer.
   * @return int The generated random integer.
   * @throws InvalidParamException On parameter type or range error.
   * @throws Exception If something goes wrong.
   */
  public function generateRandomInt($min, $max)
  {
      if (function_exists('random_int')) {
          return random_int($min, $max);
      }
      if (!is_int($min)) {
          throw new InvalidParamException('First parameter ($min) must be an integer');
      }
      if (!is_int($max)) {
          throw new InvalidParamException('Second parameter ($max) must be an integer');
      }
      if ($min > $max) {
          throw new InvalidParamException('First parameter ($min) must be no greater than second parameter ($max)');
      }
      if ($min === $max) {
          return $min;
      }
      // $range is a PHP float if the expression exceeds PHP_INT_MAX.
      $range = $max - $min + 1;
      if (is_float($range)) {
          $mask = null;
      } else {
          // Make a bit mask of (the next highest power of 2 >= $range) minus one.
          $mask = 1;
          $shift = $range;
          while ($shift > 1) {
              $shift >>= 1;
              $mask = ($mask << 1) | 1;
          }
      }
      $tries = 0;
      do {
          $bytes = Yii::$app->security->generateRandomKey(PHP_INT_SIZE);
          // Convert byte string to a signed int by shifting each byte in.
          $value = 0;
          for ($pos = 0; $pos < PHP_INT_SIZE; $pos += 1) {
              $value = ($value << 8) | ord($bytes[$pos]);
          }
          if ($mask === null) {
              // Use all bits in $bytes and check $value against $min and $max instead of $range.
              if ($value >= $min && $value <= $max) {
                  return $value;
              }
          } else {
              // Use only enough bits from $bytes to cover the $range.
              $value &= $mask;
              if ($value < $range) {
                  return $value + $min;
              }
          }
          $tries += 1;
      } while ($tries < self::RANDOM_INT_LOOP_LIMIT);
      // Worst case: this is as likely as self::RANDOM_INT_LOOP_LIMIT heads in as many coin tosses.
      throw new Exception('Unable to generate random int after ' . self::RANDOM_INT_LOOP_LIMIT . ' tries');
  }
}
