<?php

namespace center\modules\Core;

use Yii;

class Module extends \yii\base\Module
{
    public $defaultRoute = 'default';
    public $defaultAction = 'index';
    public $controllerNamespace = 'center\modules\Core\controllers';

}
