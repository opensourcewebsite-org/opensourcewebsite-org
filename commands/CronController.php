<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\CronJob;
use app\models\CronJobConsole;
use app\components\CustomConsole;
use yii\console\Exception;

/**
 * CronController is a cron manager.
 * It runs other commands, that chained in single thread (should be run one by one).
 * Instance https://github.com/opensourcewebsite-org/osw-devops/blob/master/pillar/prod/supervisor.sls#L22
 *
 * @property array $map
 * @property bool $log use param --log to show logs
 */
class CronController extends Controller
{
    use ControllerLogTrait;

    public const SLEEP_INTERVAL = 60; // seconds
    public const PREFIX = 'app\commands\\';
    public const POSTFIX = 'Controller';

    protected static array $map = [
        'TelegramBot',
        'AdOfferMatch',
        'AdSearchMatch',
        'VacancyMatch',
        'ResumeMatch',
        'CurrencyExchangeOrderMatch',
        //'Debt',
        'StellarCroupier',
        'StellarGiver',
    ];

    private $cronJobs;

    private ?string $currentBranchHash = null;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $model = new CronJobConsole();
        $model->setCronJobs(static::$map);
        $model->add();
        $model->clear();
        $this->currentBranchHash = $this->getCurrentGitBranchHeadHash();

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return $this->optionsAppendLog(parent::options($actionID));
    }

    /**
     * Main starter of all scripts
     */
    public function actionIndex()
    {
        $this->cronJobs = CronJobConsole::find()->all();

        if (empty($this->cronJobs)) {
            throw new Exception('Cron jobs not found');
        }

        while (true) {
            $session = Yii::$app->security->generateRandomString();
            $this->output(
                "[OPEN] session id: $session",
                [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
            );

            /** @var CronJobConsole $script */
            foreach ($this->cronJobs as $script) {
                if ($script->status !== CronJobConsole::STATUS_ON) {
                    continue;
                }

                $job = static::PREFIX . $script->name . static::POSTFIX;

                $this->output(
                    "[STARTED] $script->name",
                    [CustomConsole::FG_GREEN, CustomConsole::BOLD]
                );

                /** @var ControllerLogTrait|CronChainedInterface $controller */
                $controller = new $job(Yii::$app->controller->id, Yii::$app);
                $controller->log = $this->log;
                $controller->actionIndex();

                CronJob::updateAll(
                    [
                        'updated_at' => time(),
                    ],
                    [
                        'name' => $script->name,
                    ]
                );

                $this->output(
                    "[FINISHED] $script->name",
                    [CustomConsole::FG_GREEN, CustomConsole::BOLD]
                );
            }

            $this->output(
                "[CLOSED] session id: $session",
                [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
            );

            $this->output();

            if (!$this->isSameGitVersion()) {
                $this->output(
                    '[EXIT] Current git HEAD is updated, exiting!',
                    [CustomConsole::FG_RED, CustomConsole::BOLD]
                );

                return;
            }

            sleep(static::SLEEP_INTERVAL);
        }
    }

    private function isSameGitVersion(): bool
    {
        return $this->currentBranchHash === $this->getCurrentGitBranchHeadHash();
    }

    private function getCurrentGitBranchHeadHash(): ?string
    {
        if (($currentHeadFilename = $this->getCurrentBranchHeadRefFilename()) &&
            file_exists($currentHeadFilename) &&
            $hash = file_get_contents($currentHeadFilename)) {
            return trim($hash);
        }

        return null;
    }

    private function getCurrentBranchHeadRefFilename(): ?string
    {
        $projectDir = dirname(dirname(__FILE__));
        $mainHeadFile = "{$projectDir}/.git/HEAD";

        if (file_exists($mainHeadFile) &&
            ($headRef = file_get_contents($mainHeadFile)) &&
            preg_match('#^ref:(.+)$#', $headRef, $matches) &&
            ($currentHeadFilename = trim($matches[1] ?? null))
        ) {
            return "{$projectDir}/.git/{$currentHeadFilename}";
        }

        return null;
    }
}
