<?php

namespace app\models;

use yii\base\Model;

/**
 * Signup form
 */
class WikiUrlForm extends Model
{
    public $url;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['url', 'required'],
            ['url', 'validateUrl'],
        ];
    }
    /**
     * Validates the url.
     * This method serves as the inline validation for url.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUrl($attribute, $params)
    {
        $valid = true;
        $atr = $this->$attribute;
        $validateUrl = preg_match('/^https:\/\/([a-z]{2}).wikinews.org\/wiki\/([A-Za-zА0-9%,_.-]+)/ui', $atr, $match);

        if (!$validateUrl) {
            $valid = false;
        }
        elseif ($match[1]) {
            $langCode = $match[1];
            $langValid = WikinewsLanguage::find()->where(['code'=>$langCode])->count();
            if(!$langValid) {
                $valid = false;
            }
        }
        else {
            $valid = false;
        }
        if (!$valid) {
            $this->addError($attribute, 'Url is not valid.');
        }
    }
}
