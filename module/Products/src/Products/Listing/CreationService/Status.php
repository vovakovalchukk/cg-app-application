<?php
namespace Products\Listing\CreationService;

class Status
{
    protected $valid = false;
    protected $warnings = [];
    protected $errors = [];

    public function success()
    {
        $this->valid = true;
    }

    public function warning(string $warning)
    {
        $this->warnings[] = $warning;
    }

    public function error(string $error)
    {
        $this->valid = false;
        $this->errors[] = $error;
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];
    }
}