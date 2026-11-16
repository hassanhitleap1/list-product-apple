<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\IphoneModels;
use app\models\TradeInRequest;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\MethodNotAllowedHttpException;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
        return $this->render('index', [
            'catalog' => IphoneModels::catalog(),
            'checklist' => Yii::$app->params['tradeInChecklist'],
        ]);
    }

    /**
     * Customer sends their trade-in request: name, phone number, chosen phone,
     * answers and photos. The offer is worked out again here from the answers.
     * Answers with JSON: {ok: true, id} or {ok: false, error}.
     *
     * @return array
     */
    public function actionSubmitRequest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        if (!$request->isPost) {
            throw new MethodNotAllowedHttpException('Use POST.');
        }
        $fail = function ($error, $code = 422) {
            Yii::$app->response->statusCode = $code;
            return ['ok' => false, 'error' => $error];
        };

        // A few requests per hour from one address is plenty for a real customer.
        $cache = Yii::$app->cache;
        $rateKey = ['trade-in-submit', $request->userIP];
        $sent = (int)$cache->get($rateKey);
        if ($sent >= 10) {
            return $fail('Too many requests. Please try again later or call us.', 429);
        }

        $model = new TradeInRequest();
        $model->name = $request->post('name');
        $model->phone = $request->post('phone');
        $model->ip = $request->userIP;
        $model->status = TradeInRequest::STATUS_NEW;

        $answers = json_decode((string)$request->post('answers', ''), true);
        $error = $model->applyChecklist($request->post('model_id'), is_array($answers) ? $answers : [], Yii::$app->params['tradeInChecklist']);
        if ($error) {
            return $fail($error);
        }

        // Check the photos before anything is saved.
        $files = UploadedFile::getInstancesByName('photos');
        if (!$files) {
            return $fail('Add at least one photo of your phone.');
        }
        if (count($files) > TradeInRequest::MAX_PHOTOS) {
            return $fail('You can send up to ' . TradeInRequest::MAX_PHOTOS . ' photos.');
        }
        $photos = [];
        foreach ($files as $i => $file) {
            if ($file->hasError) {
                return $fail('A photo could not be uploaded. It may be too large; try again.');
            }
            $info = @getimagesize($file->tempName);
            $ext = $info ? (TradeInRequest::PHOTO_TYPES[$info['mime']] ?? null) : null;
            if (!$ext) {
                return $fail('Photos must be JPG, PNG or WebP images.');
            }
            if ($file->size > TradeInRequest::PHOTO_MAX_BYTES) {
                return $fail('Each photo must be smaller than 8 MB.');
            }
            $photos[] = [$file, sprintf('%02d-%s.%s', $i + 1, Yii::$app->security->generateRandomString(12), $ext)];
        }

        $model->photos = Json::encode(array_column($photos, 1));
        if (!$model->validate()) {
            return $fail(implode(' ', $model->getFirstErrors()));
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $model->save(false);
            $dir = $model->getPhotoDir();
            FileHelper::createDirectory($dir);
            foreach ($photos as [$file, $fileName]) {
                if (!$file->saveAs($dir . '/' . $fileName)) {
                    throw new \RuntimeException('Could not save photo to ' . $dir);
                }
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            if (isset($dir) && is_dir($dir)) {
                FileHelper::removeDirectory($dir);
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $fail('Something went wrong while sending. Please try again.', 500);
        }

        $cache->set($rateKey, $sent + 1, 3600);
        return ['ok' => true, 'id' => $model->id];
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/iphone-models/index']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Back to the admin page they tried to open, or the prices page.
            return $this->goBack(['/iphone-models/index']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
