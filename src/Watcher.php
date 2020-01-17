<?php

namespace Pintokha\Craxus;

use GuzzleHttp\Client;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Runner\BeforeTestHook;
use ReflectionClass;
use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;

final class Watcher implements BeforeTestHook, AfterLastTestHook,
    AfterSuccessfulTestHook, AfterTestFailureHook, AfterTestErrorHook,
    AfterRiskyTestHook, AfterSkippedTestHook, AfterIncompleteTestHook
{
    // user config
    protected $enable;
    protected $api_token;
    protected $project_id;
    protected $configured = false;

    protected $results = [];
    protected $exceptions = ['Warning'];

    protected $class;
    protected $method;
    protected $testID;
    protected $docComment;

    const TEST_SUCCESS        = '.';
    const TEST_FAILURE        = 'F';
    const TEST_ERROR          = 'E';
    const TEST_RISKY          = 'R';
    const TEST_SKIPPED        = 'S';
    const TEST_INCOMPLETE     = 'I';

    public function __construct(bool $enable = false, string $api_token = null, string $project_id = null)
    {
        if (($enable && !$api_token) || ($enable && !$project_id)) {
            echo "Craxus: no arguments were given.";
            echo PHP_EOL;
        } else if ($enable && $api_token && $project_id) {
            // set value
            $this->enable = $enable;
            $this->api_token = $api_token;
            $this->project_id = $project_id;

            $this->configured = true;
        }
    }

    public function executeBeforeTest(string $test): void
    {
        if(!in_array($test, $this->exceptions)) {
            try { $this->setTestData($test); }
            catch (\Exception $exception) {
                // skip
            }
        }
    }

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
        if ($this->configured)
            $this->sendRequest();
    }

    protected function setResult(string $result, string  $test, string $message, float $time)
    {
        $this->results[] = [
            'number'    => $this->testID,
            'testName'  => $test,
            'docBlock'  => $this->docComment,
            'result'    => $result,
            'message'   => $message,
            'time'      => $time
        ];
    }

    public function setTestData(string $test)
    {
        $this->parseTest($test);
        $this->getDocComment();
        $this->getTestId();
    }

    public function getDocComment()
    {
        try {
            $rc = new ReflectionClass($this->class);
            $this->docComment = $rc->getMethod($this->method)->getDocComment();
        } catch (\Exception $exception) {
            $this->docComment = '';
        }
    }

    public function getTestId()
    {
        preg_match_all('#@(.*?)\n#s', $this->docComment, $annotations);

        foreach ($annotations[1] as $annotation)
            if (strpos($annotation, 'craxus') !== false) {
                list(, $this->testID) = explode(' ', $annotation);
            }
    }

    public function parseTest($test)
    {
        list($this->class, $this->method) = explode('::', $test);
    }

    public function sendRequest()
    {
        $client = new Client();

        $res = $client->post('https://craxus.io/api/projects/'. $this->project_id .'/new_result', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'api_token' => $this->api_token,
                'result' => json_encode($this->results)
            ]
        ]);

        printf("\n %s", $res->getBody());
    }
}
