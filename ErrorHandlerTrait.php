<?php

namespace demi\yii2outlog;

use Yii;

trait ErrorHandlerTrait
{
    /**
     * Tracks if we are in the exception handler and have already notified Bugsnag about
     * the exception
     *
     * @var boolean
     */
    protected $inExceptionHandler = false;

    /**
     * Only log the exception here if we haven't handled it below (in handleException)
     */
    public function logException($exception)
    {
        if (!$this->inExceptionHandler) {
            Yii::$app->bugsnag->notifyException($exception);
        }
        try {
            Yii::error("Caught exception " . get_class($exception) . ": " . (string)$exception,
                BugsnagComponent::IGNORED_LOG_CATEGORY);
        } catch (\Exception $e) {
        }
    }

    /**
     * Ensures CB logs are written to the DB if an exception occurs
     */
    public function handleException($exception)
    {
        Yii::$app->bugsnag->notifyException($exception);
        $this->inExceptionHandler = true;
        parent::handleException($exception);
    }

    /**
     * Handles fatal PHP errors
     */
    public function handleFatalError()
    {
        // When running under codeception, a Yii application won't actually exist, so we just have to eat it here...
        if (is_object(Yii::$app)) {
            // Call into Bugsnag client's errorhandler since this will potentially kill the script below
            Yii::$app->bugsnag->runShutdownHandler();
        }
        parent::handleFatalError();
    }
}