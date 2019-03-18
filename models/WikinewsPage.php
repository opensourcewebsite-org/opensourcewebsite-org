<?php

namespace app\models;
use app\models\WikinewsLanguage;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wikinews_page".
 *
 * @property int $id
 * @property int $language_id
 * @property string $title
 * @property int $group_id
 * @property int $pageid
 * @property int $created_by
 * @property int $created_at
 * @property int $parsed_at
 * @property object $language
 */
class WikinewsPage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wikinews_page';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
		
        return [
            [['language_id', 'title'], 'required'],
            [['title'], 'checkDateFormat'],
			 ['title', 'unique', 'targetAttribute' => 'title'],
			  ['title', 'match', 'pattern' => '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'(([0-9]{1,5})?\\/.*)?$/i'],
            [['language_id', 'group_id', 'pageid', 'created_by', 'created_at', 'parsed_at'], 'integer'],
        ];
    }
function checkDateFormat($attribute, $params){

	$model = WikinewsLanguage::find()->all();
	   
	 foreach($model as $key=>$val){
		$languageArrCustom[] = $val->code;
	} 
	
	 $titleArr =explode("//",$this->title);
	 
	 if(isset($titleArr[1])){
		 $titleNextArr =explode(".",$titleArr[1]);
		 
		 if (!in_array($titleNextArr[0], $languageArrCustom)) {
		     $this->addError('title','Please Enter Valid WikiNews URL');      
		  
		 }
		 
	 }
	 if (strpos($titleArr[1], ':') !== false or strpos($titleArr[1], '@') !== false or strpos($titleArr[1], '!') !== false  or strpos($titleArr[1], '#') !== false or strpos($titleArr[1], '$') !== false or strpos($titleArr[1], '%') !== false or strpos($titleArr[1], '^') !== false or strpos($titleArr[1], '&') !== false or strpos($titleArr[1], '*') !== false or strpos($titleArr[1], '(') !== false or strpos($titleArr[1], ')') !== false or strpos($titleArr[1], '.com') !== false or strpos($titleArr[1], '.org/wiki') == false) {
	
		 $this->addError('title','Please Enter Valid WikiNews URL');
			return;
	  }
	  
	  

}
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_id' => 'Language ID',
            'title' => 'Title',
            'group_id' => 'Group ID',
            'pageid' => 'Page ID',
            'created_by' => 'Created by',
            'created_at' => 'Created at',
            'parsed_at' => 'Parsed at',
        ];
    }

    public function getLanguage()
    {
        return $this->hasOne(WikinewsLanguage::class, ['id' => 'language_id']);
    }
}
