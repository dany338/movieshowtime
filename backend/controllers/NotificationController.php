<?php

namespace backend\controllers;

use Yii;
use backend\models\Notification;
use backend\models\NotificationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\ModelEXCELZIPExporter;
/**
 * NotificationController implements the CRUD actions for Notification model.
 */
class NotificationController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
      return [
        'access' => [
          'class' => AccessControl::className(),
          'only' => ['index', 'view', 'export'],
          'rules' => [
            [
              'actions' => ['index', 'view', 'export'],
              'allow' => true,
              'roles' => ['admin'],
            ],
          ],
        ],
      ];
    }

    /**
     * Lists all Notification models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NotificationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Notification model.
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
     * Finds the Notification model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Notification the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Notification::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
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
      $sql         = Notification::getSqlExport($params['year']);

      $modelCSVZIPExporter = new ModelEXCELZIPExporter();
      $modelCSVZIPExporter->exportToExcelsZIP($paramsQuery,
                                              $sql,
                                              "Report_Notification_Movie_Show_Time_Finder_".date('Y-m-d_h:i'));
    }
}
