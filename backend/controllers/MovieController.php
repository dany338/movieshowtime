<?php

namespace backend\controllers;

use Yii;
use backend\models\Movie;
use backend\models\MovieSearch;
use backend\models\Subscription;
use backend\models\Moviebillboard;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\ModelEXCELZIPExporter;
/**
 * MovieController implements the CRUD actions for Movie model.
 */
class MovieController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
      return [
        'access' => [
          'class' => AccessControl::className(),
          'only' => ['index', 'view', 'export', 'get-subscriptions', 'get-moviebillboards'],
          'rules' => [
            [
              'actions' => ['index', 'view', 'export', 'get-subscriptions', 'get-moviebillboards'],
              'allow' => true,
              'roles' => ['admin'],
            ],
          ],
        ],
      ];
    }

    /**
     * Lists all Movie models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MovieSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Movie model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Movie model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Movie the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Movie::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionGetSubscriptions()
    {
      $params  = Yii::$app->request->post();
      $data    = [];
      $message = '';
      $exito   = 0;
      if (isset($params['movie'])) {
        $sql = Subscription::getSql($params['movie']);
        $db  = \Yii::$app->db;
        ob_start();
        //> Start Transaction
        $transaction  = $db->beginTransaction();
        try {
          $consulta   = $db->createCommand($sql);
          $params = [
            ':movie' => $params['movie']
          ];
          $consulta->bindValues($params);
          $data       = $consulta->queryAll();
          $exito      = 0;
          $statusCode = 200;
          $message    = 'No records';
          if(count($data) > 0 ) {
            $exito      = 1;
            $message    = 'Records found';
            $statusCode = 200;
          } else {
            $data = [];
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
        $message    = 'The movie variable was not sent!';
        $exito      = 0;
        $statusCode = 500;
      }

      $response = \Yii::$app->response;
      $response->statusCode = $statusCode;
      $response->format = \yii\web\Response::FORMAT_JSON;
      $response->data   = [
        'exito'  => $exito,
        'message'=> $message,
        'data'   => $data,
      ];
    }

    public function actionGetMoviebillboards()
    {
      $params  = Yii::$app->request->post();
      $data    = [];
      $message = '';
      $exito   = 0;
      if (isset($params['movie'])) {
        $sql = Moviebillboard::getSql($params['movie']);
        $db  = \Yii::$app->db;
        ob_start();
        //> Start Transaction
        $transaction  = $db->beginTransaction();
        try {
          $consulta   = $db->createCommand($sql);
          $params = [
            ':movie' => $params['movie']
          ];
          $consulta->bindValues($params);
          $data       = $consulta->queryAll();
          $exito      = 0;
          $statusCode = 200;
          $message    = 'No records';
          if(count($data) > 0 ) {
            $exito      = 1;
            $message    = 'Records found';
            $statusCode = 200;
          } else {
            $data = [];
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
        $message    = 'The movie variable was not sent!';
        $exito      = 0;
        $statusCode = 500;
      }

      $response = \Yii::$app->response;
      $response->statusCode = $statusCode;
      $response->format = \yii\web\Response::FORMAT_JSON;
      $response->data   = [
        'exito'  => $exito,
        'message'=> $message,
        'data'   => $data,
      ];
    }

    public function actionExport()
    {
      ini_set("upload_max_filesize", "256M");
      ini_set("post_max_size", "256M");
      ini_set('max_execution_time', 0);
      ini_set('memory_limit', -1);
      ini_set('max_input_time', -1);
      set_time_limit(0);

      $params      = Yii::$app->request->queryParams;
      $paramsQuery = [':year' => $params['year']];
      $sql         = Movie::getSqlExport($params['year']);

      $modelCSVZIPExporter = new ModelEXCELZIPExporter();
      $modelCSVZIPExporter->exportToExcelsZIP($paramsQuery,
                                              $sql,
                                              "Report_Movie_Show_Time_Finder_".date('Y-m-d_h:i'));
    }
}
