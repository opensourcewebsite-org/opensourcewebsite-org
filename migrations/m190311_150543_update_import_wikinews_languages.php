<?php

use yii\db\Migration;

/**
 * Class m190311_150543_update_import_wikinews_languages
 */
class m190311_150543_update_import_wikinews_languages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $languages = [
            'sr' => 'Српски / Srpski',
            'en' => 'English',
            'fr' => 'Français',
            'ru' => 'Русский',
            'de' => 'Deutsch',
            'pt' => 'Português',
            'pl' => 'Polski',
            'es' => 'Español',
            'zh' => '中文',
            'it' => 'Italiano',
            'ar' => 'العربية',
            'cs' => 'Čeština',
            'ca' => 'Català',
            'nl' => 'Nederlands',
            'el' => 'Ελληνικά',
            'ta' => 'தமிழ்',
            'sv' => 'Svenska',
            'uk' => 'Українська',
            'fa' => 'فارسی',
            'ro' => 'Română',
            'fi' => 'Suomi',
            'tr' => 'Türkçe',
            'ja' => '日本語',
            'li' => 'Limburgs',
            'sq' => 'Shqip',
            'no' => 'Norsk (Bokmål)',
            'eo' => 'Esperanto',
            'hu' => 'Magyar',
            'ko' => '한국어',
            'bs' => 'Bosanski',
            'he' => 'עברית',
            'bg' => 'Български',
            'th' => 'ไทย',
            'sd' => 'سنڌي، سندھی ، सिन्ध',
        ];

        foreach ($languages as $code => $name) {
            $this->insert('{{%wikinews_language}}', [
                'code' => $code,
                'name' => $name,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190311_150543_update_import_wikinews_languages cannot be reverted.\n";

        return false;
    }
}
