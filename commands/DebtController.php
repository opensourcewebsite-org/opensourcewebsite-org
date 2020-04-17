<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\components\debt\BalanceChecker;
use app\components\debt\Reduction;
use app\interfaces\ICronChained;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\VarDumper;

class DebtController extends Controller implements ICronChained
{
    use ControllerLogTrait;

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return $this->optionsAppendLog(parent::options($actionID));
    }

    /**
     * Check that there are no data collision between DB tables `debt` and `debt_balance`.
     * Should be valid next formula: `debt_balance.amount = sumOfAllDebt(Credits) - sumOfAllDebt(Deposits)`
     *
     * @throws \yii\db\Exception
     */
    public function actionCheckBalance()
    {
        //in this action no sense to disable log.
        $this->log = true;
        $this->outputLogState();

        $errors = (new BalanceChecker)->run();

        if (null === $errors) {
            $this->output('There are no appropriate rows in DB table `debt`. Nothing to analyze.', [Console::BG_GREY]);
        } elseif (empty($errors)) {
            $this->output('SUCCESS: no bugs found.', [Console::FG_GREEN]);
        } else {
            $count = count($errors);
            $message = "ERROR: found $count data collisions!\n" . VarDumper::dumpAsString($errors);
            $this->output($message, [Console::FG_RED]);
        }
    }

    /**
     * Есть два сценария тестирования скриптов, в зависимости от цели:
     *
     * 1. **Производительность**
     *      Просто запустить два параллельных скрипта:
     * ```
     * yii dataGenerator  "*"  --interval=0
     * yii cron
     * ```
     *      Это будет максимально похоже на живое. Но никаких или почти никаких Взаимовычетов не будет происходить.
     *      Потому, что генератор рандомный. И вероятность того, что сгенерируются именно зацикленные цепочки долгов
     *          очень-очень мала. Не знаю сколько нужно ждать.
     * 2. **Работоспособность**  (правильно ли скрипт работает)
     *      Поэтому если хочется увидеть много взаимовычетов, то нужно сделать так:
     *          1. Юзеров в БД не должно быть много. (я тестил при 10ти). Хотя, скорее всего, после выполнения следующего
     *              пункта кол-во юзеров не важно.
     *          2. создаем между ними ВСЕ возможные контакты. Для этого запускаем
     *              `yii dataGenerator "Contact"  --interval=0` и ждем несколько секунд, пока не посыпятся серые блоки
     *              сообщений - это значит, что закончились возможные варианты
     *          3. теперь уже запускаем "вечную" генерацию только долгов:
     *              `yii dataGenerator  "Debt"  --interval=0`
     *          4. Запускаем параллельно `yii cron` и видим как иногда мелькают зеленые строки - это занчит
     *              цепочка найдена и проведена
     *              Зеленая строка выглядит так:
     *                 `amount=-63570106   group=1587112706.7923     108:8:1 -> 108:1:2 -> 108:2:8`
     *                 `amount` - это максимально возможная сумма, которую можно было списать с цепочки. В `debt.amount`
     *                      вы найдете ту же самую сумму, только со знаком `+`
     *                 `group` - это `debt.group`. По нему можно найти все строки `Debt`. Например:
     *                      `SELECT * FROM debt WHERE group = 1587112706.7923`
     *                 `108:8:1 -> 108:1:2 -> 108:2:8` - это замкнутая цепочка `PK` Балансов. В
     *                      данном случае мы видим цепочку из 3х балансов.
     *                      `108:8:1` == `currency_id : from_user_id : to_user_id` таблицы `debt_balance`
     *
     * **Проверить целостность БД**:
     *     (что данные в таблицах `debt` & `debt_balance` синхронизированы)
     *     `yii debt/check-balance`
     *
     * @throws \Throwable
     */
    public function actionIndex()
    {
        $class = Reduction::class;
        $this->output("Running $class ...");

        $reduction = new Reduction();
        $reduction->logger = function ($message, $format = []) {
            $this->output($message, $format);
        };
        $reduction->run();
    }
}
