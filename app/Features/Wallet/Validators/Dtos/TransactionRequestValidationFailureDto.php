<?php

namespace App\Features\Wallet\Validators\Dtos;

final class TransactionRequestValidationFailureDto extends TransactionRequestValidationResultDto
{
    public function __construct(
        private readonly array|string $errors
    )
    {
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return (array) $this->errors;
    }
}
