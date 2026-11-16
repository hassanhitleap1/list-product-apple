<?php

namespace app\controllers;

use Yii;
use app\models\TradeInRequest;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Back office: requests customers sent from the calculator (name, phone, photos).
 */
class TradeInRequestsController extends Controller
{
    public function behaviors()
    {
        return [
            // Staff only: customer names, phone numbers and photos are private.
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'status' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @param string|null $status new | done | null for all
     */
    public function actionIndex($status = TradeInRequest::STATUS_NEW)
    {
        $query = TradeInRequest::find();
        if (in_array($status, [TradeInRequest::STATUS_NEW, TradeInRequest::STATUS_DONE], true)) {
            $query->where(['status' => $status]);
        } else {
            $status = 'all';
        }
        $q = trim((string)Yii::$app->request->get('q', ''));
        if ($q !== '') {
            $digits = TradeInRequest::normalizePhone($q);
            $query->andWhere(['or', ['like', 'name', $q], ['like', 'phone', $digits], ['like', 'device', $q]]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['id' => SORT_DESC], 'attributes' => ['id', 'name', 'created_at', 'offer_high']],
                'pagination' => ['pageSize' => 30],
            ]),
            'status' => $status,
            'q' => $q,
            'counts' => [
                'new' => TradeInRequest::countNew(),
                'done' => (int)TradeInRequest::find()->where(['status' => TradeInRequest::STATUS_DONE])->count(),
            ],
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /** Streams one of the request's photos (they are not in web/). */
    public function actionPhoto($id, $file)
    {
        $model = $this->findModel($id);
        if (!in_array($file, $model->getPhotoList(), true)) {
            throw new NotFoundHttpException('Photo not found.');
        }
        $path = $model->getPhotoDir() . '/' . basename($file);
        if (!is_file($path)) {
            throw new NotFoundHttpException('Photo not found.');
        }
        $response = Yii::$app->response;
        $response->headers->set('Cache-Control', 'private, max-age=86400');
        return $response->sendFile($path, $file, ['inline' => true]);
    }

    /** Mark as done, or back to new. */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $model->status = $model->status === TradeInRequest::STATUS_DONE ? TradeInRequest::STATUS_NEW : TradeInRequest::STATUS_DONE;
        $model->save(false, ['status']);
        Yii::$app->session->addFlash('success', 'Request #' . $model->id . ($model->status === TradeInRequest::STATUS_DONE ? ' marked as done.' : ' moved back to new.'));
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->addFlash('success', 'Request #' . $model->id . ' and its photos were deleted.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = TradeInRequest::findOne(['id' => (int)$id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('This request does not exist.');
    }
}
