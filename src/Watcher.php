<?php

namespace Pintokha\Craxus;

use GuzzleHttp\Client;
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
    protected $projectID;
    protected $api_token;

    protected $results = [];
    protected $exceptions = ['Warning'];

    protected $class;
    protected $method;
    protected $testID;
    protected $docComment;

    protected const TEST_SUCCESS        = '.';
    protected const TEST_FAILURE        = 'F';
    protected const TEST_ERROR          = 'E';
    protected const TEST_RISKY          = 'R';
    protected const TEST_SKIPPED        = 'S';
    protected const TEST_INCOMPLETE     = 'I';

    public function executeBeforeTest(string $test): void
    {
        if(!in_array($test, $this->exceptions)) {
            try { $this->setTestData($test); }
            catch (\Exception $exception) { var_dump($exception->getMessage()); }
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
        $this->checkConfig();

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
            var_dump($exception->getMessage());
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

    public function checkConfig()
    {
        if (!getenv('CRAXUS_API_TOKEN')) {
            echo PHP_EOL;
            echo "=================================================================";
            echo PHP_EOL;
            echo "CRAXUS: You have not set a value in .env file: 'CRAXUS_API_TOKEN'";
            echo PHP_EOL;
            echo "=================================================================";
            echo PHP_EOL;
            exit();
        } elseif(!getenv('CRAXUS_PROJECT_ID')) {
            echo PHP_EOL;
            echo "==================================================================";
            echo PHP_EOL;
            echo "CRAXUS: You have not set a value in .env file: 'CRAXUS_PROJECT_ID'";
            echo PHP_EOL;
            echo "==================================================================";
            echo PHP_EOL;
            exit();
        } else {
            $this->api_token = getenv('CRAXUS_API_TOKEN');
            $this->projectID = getenv('CRAXUS_PROJECT_ID');
        }
    }

    public function sendRequest()
    {
        $client = new Client();

        $res = $client->post('https://craxus.io/api/'. $this->projectID .'/send_result', [
            'form_params' => [
                'api_token' => $this->api_token,
                'result' => json_encode($this->results)
            ]
        ]);

        printf("\n %s", $res->getBody());
    }
}
