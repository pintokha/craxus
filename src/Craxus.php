<?php

namespace Pintokha\Craxus;

use GuzzleHttp\Client;
use ReflectionClass;
use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;

final class Craxus implements AfterLastTestHook,
    AfterSuccessfulTestHook, AfterTestFailureHook, AfterTestErrorHook,
    AfterRiskyTestHook, AfterSkippedTestHook, AfterIncompleteTestHook
{
    const TEST_SUCCESS        = '.';
    const TEST_FAILURE        = 'F';
    const TEST_ERROR          = 'E';
    const TEST_RISKY          = 'R';
    const TEST_SKIPPED        = 'S';
    const TEST_INCOMPLETE     = 'I';

    /**
     * @var boolean configured
     */
    protected $configured = false;

    /**
     * @var string app_id
     */
    protected $app_id;

    /**
     * @var string secret
     */
    protected $secret;

    /**
     * @var array results
     */
    protected $results = [];

    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        $this->setResult(self::TEST_SUCCESS, $test, '', $time);
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this->setResult(self::TEST_FAILURE, $test, $message, $time);
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this->setResult(self::TEST_ERROR, $test, $message, $time);
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        $this->setResult(self::TEST_RISKY, $test, $message, $time);
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        $this->setResult(self::TEST_SKIPPED, $test, $message, $time);
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        $this->setResult(self::TEST_INCOMPLETE, $test, $message, $time);
    }

    public function executeAfterLastTest(): void
    {
        $this->getCredentials();

        if ($this->configured)
            $this->sendRequest();
    }

    /**
     * Get user credentials and check if Craxus enabled to send request
     */
    private function getCredentials(): void
    {
        $enable = (getenv('CRAXUS_ENABLE') === 'true') ? true : false;
        $this->app_id = getenv('CRAXUS_APP_ID');
        $this->secret = getenv('CRAXUS_SECRET');

        if ($enable && $this->app_id && $this->secret) {
            $this->configured = true;
        }
    }

    /**
     * Set result to the array after each finished test
     *
     * @param string $result
     * @param string $test
     * @param string $message
     * @param float $time
     */
    private function setResult(string $result, string  $test, string $message, float $time)
    {
        $this->results[] = [
            'test'      => $test,
            'result'    => $result,
            'message'   => $message,
            'time'      => $time,
            'comment'   => $this->getComment($test),
        ];
    }

    /**
     * Get test comment if exist
     *
     * @param string $test
     * @return string
     */
    private function getComment(string $test): string
    {
        list($class, $method) = explode('::', $test);

        try {
            $reflectionClass = new ReflectionClass($class);
            $comment = $reflectionClass->getMethod($method)->getDocComment();

        } catch (\ReflectionException $exception) {
            $comment = '';
        }

        return $comment;
    }

    /**
     * Send all tests result
     */
    private function sendRequest()
    {
        try {
            $client = new Client();

            $client->post('http://craxus.loc/api/v1/observer', [
                'headers' => [ 'Accept' => 'application/json' ],
                'form_params' => [
                    'app_id' => $this->app_id,
                    'secret' => $this->secret,
                    'result' => json_encode($this->results)
                ]
            ]);

            echo PHP_EOL;
            echo "Craxus: your tests result successfully updated.";
        } catch (\Exception $exception) {
            echo PHP_EOL;
            echo "Craxus: something wrong, please let me know by email: pintokha17@gmail.com";
        }
    }
}
