<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */
?>

您好： <?= Html::encode($user->username) ?><br />


系统管理员已经将您设置为组织结构管理者，特此邮件通知。<br />
您登陆系统的用户名 <?= Html::encode($user->username); ?>, 密码 <?= Html::encode($password); ?>。<br />
<strong><font color="red">该邮件为涉密邮件，请您谨记该邮件内容</font></strong>
