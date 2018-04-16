<?php


class hash
{


    public function compareString($expected, $actual)
    {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = self::byteLength($expected);
        $actualLength = self::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }



    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    public function validatePassword($password, $hash)
    {
        if (!is_string($password) || $password === '') {
            throw new InvalidParamException('Password must be a string and cannot be empty.');
        }

        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            throw new InvalidParamException('Hash is invalid.');
        }

        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }

        $test = crypt($password, $hash);
        $n = strlen($test);
        if ($n !== 60) {
            return false;
        }

        return $this->compareString($test, $hash);
    }




    /**
     * 验证密码是否正确
     * @throws yii\base\Exception
     */
    public function actionVerifyPassword()
    {
        $id = Yii::$app->request->post()['id'];
        $password = Yii::$app->request->post()['password'];
        if (!empty($password)) {
            $passwordHash = Yii::$app->security->generatePasswordHash($password);
            $user = User::findOne($id);
            if (!$user || !$user->validatePassword($password)) {
                echo json_encode(['error' => 1, 'msg' => Yii::t('app', 'Old password error')]);
            } else {
                echo json_encode(['error' => 0]);
            }
        } else {
            echo json_encode(['error' => 0]);
        }
    }
}



