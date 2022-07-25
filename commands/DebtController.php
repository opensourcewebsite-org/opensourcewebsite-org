<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\components\debt\Redistribution;
use app\components\debt\Reduction;
use app\models\Debt;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\db\Transaction;
use yii\helpers\Console;

/**
 * Class DebtController
 * Пользователи создают записи о том сколько должны денег друг другу.
 * После создания долга (и его подтверждении) система ищет цепочки контактов пользователя для аннулирования долгов и переноса долгов к пользователям с более высоким приоритетом.
 *
 * @package app\commands
 */
class DebtController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public $debugReduction;

    public static function formatConsoleArgument($value)
    {
        if (is_array($value)) {
            return base64_encode(json_encode($value));
        } else {
            return json_decode(base64_decode($value), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        if ('index' === $actionID) {
            $options[] = 'debugReduction';
        }

        return $this->optionsAppendLog($options);
    }

    /**
     * Testing:
     * 1. Пользователей в БД не должно быть много. (я тестил при 10ти). Хотя, скорее всего, после выполнения следующего пункта кол-во юзеров не важно.
     * 2. создаем между ними ВСЕ возможные контакты. Для этого запускаем:
     * `yii dataGenerator "Contact"  --interval=0` и ждем несколько секунд, пока не посыпятся серые блоки сообщений - это значит, что закончились возможные варианты
     * 3. теперь уже запускаем "вечную" генерацию только долгов:
     * `yii dataGenerator  "Debt"  --interval=0`
     * 4. Запускаем параллельно `yii debt` и видим как иногда мелькают зеленые строки - это занчит цепочка найдена и проведена
     * Зеленая строка выглядит так:
     * `amount=-63570106   group=1587112706.7923     108:8:1 -> 108:1:2 -> 108:2:8`
     * `amount` - это максимально возможная сумма, которую можно было списать с цепочки. В `debt.amount` вы найдете ту же самую сумму, только со знаком `+`
     * `group` - это `debt.group`. По нему можно найти все строки `Debt`. Например:
     * `SELECT * FROM debt WHERE group = 1587112706.7923`
     * `108:8:1 -> 108:1:2 -> 108:2:8` - это замкнутая цепочка `PK` Балансов. В данном случае мы видим цепочку из 3х балансов.
     * `108:8:1` == `currency_id : from_user_id : to_user_id` таблицы `debt_balance`
     *
     * Проверить целостность БД:
     * (что данные в таблицах `debt` & `debt_balance` синхронизированы)
     * `yii core/check-debt-balances`
     * (что созданы уникальные группы для каждой цепочки долгов)
     * php yii core/check-debt-unique-groups
     *
     * @throws \Throwable
     */
    public function actionIndex()
    {
        $reduction = new Reduction();
        $reduction->logger = function ($message, $format = []) {
            $this->output($message, $format);
        };
        if ($this->debugReduction) {
            $reduction->debug['DebtBalanceCondition'] = self::formatConsoleArgument($this->debugReduction);
            $reduction->debug['logConsole'] = true;
            $this->log = true;
        }
        $reduction->run();

        $redistribution = new Redistribution();
        $redistribution->logger = $reduction->logger;
        $redistribution->run();
    }

    /**
     * Create and confirm Debts reverse to specified.
     * Developer tool, that may be useful for debugging.
     *
     * @param int $id
     * @param float $group
     *
     * @throws \Throwable
     */
    public function actionCreateReverseDebts($id = 0, $group = 0.0)
    {
        $debts = $this->findDebts($id, $group);
        $function = $this->revert($debts);
        Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);

        $this->stdout('SUCCESS', Console::FG_GREEN);
    }

    /**
     * @param int   $id
     * @param float $group
     *
     * @return Debt[]
     */
    private function findDebts($id, $group): array
    {
        if (!$id && !$group) {
            $message = 'No required argument was passed. You must provide either "id" or "group".';
            throw new InvalidArgumentException($message);
        }
        if ($id && $group) {
            throw new InvalidArgumentException('You must provide only one argument: either "id" or "group".');
        }

        if ($id) {
            $debts = Debt::findAll($id);
        } else {
            $debts = Debt::find()->groupCondition($group)->all();
        }

        if (empty($debts)) {
            throw new InvalidArgumentException("Can't find any row in DB using provided args.");
        }

        return $debts;
    }

    /**
     * @param Debt[] $debts
     *
     * @return callable
     */
    private function revert(array $debts): callable
    {
        return static function () use ($debts) {
            $groupNew = Debt::generateGroup();

            foreach ($debts as $debt) {
                $debtNew = Debt::factoryBySource($debt, -$debt->amount, $groupNew);

                if (!$debtNew->save()) {
                    $message = "Unexpected error occurred: Fail to save Debt.\n";
                    $message .= 'Debt::$errors = ' . print_r($debtNew->errors, true);
                    throw new Exception($message);
                }
            }
        };
    }
}
