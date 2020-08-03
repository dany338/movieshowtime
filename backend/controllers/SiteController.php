<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use backend\models\Moviebillboard;
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'load-billboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // [
                    //   'actions' => ['index'],
                    //   'allow' => true,
                    //   'roles' => ['?'],
                    // ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

  public function actionLoadBillboard()
  {
    ini_set("upload_max_filesize","100M");
    ini_set("post_max_size","100M");
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', -1);
    ini_set('max_input_time',8600);

    $params  = Yii::$app->request->post();
    $mensaje         = '';
    $data            = [];
    $moviebillboard  = [];
    $exito      = 0;
    $statusCode = 200;
    if (isset($params['month']) && isset($params['year'])) {
      $month       = $params['month'];
      $year        = $params['year'];
      $db = \Yii::$app->db;
      ob_start();
      //> Start Transaction
      $transaction = $db->beginTransaction();
      try {
        $sql = Moviebillboard::getSql($month, $year);
        $consulta = $db->createCommand($sql);
        $params = [
          ':month'=> $month,
          ':year' => $year,
        ];
        $consulta->bindValues($params);
        $tareas   = $consulta->queryAll();
        if(count($moviebillboard) > 0){
          $exito      = 1;
          $mensaje    = '';
          $statusCode = 200;
        }
        $transaction->commit();
      } catch(\Exception $e) {
          $transaction->rollBack();
          throw $e;
      } catch(\Throwable $e) {
          $transaction->rollBack();
          throw $e;
      }
      ob_get_clean();
    } else {
      $mensaje    = 'The undefined month and year variable';
      $exito      = 0;
      $statusCode = 500;
    }

    $response = \Yii::$app->response;
    $response->statusCode = $statusCode;
    $response->format = \yii\web\Response::FORMAT_JSON;
    $response->data   = [
      'exito'          => $exito,
      'mensaje'        => $mensaje,
      'moviebillboard' => $moviebillboard,
    ];
  }
}
