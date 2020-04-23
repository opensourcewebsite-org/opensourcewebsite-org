<?php

namespace app\modules\dataGenerator\commands;

use app\commands\traits\ControllerLogTrait;
use app\modules\dataGenerator\components\generators\ARGenerator;
use app\modules\dataGenerator\components\generators\ARGeneratorException;
use yii\console\controllers\FixtureController;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * This command Generates data of the specified model fixtures.
 */
class DefaultController extends FixtureController
{
    use ControllerLogTrait;

    /**
     * @var null|int count of models to generate. Default - continuous.
     */
    public $limit;
    /**
     * @var int seconds between model generation
     */
    public $interval       = 2;
    public $interactive    = false;
    public $namespace      = 'app\modules\dataGenerator\components\generators';
    public $globalFixtures = [];

    public function options($actionID)
    {
        $res = $this->optionsAppendLog(parent::options($actionID));
        if ($actionID === 'load') {
            $res[] = 'interval';
            $res[] = 'limit';
        }
        return $res;
    }

    /**
     * Generates data of the specified model fixtures.
     *
     * For example:
     *
     * ```
     * # Generate data of specified model fixtures: User and Contact.
     * yii dataGenerator "User, Contact"
     *
     * # load all available model fixtures found under 'app\modules\dataGenerator\components\generators'
     * yii dataGenerator "*"
     *
     * # load all model fixtures except User and Contact, with 5 seconds interval
     * yii dataGenerator "*, -User, -Contact" --interval=5
     * ```
     *
     * @param array $fixturesInput
     *
     * @return int return code
     * @throws Exception if the specified fixture does not exist.
     */
    public function actionLoad(array $fixturesInput = [])
    {
        if ($fixturesInput === []) {
            $this->printHelpMessage();
            return ExitCode::OK;
        }
        $this->outputLogState();

        return parent::actionLoad($fixturesInput);
    }

    public function actionUnload(array $fixturesInput = [])
    {
        //we don't need this action
        throw new Exception('This action is not supported');
    }

    /**
     * @param ARGenerator[] $fixtures the fixtures to be loaded.
     * @throws ARGeneratorException
     */
    public function loadFixtures($fixtures = null)
    {
        static $isFirst = true;
        if ($isFirst) {
            $isFirst = false;
            $this->output("\n[PROCESS] Loading fixtures:", [Console::FG_YELLOW, Console::BOLD]);
        }

        $while = $this->limit ?? true;

        while($while) {
            /** @var ARGenerator $fixtureRand */
            $fixtureRand = ARGenerator::getFaker()->randomElement($fixtures);
            $this->stdout($fixtureRand::classNameModel() . PHP_EOL);
            parent::loadFixtures([$fixtureRand]);
            sleep($this->interval);

            if (is_numeric($while)) {
                --$while;
            }
        }
    }

    public function stdout($string)
    {
        if ($string === "Applying leads to purging of certain data in the database!\n") {
            $string = "Applying leads to pushing new data into the database!\n";
        }

        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }

        return parent::stdout($string);
    }

    /**
     * Show help message.
     */
    private function printHelpMessage(): void
    {
        $this->stdout($this->getHelpSummary() . "\n");

        $helpCommand = Console::ansiFormat('yii dataGenerator -h', [Console::FG_CYAN]);
        $this->stdout("Use $helpCommand to get usage info.\n");
    }
}
