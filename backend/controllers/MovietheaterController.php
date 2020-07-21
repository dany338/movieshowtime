<?php

namespace backend\controllers;

use Yii;
use backend\models\Movietheater;
use backend\models\MovietheaterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\ModelEXCELZIPExporter;
/**
 * MovietheaterController implements the CRUD actions for Movietheater model.
 */
class MovietheaterController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
      return [
        'access' => [
          'class' => AccessControl::className(),
          'only' => ['index', 'view', 'create', 'update', 'export'],
          'rules' => [
            [
              'actions' => ['index', 'view', 'create', 'update', 'export'],
              'allow' => true,
              'roles' => ['admin'],
            ],
          ],
        ],
      ];
    }

    /**
     * Lists all Movietheater models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MovietheaterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Movietheater model.
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
     * Creates a new Movietheater model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Movietheater();
        $model->created_at = time();
        $model->updated_at = time();
        $model->user_id    = Yii::$app->user->identity->id;
        $model->status     = Movietheater::ACTIVE;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
          Yii::$app->session->setFlash('success', 'Data successfully saved!');
          return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Movietheater model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->updated_at = time();
        $model->user_id    = Yii::$app->user->identity->id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
          Yii::$app->session->setFlash('success', 'Data successfully saved!');
          return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the Movietheater model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Movietheater the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Movietheater::findOne($id)) !== null) {
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
      $sql         = Movietheater::getSqlExport($params['year']);

      $modelCSVZIPExporter = new ModelEXCELZIPExporter();
      $modelCSVZIPExporter->exportToExcelsZIP($paramsQuery,
                                              $sql,
                                              "Report_Movietheater_Movie_Show_Time_Finder_".date('Y-m-d_h:i'));
    }
}
