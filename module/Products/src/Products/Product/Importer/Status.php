<?php
namespace Products\Product\Importer;

class Status
{
    protected $missingHeaders = [];
    protected $linesProcessed = 0;
    protected $linesFailed = [];

    /**
     * @return self
     */
    public function headerMissing($header)
    {
        $this->missingHeaders[] = $header;
        return $this;
    }

    /**
     * @return self
     */
    public function lineProcessed()
    {
        $this->linesProcessed++;
        return $this;
    }

    /**
     * @return self
     */
    public function lineFailed(int $line, array $errors)
    {
        $this->linesFailed[] = compact('line', 'errors');
        return $this;
    }

    public function toArray(): array
    {
        return [
            'missingHeaders' => $this->missingHeaders,
            'lines' => [
                'processed' => $this->linesProcessed,
                'failed' => $this->linesFailed,
            ],
        ];
    }

    public function __toString(): string
    {
        $msg = [];
        if (!empty($this->missingHeaders)) {
            $msg[] = sprintf('The following headers are missing: %s', implode(', ', $this->missingHeaders));
        }
        $msg[] = sprintf('Successfully processed %d lines', $this->linesProcessed);
        if (!empty($this->linesFailed)) {
            foreach ($this->linesFailed as $failedLine) {
                foreach ($failedLine['errors'] as $header => $error) {
                    $msg[] = sprintf('Line: %d, Column: %s, Error: %s', $failedLine['line'], $header, $error);
                }
            }
        }
        return implode(PHP_EOL, $msg);
    }
}