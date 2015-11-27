<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

use app\models\User;

class LoginController extends Controller
{
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
		        'actions' => [
                    'index' => ['post']
		        ]
			],
			'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
						'actions' => ['index'],
                        'roles' => ['?'],
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

    public function actionIndex()
    {
        $params = Yii::$app->request->getBodyParams();

        $user = User::findByEmail(Yii::$app->request->getBodyParam('email'));

        if (!$user) {
            return [
                'success' => 0,
                'message' => 'No such user found'
            ];
        }

        $valid = $user->validatePassword(Yii::$app->request->getBodyParam('password'));

        if (!$valid) {
            return [
                'success' => 0,
                'message' => 'Incorrect password'
            ];
        }

        return [
            'success' => 1,
            'payload' => $user
        ];
    }
}
