<?php

namespace Larashed\Agent\Tests\Unit\Errors;

use Exception;
use PHPUnit\Framework\TestCase;
use Larashed\Agent\Errors\ExceptionTransformer;

class ExceptionTransformerTest extends TestCase
{
    public function testExceptionTransformsToArray()
    {
        $message = 'Exception message';
        $previousMessage = 'Previous exception message';

        $previousException = new Exception($previousMessage);
        $exception = new Exception($message, 0, $previousException);

        $transformer = new ExceptionTransformer($exception);
        $result = $transformer->toArray();

        $neededKeys = ['class', 'message', 'code', 'line', 'trace'];
        foreach ($neededKeys as $key) {
            $this->assertArrayHasKey($key, $result[0]);
            $this->assertArrayHasKey($key, $result[1]);
        }

        $this->assertEquals($result[0]['message'], $message);
        $this->assertEquals($result[1]['message'], $previousMessage);
    }

    public function testExceptionTraceSkipsLines()
    {
        $exception = new Exception('Exception message');

        $transformer = new ExceptionTransformer($exception);
        $transformer->setIgnoredNamespaces(['PHPUnit\Framework\TestCase']);
        $result = $transformer->toArray();

        $expected = [
            [
                'class' => 'Larashed\\Agent\\Tests\\Unit\\Errors\\ExceptionTransformerTest',
            ],
            [
                'class' => 'ReflectionMethod',
            ],
            [
                'class' => 'PHPUnit\\Framework\\TestCase',
            ],
        ];

        $this->assertCount(3, $result[0]['trace']);
        $this->assertArraySubset($expected, $result[0]['trace']);
    }
}
