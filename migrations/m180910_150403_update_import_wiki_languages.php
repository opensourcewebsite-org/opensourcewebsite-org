<?php

use yii\db\Migration;

/**
 * Class m180910_150403_update_import_wiki_languages
 */
class m180910_150403_update_import_wiki_languages extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $languages = [
            'ar' => 'العربية',
            'az' => 'Azərbaycanca',
            'bg' => 'Български',
            'nan' => 'Bân-lâm-gú / Hō-ló-oē',
            'be' => 'Беларуская (Акадэмічная)',
            'ca' => 'Català',
            'cs' => 'Čeština',
            'da' => 'Dansk',
            'de' => 'Deutsch',
            'et' => 'Eesti',
            'el' => 'Ελληνικά',
            'en' => 'English',
            'es' => 'Español',
            'eo' => 'Esperanto',
            'eu' => 'Euskara',
            'fa' => 'فارسی',
            'fr' => 'Français',
            'gl' => 'Galego',
            'ko' => '한국어',
            'hy' => 'Հայերեն',
            'hi' => 'हिन्दी',
            'hr' => 'Hrvatski',
            'id' => 'Bahasa Indonesia',
            'it' => 'Italiano',
            'he' => 'עברית',
            'ka' => 'ქართული',
            'la' => 'Latina',
            'lt' => 'Lietuvių',
            'hu' => 'Magyar',
            'ms' => 'Bahasa Melayu',
            'min' => 'Bahaso Minangkabau',
            'nl' => 'Nederlands',
            'ja' => '日本語',
            'no' => 'Norsk (Bokmål)',
            'nn' => 'Norsk (Nynorsk)',
            'ce' => 'Нохчийн',
            'uz' => 'Oʻzbekcha / Ўзбекча',
            'pl' => 'Polski',
            'pt' => 'Português',
            'kk' => 'Қазақша / Qazaqşa / قازاقشا',
            'ro' => 'Română',
            'ru' => 'Русский',
            'simple' => 'Simple English',
            'ceb' => 'Sinugboanong Binisaya',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina',
            'sr' => 'Српски / Srpski',
            'sh' => 'Srpskohrvatski / Српскохрватски',
            'fi' => 'Suomi',
            'sv' => 'Svenska',
            'th' => 'ภาษาไทย',
            'tr' => 'Türkçe',
            'uk' => 'Українська',
            'ur' => 'اردو',
            'vi' => 'Tiếng Việt',
            'vo' => 'Volapük',
            'war' => 'Winaray',
            'zh' => '中文',
        ];

        foreach ($languages as $code => $name) {
            $this->insert('{{%wiki_language}}', [
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
        $this->truncateTable('{{%wiki_language}}');
    }
}
