<?php

namespace Larashed\Agent\Tests\Unit\Errors;

use Larashed\Agent\Errors\CodeSnippet;
use PHPUnit\Framework\TestCase;

class CodeSnippetTest extends TestCase
{
    public function testAbilityToGetFromMiddleOfFile()
    {
        $code = new CodeSnippet($this->getTestFilePath('20-lines.txt'), 10, 5);

        $this->assertEquals([
            8  => 'Line 8',
            9  => 'Line 9',
            10 => 'Line 10',
            11 => 'Line 11',
            12 => 'Line 12'
        ], $code->get());
    }

    public function testAbilityToGetFromStartOfFile()
    {
        $code = new CodeSnippet($this->getTestFilePath('20-lines.txt'), 2, 3);

        $this->assertEquals([
            1 => 'Line 1',
            2 => 'Line 2',
            3 => 'Line 3'
        ], $code->get());
    }

    public function testAbilityToGetFromEndOfFile()
    {
        $code = new CodeSnippet($this->getTestFilePath('20-lines.txt'), 19, 3);

        $this->assertEquals([
            18 => 'Line 18',
            19 => 'Line 19',
            20 => 'Line 20'
        ], $code->get());
    }

    public function testGettingEndOfFileWithBigLineNumber()
    {
        $code = new CodeSnippet($this->getTestFilePath('20-lines.txt'), 50, 3);

        $this->assertEquals([
            18 => 'Line 18',
            19 => 'Line 19',
            20 => 'Line 20'
        ], $code->get());
    }

    public function testGettingWholeFileWithHighLineCount()
    {
        $code = new CodeSnippet($this->getTestFilePath('5-lines.txt'), 1, 30);

        $this->assertEquals([
            1 => 'Line 1',
            2 => 'Line 2',
            3 => 'Line 3',
            4 => 'Line 4',
            5 => 'Line 5'
        ], $code->get());
    }

    public function testHandlingNonExistingFile()
    {
        $code = new CodeSnippet($this->getTestFilePath('wrong-filename.txt'), 1);

        $this->assertEquals([], $code->get());
    }

    private function getTestFilePath($fileName)
    {
        return __DIR__ . "/test-data/{$fileName}";
    }
}
