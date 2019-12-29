<?php

namespace Larashed\Agent\Errors;

use RuntimeException;

class CodeSnippet
{
    private $fileName;

    private $line;

    private $lineCount;

    public function __construct($fileName = null, $line = 1, $lineCount = 9)
    {
        $this->fileName = $fileName;

        $this->line = $line;

        $this->lineCount = $lineCount;
    }

    public function get()
    {
        if (!file_exists($this->fileName)) {
            return [];
        }

        try {
            $file = new File($this->fileName);

            $bounds = $this->getBounds($file->numberOfLines());

            $code = [];

            $line = $file->getLine($bounds['start']);

            $currentLine = $bounds['start'];

            while ($currentLine <= $bounds['end']) {
                $code[$currentLine] = rtrim(substr($line, 0, 250));

                $line = $file->getNextLine();
                $currentLine++;
            }

            return $code;
        } catch (RuntimeException $exception) {
            return [];
        }
    }

    private function getBounds($totalNumberOfLines)
    {
        $startLine = max($this->line - floor($this->lineCount / 2), 1);

        $endLine = $startLine + ($this->lineCount - 1);

        if ($endLine > $totalNumberOfLines) {
            $endLine = $totalNumberOfLines;
            $startLine = max($endLine - ($this->lineCount - 1), 1);
        }

        return [
            'start' => $startLine,
            'end'   => $endLine
        ];
    }
}
