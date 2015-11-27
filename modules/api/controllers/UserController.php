<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\api\components\ApiAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;

use yii\web\ForbiddenHttpException;

use app\models\User;

use app\modules\api\components\FileUploader;

class UserController extends Controller
{
	public function behaviors()
	{
		return [
			'authenticator' => [
				'class' => ApiAuth::className()
			],
			'verbs' => [
				'class' => VerbFilter::className(),
		        'actions' => [
		            'me' => ['get'],
					'all' => ['get'],
                    'client' => ['get'],
		            'create' => ['post'],
					'update' => ['put']
		        ]
			],
			'access' => [
                'class' => AccessControl::className(),
                'only' => ['me','all','client','create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
			'contentNegotiator' => [
	            'class' => ContentNegotiator::className(),
	            'formats' => [
	                'application/json' => Response::FORMAT_JSON,
	            ],
	        ]
		];
	}

    public function actionMe()
    {
		$user = User::findIdentity(Yii::$app->user->id);

		if (!$user) {
            return [
                'success' => 0,
                'payload' => 'Some error occurred'
            ];
        }

        return [
            'success' => 1,
            'payload' => $user
        ];
    }

	public function actionAll()
    {
        if (Yii::$app->user->identity->role != USER::ADMIN) {
            throw new ForbiddenHttpException("You are not allowed", 1);
        }

        $users = User::findAll([
            'status' => User::ACTIVE,
            'role' => User::CLIENT
        ]);

        return [
            'success' => 1,
            'payload' => $users
        ];
    }

    public function actionClient()
    {
        if (Yii::$app->user->identity->role != USER::ADMIN
			&& Yii::$app->user->id != Yii::$app->request->getQueryParam('id')) {
            throw new ForbiddenHttpException("You are not allowed", 1);
        }

        $projection = ['id', 'username', 'email', 'phone', 'role', 'access_token', 'profile_picture'];

        $user = User::find()->where(
            'id = :id and status = :status', [
                ':id' => Yii::$app->request->getQueryParam('id'),
                ':status' => User::ACTIVE
            ])->select($projection)->asArray()->one();

        return [
            'success' => 1,
            'payload' => $user
        ];
    }

	private function _addOrUpdate($params)
	{
		if ($params['id']) {
            $user = User::findOne([
                'id' => $params['id']
            ]);

			if (!$user) {
				return [
	                'success' => 0,
	                'message' => 'No such user exist'
	            ];
	        }
        } else {
            $user = new User();
        }

    	$user->username = $params['username'];
    	$user->email = $params['email'];
    	$user->phone = $params['phone'];
    	$user->password = $params['password'];

    	if (!$user->validate()) {
    		return [
    			'success' => 0,
    			'message' => $user->getErrors()
    		];
    	}

        if (count($_FILES)) {
            $uploader = new FileUploader($_FILES['profile_picture']);
            $fileName = md5($user->email . Yii::$app->security->generateRandomString());
            $path = Yii::$app->basePath . '/web/images/profile/' . $fileName . '.' . $uploader->extension();

            $uploadStatus = $uploader->save($path);

            if (!$uploadStatus['success']) {
                return [
                    'success' => 0,
                    'message' => $uploadStatus['error']
                ];
            }

            $user->profile_picture = $file_name . '.' . $uploader->extension();
        }

        if (!$user->save()) {
            return [
                'success' => 0,
                'message' => 'Some error occurred'
            ];
        }

    	return [
    		'success' => 1,
    		'payload' => $user
    	];
	}

    public function actionCreate()
    {
    	return _addOrUpdate(Yii::$app->request->getBodyParams());
    }

	public function actionUpdate()
    {
    	return _addOrUpdate(Yii::$app->request->getBodyParams());
    }

}
