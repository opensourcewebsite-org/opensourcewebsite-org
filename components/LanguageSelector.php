<?php

namespace app\components;

use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $langCookie = $app->request->cookies->getValue('language', null);

        $lang = \app\models\Language::find($langCookie)->one();

        //If the cookie exists and the value that it store is valid, then asign the language, otherwise check the browser language
        if ($langCookie != null && $lang != null) {
            $app->language = $langCookie;
        } else {
            $preferredLanguage = $app->request->getPreferredLanguage();
            $app->language = $preferredLanguage;
        }
    }
}
