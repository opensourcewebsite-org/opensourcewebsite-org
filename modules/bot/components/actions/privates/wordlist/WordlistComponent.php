<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Class WordlistBehavior
 * создан для того, чтобы не дублировать код для идентичных
 * контроллеров бота, управляющих списками фраз.
 * Он добавляет контроллеру необходимые экшены
 * для вывода списка, добавления, редактирования, удаления фраз ...
 *
 * после привязки поведения необходимо добавит кнопку в InlineKeyboard, указав
 * в callback_data роут к экшену списка фраз (по умолчанию word-list)
 *
 * Для использования
 *  1. нужно добавить в actions() методы возвращаемые компонентом
 *  ```
 * public function actions()
 * {
 *     return array_merge(
 *         parent::actions(),
 *         Yii::createObject([
 *             'class' => WordlistComponent::className(),
 *             'wordModelClass' => BotRouteAlias::className(),
 *             'modelAttributes' => [
 *                 'command' => VotebanController::createRoute('index')
 *             ]
 *         ])->actions()
 *     );
 * } * ```
 *
 * 3. Создать необходимые вьюшки в зависимости от параметров
 * - List: word-list.php (w-l.php, whitelist-word-list.php, whitelist-w-l.php)
 * - Enter: word-enter.php (w-e.php, whitelist-word-enter.php, whitelist-w-e.php)
 * - View: word-view.php (w-v.php, whitelist-word-view.php, whitelist-w-v.php)
 * - Change: word-change.php (w-c.php, whitelist-word-change.php, whitelist-w-c.php)
 *
 * @property string $wordModelClass - имя класса модели фраз
 * @property array $modelAttributes - дополнительные атрибуты модели (например,
 * если черный список и белый список фраз в одной таблице и отличается
 * по значению в столбце type, то можно передать ['type' => 'whitelist'], а для
 * списока алиасов роутов ['route' => '/voteban__index']  )
 * хотя можно было бы не делать этот параметр, никто не мешает создать модель
 * whitelist наследующую Phrase и использовать ее
 * @property string $actionGroupName - в случае когда в контроллере несколько
 * списков фраз, например whitelist и blacklist - можно дважды подцепить behavior
 * указав этот параметры, чтобы создаваемые экшены были уникальными. Если параметр
 * не указан, то например для списка фраз создается экшен word-list
 * или w-l (сокращенно от word-list, если параметр $short равен true, сокращение
 * применяется, т.к. есть ограничение API Telegram на длину поля callback_data
 * кнопок в InlineKeyboard). Если же данный параметр равен whitelist, то создается
 * экшен whitelist-word-list или whitelist-w-l при $short равном true
 * @property boolean $short - указывает применять ли сокращения для создаваемых
 * экшенов (подробнее в описании $actionGroupName)
 */
class WordlistComponent extends \yii\base\Component
{
    public $wordModelClass;
    public $modelAttributes = [];
    public $actionGroupName;
    public $short = false;
    public $buttons = [];
    public $options = [];

    public function actions()
    {
        if (!isset($this->wordModelClass)) {
            throw new InvalidConfigException('Class ' . self::class . ' require `wordModelClass` property to be set');
        }

        $prefix = isset($this->actionGroupName) ? $this->actionGroupName . '-' : '';

        $listActionId = $prefix . ($this->short ? 'w-l' : 'word-list');
        $viewActionId = $prefix . ($this->short ? 'w-v' : 'word-view');
        $selectActionId = $prefix . ($this->short ? 'w-s' : 'word-select');
        $enterActionId = $prefix . ($this->short ? 'w-e' : 'word-enter');
        $insertActionId = $prefix . ($this->short ? 'w-i' : 'word-insert');
        $updateActionId = $prefix . ($this->short ? 'w-u' : 'word-update');
        $changeActionId = $prefix . ($this->short ? 'w-c' : 'word-change');
        $deleteActionId = $prefix . ($this->short ? 'w-d' : 'word-delete');
        $changeFieldActionId = $prefix . 'w-c-f';
        $updateFieldActionId = $prefix . 'w-u-f';

        return [
            // word list
            $listActionId => [
                'class' => ListAction::class,
                'wordModelClass' => $this->wordModelClass,
                'viewActionId' => $viewActionId,
                'selectActionId' => $selectActionId,
                'enterActionId' => $enterActionId,
                'modelAttributes' => $this->modelAttributes,
                'options' => $this->options,
            ],
            // word view
            $viewActionId => [
                'class' => ViewAction::class,
                'wordModelClass' => $this->wordModelClass,
                'listActionId' => $listActionId,
                'changeActionId' => $changeActionId,
                'deleteActionId' => $deleteActionId,
                'buttons' => $this->buttons,
                'options' => $this->options,
            ],
            // word select
            $selectActionId => [
                'class' => SelectAction::class,
                'wordModelClass' => $this->wordModelClass,
                'listActionId' => $listActionId,
                'options' => $this->options,
            ],
            // word enter new
            $enterActionId => [
                'class' => EnterAction::class,
                'wordModelClass' => $this->wordModelClass,
                'listActionId' => $listActionId,
                'insertActionId' => $insertActionId,
                'options' => $this->options,
            ],
            // word save new
            $insertActionId => [
                'class' => InsertAction::class,
                'wordModelClass' => $this->wordModelClass,
                'listActionId' => $listActionId,
                'modelAttributes' => $this->modelAttributes,
                'options' => $this->options,
            ],
            // word enter old
            $changeActionId => [
                'class' => ChangeAction::class,
                'wordModelClass' => $this->wordModelClass,
                'updateActionId' => $updateActionId,
                'viewActionId' => $viewActionId,
                'options' => $this->options,
            ],
            // word save old
            $updateActionId => [
                'class' => UpdateAction::class,
                'wordModelClass' => $this->wordModelClass,
                'viewActionId' => $viewActionId,
                'options' => $this->options,
            ],
            // word delete
            $deleteActionId => [
                'class' => DeleteAction::class,
                'wordModelClass' => $this->wordModelClass,
                'listActionId' => $listActionId,
                'options' => $this->options,
            ],
            // field enter old
            $changeFieldActionId => [
                'class' => ChangeFieldAction::class,
                'wordModelClass' => $this->wordModelClass,
                'viewActionId' => $viewActionId,
                'buttons' => $this->buttons,
                'options' => $this->options,
            ],
            // field save old
            $updateFieldActionId => [
                'class' => UpdateFieldAction::class,
                'wordModelClass' => $this->wordModelClass,
                'viewActionId' => $viewActionId,
                'buttons' => $this->buttons,
                'options' => $this->options,
            ],
        ];
    }
}
