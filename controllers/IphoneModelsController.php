<?php

namespace app\controllers;

use Yii;
use app\models\IphoneModels;
use app\models\IphoneModelsSearch;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * IphoneModelsController implements the CRUD actions for IphoneModels model.
 */
class IphoneModelsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                // Back office: signed-in users only. Guests are sent to the login page.
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        ['allow' => true, 'roles' => ['@']],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all IphoneModels models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new IphoneModelsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** Allowed photo types and the most a photo may weigh. */
    const PHOTO_TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    const PHOTO_MAX_BYTES = 3 * 1024 * 1024;

    /**
     * One photo per model, shared by all its storage sizes.
     * Shows every model with its current photo; upload, replace or remove in one save.
     * @return string|\yii\web\Response
     */
    public function actionPhotos()
    {
        $groups = IphoneModels::modelGroups();

        if ($this->request->isPost) {
            $remove = (array)$this->request->post('remove', []);
            $saved = 0;
            $removed = 0;
            $errors = [];
            $dir = Yii::getAlias('@webroot/uploads/phones');
            FileHelper::createDirectory($dir);

            foreach ($groups as $key => $g) {
                $file = UploadedFile::getInstanceByName('photo[' . $key . ']');
                $where = ['brand' => $g['brand'], 'name' => $g['name']];
                $link = trim((string)$this->request->post('link', [])[$key] ?? '');

                if ($link !== '' && !($file && !$file->hasError)) {
                    if ($link === $g['image']) {
                        continue;
                    }
                    if (strlen($link) > 1000 || !filter_var($link, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $link)) {
                        $errors[] = $g['name'] . ': the link must be a full web address starting with https://';
                        continue;
                    }
                    IphoneModels::updateAll(['image' => $link], $where);
                    $this->deletePhoto($g['image']);
                    $saved++;
                    continue;
                }

                if ($file && !$file->hasError) {
                    $ext = strtolower($file->extension);
                    $info = @getimagesize($file->tempName);
                    if (!isset(self::PHOTO_TYPES[$ext]) || !$info || $info['mime'] !== self::PHOTO_TYPES[$ext]) {
                        $errors[] = $g['name'] . ': use a JPG, PNG or WebP image.';
                        continue;
                    }
                    if ($file->size > self::PHOTO_MAX_BYTES) {
                        $errors[] = $g['name'] . ': the photo is larger than 3 MB.';
                        continue;
                    }
                    $fileName = Inflector::slug($g['brand'] . '-' . $g['name']) . '-' . time() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                    if (!$file->saveAs($dir . '/' . $fileName)) {
                        $errors[] = $g['name'] . ': the photo could not be saved. Check that web/uploads/phones is writable.';
                        continue;
                    }
                    IphoneModels::updateAll(['image' => 'uploads/phones/' . $fileName], $where);
                    $this->deletePhoto($g['image']);
                    $saved++;
                } elseif ($file && $file->hasError && $file->error !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = $g['name'] . ': the upload failed (the file may be too large for the server).';
                } elseif (in_array($key, $remove, true) && $g['image']) {
                    IphoneModels::updateAll(['image' => null], $where);
                    $this->deletePhoto($g['image']);
                    $removed++;
                }
            }

            $session = Yii::$app->session;
            if ($saved || $removed) {
                $session->addFlash('success', Yii::t('app', 'Photos saved: {saved}. Removed: {removed}.', ['saved' => $saved, 'removed' => $removed]));
            }
            foreach ($errors as $e) {
                $session->addFlash('error', $e);
            }
            if (!$saved && !$removed && !$errors) {
                $session->addFlash('info', Yii::t('app', 'Nothing changed. Choose a photo for a model first.'));
            }
            return $this->refresh();
        }

        return $this->render('photos', ['groups' => $groups]);
    }

    /** Deletes an uploaded photo file if no phone uses it any more. */
    private function deletePhoto($path)
    {
        if (!$path || strpos($path, 'uploads/phones/') !== 0 || IphoneModels::find()->where(['image' => $path])->exists()) {
            return;
        }
        $file = Yii::getAlias('@webroot/' . $path);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Displays a single IphoneModels model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new IphoneModels model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new IphoneModels();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing IphoneModels model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing IphoneModels model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the IphoneModels model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return IphoneModels the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IphoneModels::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
