<?php

namespace Larashed\Agent\Errors;

use RuntimeException;

class CodeSnippet
{
    private $line = 1;

    private $lineCount = 9;

    public function line(int $line): self
    {
        $this->line = $line;
        return $this;
    }

    public function lineCount(int $lineCount): self
    {
        $this->lineCount = $lineCount;
        return $this;
    }

    public function get(string $fileName): array
    {
        if (!file_exists($fileName)) {
            return [];
        }

        try {
            $file = new File($fileName);

            [$startLine, $endLine] = $this->getBounds($file->numberOfLines());

            $code = [];

            $line = $file->getLine($startLine);

            $currentLine = $startLine;

            while ($currentLine <= $endLine) {
                $code[$currentLine] = rtrim(substr($line, 0, 250));

                $line = $file->getNextLine();
                $currentLine++;
            }

            return $code;
        } catch (RuntimeException $exception) {
            return [];
        }
    }

    private function getBounds($totalNumberOfLines): array
    {
        $startLine = max($this->line - floor($this->lineCount / 2), 1);

        $endLine = $startLine + ($this->lineCount - 1);

        if ($endLine > $totalNumberOfLines) {
            $endLine = $totalNumberOfLines;
            $startLine = max($endLine - ($this->lineCount - 1), 1);
        }

        return [$startLine, $endLine];
    }
}
