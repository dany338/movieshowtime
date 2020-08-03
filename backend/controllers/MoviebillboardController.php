<?php

namespace backend\controllers;

use Yii;
use backend\models\Moviebillboard;
use backend\models\MoviebillboardSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\ModelEXCELZIPExporter;
/**
 * MoviebillboardController implements the CRUD actions for Moviebillboard model.
 */
class MoviebillboardController extends Controller
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
     * Lists all Moviebillboard models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MoviebillboardSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Moviebillboard model.
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
     * Creates a new Moviebillboard model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Moviebillboard();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Moviebillboard model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Moviebillboard model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Moviebillboard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Moviebillboard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Moviebillboard::findOne($id)) !== null) {
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
      $sql         = Moviebillboard::getSqlExport($params['year']);

      $modelCSVZIPExporter = new ModelEXCELZIPExporter();
      $modelCSVZIPExporter->exportToExcelsZIP($paramsQuery,
                                              $sql,
                                              "Report_Movie_billboard_".date('Y-m-d_h:i'));
    }
}
