<?php

namespace demi\yii2outlog;

use Yii;
use yii\base\Component;
use demi\outlog\Outlog;

/**
 * Outlog Component
 *
 * @property-read Outlog $client
 */
class OutlogComponent extends Component
{
    const IGNORED_LOG_CATEGORY = 'outlog';

    /**
     * Outlog project token
     *
     * @var string
     */
    public $apiKey;
    /**
     * True if we are in OutLogTarget::export(), then don't trigger a flush, causing an infinite loop
     *
     * @var boolean
     */
    public $exportingLog = false;

    /**
     * Logger class instance
     *
     * @var Outlog
     */
    protected $_client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $outlog = $this->_client = new Outlog($this->apiKey);
        $outlog->basePath = dirname(Yii::$app->basePath);

        parent::init();
    }

    /**
     * Client instance getter
     *
     * @return \demi\outlog\Outlog
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Submit new Exception
     *
     * @param \Exception $exception
     * @param string $type
     */
    public function notifyException(\Exception $exception, $type = Outlog::TYPE_INFO)
    {
        $this->client->notifyException($exception, $type);
    }


    public function notifyError($category, $message, $trace = null)
    {
        $this->client->notifyError($category, $message, ['trace' => $trace], 'error');
    }

    public function notifyWarning($category, $message, $trace = null)
    {
        $this->client->notifyError($category, $message, ['trace' => $trace], 'warning');
    }

    public function notifyInfo($category, $message, $trace = null)
    {
        $this->client->notifyError($category, $message, ['trace' => $trace], 'info');
    }

    public function runShutdownHandler()
    {
        if (!$this->exportingLog) {
            Yii::getLogger()->flush(true);
        }

        $this->client->shutdownHandler();
    }
}