<?php

namespace macklus\SimpleQueue;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

//use macklus\SimpleQueue\SimpleQueue;

class SimpleQueueController extends Controller
{

    const RELEASE = 'RELEASE';
    const BURY = 'BURY';
    const DELAY = 'DELAY';
    const DELETE = 'DELETE';
    const DELAY_PRIORITY = 0;
    const DELAY_TIME = 3000;

    private $_tubeActions = [];
    private $_willTerminate = false;
    private $_lasttimereconnect;
    private $_queue;
    private $_inProgress;
    private $_test;

    public function beforeAction($action)
    {
        $this->_queue = Yii::$app->queue;
        if ($action->id == "index") {
            $this->registerSignalHandler();
            foreach ($this->getTubes() as $tube) {
                $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $tube))));
                if ($this->hasMethod($methodName)) {
                    $this->_tubeActions[$tube] = $methodName;
                    fwrite(STDOUT, Console::ansiFormat("Listening $tube tube.\n", [Console::FG_GREEN]));
                    $this->_queue->watch($tube);
                } else {
                    fwrite(STDOUT, Console::ansiFormat("Not Listening {tube} tube since there is no action defined. {methodName}", ["tube" => $tube, "methodName" => $methodName] . "\n", [Console::FG_YELLOW]));
                }
            }

            if (count($this->_tubeActions) == 0) {
                fwrite(STDERR, Console::ansiFormat("No tube found to listen!" . "\n", [Console::FG_RED]));
                return $this->end();
            }

            while (!$this->_willTerminate) {
                try {
                    if ($this->_lasttimereconnect == null) {
                        $this->_lasttimereconnect = time();
                        $this->setDBSessionTimeout();
                    }

                    if (time() - $this->_lasttimereconnect > 86400) {
                        $this->getDb()->close();
                        $this->getDb()->open();
                        Yii::info("Reconnecting to the DB");
                        $this->setDBSessionTimeout();
                        $this->_lasttimereconnect = time();
                    }

                    $job = $this->_queue->pop();
                    if (!$job) {
                        sleep(3);
                        continue;
                    }
                    $methodName = $this->getTubeAction($job);

                    if (!$methodName) {
                        fwrite(STDERR, Console::ansiFormat("No method found for job's tube!" . "\n", [Console::FG_RED]));
                        break;
                    }
                    $this->_inProgress = true;
                    $this->executeJob($methodName, $job);
                } catch (Yii\db\Exception $e) {
                    $this->_queue->release([$job]);
                    fwrite(STDERR, Console::ansiFormat($e->getMessage() . "\n", [Console::FG_RED]));
                    fwrite(STDERR, Console::ansiFormat('DB Error job is decaying.' . "\n", [Console::FG_RED]));
                } catch (Yii\base\ErrorException $e) {
                    fwrite(STDERR, Console::ansiFormat($e->getMessage() . "\n", [Console::FG_RED]));
                }
            }
            return $this->end();
        }
    }

    /**
     * Execute job and handle outcome
     *
     * @param $methodName
     * @param $job
     */
    protected function executeJob($methodName, $job)
    {
        switch (call_user_func_array([ $this, $methodName], [ "job" => $job])) {
            case self::RELEASE:
                $this->_queue->release([$job]);
                break;
            case self::DELETE:
                $this->_queue->delete([$job]);
                break;
            case self::DELAY:
                $this->_queue->delay([$job], static::DELAY_PRIORITY, static::DELAY_TIME);
                break;
            case self::BURY:
            default:
                $this->_queue->bury([$job]);
                break;
        }
    }

    public function actionIndex()
    {
        
    }

    public function listenTubes()
    {
        return [];
    }

    public function getTubes()
    {
        return array_unique(array_merge([], $this->listenTubes()));
    }

    public function registerSignalHandler()
    {
        if (!extension_loaded('pcntl')) {
            fwrite(STDOUT, Console::ansiFormat("Warning: Process Control Extension is not loaded. Signal Handling Disabled! If process is interrupted, the reserved jobs will be hung. You may lose the job data." . "\n", [Console::FG_YELLOW]));
            return;
        }
        declare (ticks = 1);
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        fwrite(STDOUT, Console::ansiFormat("Process Control Extension is loaded. Signal Handling Registered!" . "\n", [Console::FG_GREEN]));
        return true;
    }

    public function signalHandler($signal)
    {
        fwrite(STDOUT, Console::ansiFormat("Received signal $signal.\n", [Console::FG_YELLOW]));

        switch ($signal) {
            case SIGTERM:
            case SIGINT:
                fwrite(STDOUT, Console::ansiFormat("Exiting" . "...\n", [Console::FG_RED]));
                if (!$this->_inProgress) {
                    return $this->end();
                }
                $this->terminate();
                break;
            default:
                break;
        }
    }

    public function getTubeAction($statsJob)
    {

        return isset($this->_tubeActions[$statsJob->queue]) ? $this->_tubeActions[$statsJob->queue] : false;
    }

    public function setDBSessionTimeout()
    {
        try {
            $this->mysqlSessionTimeout();
        } catch (\Exception $e) {
            Yii::error("DB wait timeout did not succeeded.");
        }
    }

    public function mysqlSessionTimeout()
    {
        try {
            $command = $this->getDb()->createCommand('SET @@session.wait_timeout = 31536000');
            $command->execute();
        } catch (\Exception $e) {
            Yii::error("Mysql session.wait_timeout command did not succeeded.");
        }
    }

    public function terminate()
    {
        $this->_willTerminate = true;
    }

    public function setTestMode()
    {
        return $this->_test = true;
    }

    public function end()
    {
        return ($this->_test) ? false : Yii::$app->end();
    }
}
