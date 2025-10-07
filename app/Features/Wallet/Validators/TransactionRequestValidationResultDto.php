<?php

namespace App\Features\Wallet\Validators;

final class TransactionRequestValidationResultDto
{
    public function __construct(
        private readonly array|string|null $errors = null
    )
    {
    }

    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return (array) $this->errors;
    }
}
