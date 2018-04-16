<?php
namespace center\modules\report\controllers;

use center\modules\report\models\detail\EfficiencyBase;
use yii;
use  yii\helpers\Json;
use center\modules\report\models\Dashboard;
use center\controllers\ValidateController;
use center\modules\report\models\detail\BaseModel;
use center\modules\report\models\OnlineReportPoint;
use center\modules\report\models\DashboardReports;

class DashboardController extends ValidateController
{
    public function actionIndex()
    {
        $model = new Dashboard();


        return $this->render('index', [

        ]);
    }
}