<?php

namespace common\extend;
/**
 * Created by PhpStorm.
 * User: liwenyu
 * Date: 15/6/15
 * Time: 15:43
 */
class CaptchaAction extends  \yii\captcha\CaptchaAction {
    /**
     * @var integer padding around the text. Defaults to 2.
     */
    public $padding = 5;
    public $fontFile = '@webroot/fonts/micross.ttf';

    /**
     * Generates a new verification code.
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        if ($this->minLength > $this->maxLength) {
            $this->maxLength = $this->minLength;
        }
        if ($this->minLength < 3) {
            $this->minLength = 3;
        }
        if ($this->maxLength > 20) {
            $this->maxLength = 20;
        }
        $length = mt_rand($this->minLength, $this->maxLength);

        $letters = '12345678901234567890';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $letters[mt_rand(0, 18)];
        }

        return $code;
    }
}