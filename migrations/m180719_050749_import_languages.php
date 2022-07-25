<?php

use yii\db\Migration;

/**
 * Class m180719_050749_import_languages
 */
class m180719_050749_import_languages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->truncateTable('{{%language}}');

        $this->execute("INSERT INTO `language` (`code`, `name`, `name_ascii`) VALUES
        ('aa', 'Afaraf', 'Afar'),
        ('ab', 'аҧсуа бызшәа, аҧсшәа', 'Abkhaz'),
        ('ae', 'avesta', 'Avestan'),
        ('af', 'Afrikaans', 'Afrikaans'),
        ('ak', 'Akan', 'Akan'),
        ('am', 'አማርኛ', 'Amharic'),
        ('an', 'aragonés', 'Aragonese'),
        ('ar', 'العربية', 'Arabic'),
        ('as', 'অসমীয়া', 'Assamese'),
        ('av', 'авар мацӀ, магӀарул мацӀ', 'Avaric'),
        ('ay', 'aymar aru', 'Aymara'),
        ('az', 'azərbaycan dili', 'Azerbaijani'),
        ('ba', 'башҡорт теле', 'Bashkir'),
        ('be', 'беларуская мова', 'Belarusian'),
        ('bg', 'български език', 'Bulgarian'),
        ('bh', 'भोजपुरी', 'Bihari'),
        ('bi', 'Bislama', 'Bislama'),
        ('bm', 'bamanankan', 'Bambara'),
        ('bn', 'বাংলা', 'Bengali, Bangla'),
        ('br', 'brezhoneg', 'Breton'),
        ('bs', 'bosanski jezik', 'Bosnian'),
        ('ca', 'català', 'Catalan'),
        ('ce', 'нохчийн мотт', 'Chechen'),
        ('ch', 'Chamoru', 'Chamorro'),
        ('co', 'corsu, lingua corsa', 'Corsican'),
        ('cr', 'ᓀᐦᐃᔭᐍᐏᐣ', 'Cree'),
        ('cs', 'čeština, český jazyk', 'Czech'),
        ('cv', 'чӑваш чӗлхи', 'Chuvash'),
        ('cy', 'Cymraeg', 'Welsh'),
        ('da', 'dansk', 'Danish'),
        ('de', 'Deutsch', 'German'),
        ('dv', 'ދިވެހި', 'Divehi, Dhivehi, Maldivian'),
        ('dz', 'རྫོང་ཁ', 'Dzongkha'),
        ('ee', 'Eʋegbe', 'Ewe'),
        ('el', 'ελληνικά', 'Greek (modern)'),
        ('en', 'English', 'English'),
        ('es', 'español', 'Spanish'),
        ('et', 'eesti, eesti keel', 'Estonian'),
        ('eu', 'euskara, euskera', 'Basque'),
        ('fa', 'فارسی', 'Persian (Farsi)'),
        ('ff', 'Fulfulde, Pulaar, Pular', 'Fula, Fulah, Pulaar, Pular'),
        ('fi', 'suomi, suomen kieli', 'Finnish'),
        ('fj', 'vosa Vakaviti', 'Fijian'),
        ('fo', 'føroyskt', 'Faroese'),
        ('fr', 'français, langue française', 'French'),
        ('fy', 'Frysk', 'Western Frisian'),
        ('ga', 'Gaeilge', 'Irish'),
        ('gd', 'Gàidhlig', 'Scottish Gaelic, Gaelic'),
        ('gl', 'galego', 'Galician'),
        ('gn', 'Avañe\'ẽ', 'Guaraní'),
        ('gu', 'ગુજરાતી', 'Gujarati'),
        ('gv', 'Gaelg, Gailck', 'Manx'),
        ('ha', '(Hausa) هَوُسَ', 'Hausa'),
        ('he', 'עברית', 'Hebrew (modern)'),
        ('hi', 'हिन्दी, हिंदी', 'Hindi'),
        ('ho', 'Hiri Motu', 'Hiri Motu'),
        ('hr', 'hrvatski jezik', 'Croatian'),
        ('ht', 'Kreyòl ayisyen', 'Haitian, Haitian Creole'),
        ('hu', 'magyar', 'Hungarian'),
        ('hy', 'Հայերեն', 'Armenian'),
        ('hz', 'Otjiherero', 'Herero'),
        ('id', 'Bahasa Indonesia', 'Indonesian'),
        ('ig', 'Asụsụ Igbo', 'Igbo'),
        ('ii', 'ꆈꌠ꒿ Nuosuhxop', 'Nuosu'),
        ('ik', 'Iñupiaq, Iñupiatun', 'Inupiaq'),
        ('io', 'Ido', 'Ido'),
        ('is', 'Íslenska', 'Icelandic'),
        ('it', 'italiano', 'Italian'),
        ('iu', 'ᐃᓄᒃᑎᑐᑦ', 'Inuktitut'),
        ('ja', '日本語 (にほんご)', 'Japanese'),
        ('jv', 'ꦧꦱꦗꦮ', 'Javanese'),
        ('ka', 'ქართული', 'Georgian'),
        ('kg', 'Kikongo', 'Kongo'),
        ('ki', 'Gĩkũyũ', 'Kikuyu, Gikuyu'),
        ('kj', 'Kuanyama', 'Kwanyama, Kuanyama'),
        ('kk', 'қазақ тілі', 'Kazakh'),
        ('kl', 'kalaallisut, kalaallit oqaasii', 'Kalaallisut, Greenlandic'),
        ('km', 'ខ្មែរ, ខេមរភាសា, ភាសាខ្មែរ', 'Khmer'),
        ('kn', 'ಕನ್ನಡ', 'Kannada'),
        ('ko', '한국어, 조선어', 'Korean'),
        ('kr', 'Kanuri', 'Kanuri'),
        ('ks', 'कश्मीरी, كشميري&lrm;', 'Kashmiri'),
        ('ku', 'Kurdî, كوردی&lrm;', 'Kurdish'),
        ('kv', 'коми кыв', 'Komi'),
        ('kw', 'Kernewek', 'Cornish'),
        ('ky', 'Кыргызча, Кыргыз тили', 'Kyrgyz'),
        ('lg', 'Luganda', 'Ganda'),
        ('li', 'Limburgs', 'Limburgish, Limburgan, Limburger'),
        ('ln', 'Lingála', 'Lingala'),
        ('lo', 'ພາສາລາວ', 'Lao'),
        ('lt', 'lietuvių kalba', 'Lithuanian'),
        ('lu', 'Tshiluba', 'Luba-Katanga'),
        ('lv', 'latviešu valoda', 'Latvian'),
        ('mg', 'fiteny malagasy', 'Malagasy'),
        ('mh', 'Kajin M̧ajeļ', 'Marshallese'),
        ('mi', 'te reo Māori', 'Māori'),
        ('mk', 'македонски јазик', 'Macedonian'),
        ('ml', 'മലയാളം', 'Malayalam'),
        ('mn', 'Монгол хэл', 'Mongolian'),
        ('mr', 'मराठी', 'Marathi (Marāṭhī)'),
        ('ms', 'bahasa Melayu, بهاس ملايو&lrm;', 'Malay'),
        ('mt', 'Malti', 'Maltese'),
        ('my', 'ဗမာစာ', 'Burmese'),
        ('na', 'Dorerin Naoero', 'Nauruan'),
        ('nb', 'Norsk bokmål', 'Norwegian Bokmål'),
        ('nd', 'isiNdebele', 'Northern Ndebele'),
        ('ne', 'नेपाली', 'Nepali'),
        ('ng', 'Owambo', 'Ndonga'),
        ('nl', 'Nederlands, Vlaams', 'Dutch'),
        ('nn', 'Norsk nynorsk', 'Norwegian Nynorsk'),
        ('no', 'Norsk', 'Norwegian'),
        ('nr', 'isiNdebele', 'Southern Ndebele'),
        ('nv', 'Diné bizaad', 'Navajo, Navaho'),
        ('ny', 'chiCheŵa, chinyanja', 'Chichewa, Chewa, Nyanja'),
        ('oc', 'occitan, lenga d\'òc', 'Occitan'),
        ('oj', 'ᐊᓂᔑᓈᐯᒧᐎᓐ', 'Ojibwe, Ojibwa'),
        ('om', 'Afaan Oromoo', 'Oromo'),
        ('or', 'ଓଡ଼ିଆ', 'Oriya'),
        ('os', 'ирон æвзаг', 'Ossetian, Ossetic'),
        ('pa', 'ਪੰਜਾਬੀ, پنجابی&lrm;', 'Panjabi, Punjabi'),
        ('pi', 'पाऴि', 'Pāli'),
        ('pl', 'język polski, polszczyzna', 'Polish'),
        ('ps', 'پښتو', 'Pashto, Pushto'),
        ('pt', 'português', 'Portuguese'),
        ('qu', 'Runa Simi, Kichwa', 'Quechua'),
        ('rc', 'Kréol Rénioné', 'Reunionese, Reunion Creole'),
        ('rm', 'rumantsch grischun', 'Romansh'),
        ('rn', 'Ikirundi', 'Kirundi'),
        ('ro', 'limba română', 'Romanian'),
        ('ru', 'Русский', 'Russian'),
        ('rw', 'Ikinyarwanda', 'Kinyarwanda'),
        ('sa', 'संस्कृतम्', 'Sanskrit (Saṁskṛta)'),
        ('sc', 'sardu', 'Sardinian'),
        ('sd', 'सिन्धी, سنڌي، سندھی&lrm;', 'Sindhi'),
        ('se', 'Davvisámegiella', 'Northern Sami'),
        ('sg', 'yângâ tî sängö', 'Sango'),
        ('si', 'සිංහල', 'Sinhalese, Sinhala'),
        ('sk', 'slovenčina, slovenský jazyk', 'Slovak'),
        ('sl', 'slovenski jezik, slovenščina', 'Slovene'),
        ('sm', 'gagana fa\'a Samoa', 'Samoan'),
        ('sn', 'chiShona', 'Shona'),
        ('so', 'Soomaaliga, af Soomaali', 'Somali'),
        ('sq', 'Shqip', 'Albanian'),
        ('sr', 'српски језик', 'Serbian'),
        ('ss', 'SiSwati', 'Swati'),
        ('st', 'Sesotho', 'Southern Sotho'),
        ('su', 'Basa Sunda', 'Sundanese'),
        ('sv', 'svenska', 'Swedish'),
        ('sw', 'Kiswahili', 'Swahili'),
        ('tg', 'тоҷикӣ, toçikī, تاجیکی&lrm;', 'Tajik'),
        ('th', 'ไทย', 'Thai'),
        ('tk', 'Türkmen, Түркмен', 'Turkmen'),
        ('tl', 'Wikang Tagalog', 'Tagalog'),
        ('tn', 'Setswana', 'Tswana'),
        ('to', 'faka Tonga', 'Tonga (Tonga Islands)'),
        ('tr', 'Türkçe', 'Turkish'),
        ('ts', 'Xitsonga', 'Tsonga'),
        ('tt', 'татар теле, tatar tele', 'Tatar'),
        ('tw', 'Twi', 'Twi'),
        ('ty', 'Reo Tahiti', 'Tahitian'),
        ('ug', 'ئۇيغۇرچە&lrm;, Uyghurche', 'Uyghur'),
        ('uk', 'Українська', 'Ukrainian'),
        ('ur', 'اردو', 'Urdu'),
        ('uz', 'Oʻzbek, Ўзбек, أۇزبېك&lrm;', 'Uzbek'),
        ('ve', 'Tshivenḓa', 'Venda'),
        ('vi', 'Tiếng Việt', 'Vietnamese'),
        ('vo', 'Volapük', 'Volapük'),
        ('wa', 'walon', 'Walloon'),
        ('wo', 'Wollof', 'Wolof'),
        ('xh', 'isiXhosa', 'Xhosa'),
        ('yi', 'ייִדיש', 'Yiddish'),
        ('yo', 'Yorùbá', 'Yoruba'),
        ('za', 'Saɯ cueŋƅ, Saw cuengh', 'Zhuang, Chuang'),
        ('zh', '中文 (Zhōngwén), 汉语, 漢語', 'Chinese'),
        ('zu', 'isiZulu', 'Zulu');
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180719_050749_import_languages cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180719_050749_import_languages cannot be reverted.\n";

        return false;
    }
    */
}
