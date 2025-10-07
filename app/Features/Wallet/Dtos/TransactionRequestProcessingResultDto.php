<?php

namespace App\Features\Wallet\Dtos;

final class TransactionRequestProcessingResultDto
{
    public function __construct(
        private readonly ?string $primaryError = null,
        private readonly ?array $otherErrors = null,
        private readonly mixed $data = null,
    )
    {
    }

    public function isSuccessful(): bool
    {
        return
            empty($this->primaryError)
            && empty($this->otherErrors);
    }

    public static function fromException(\Exception $exception): self
    {
        return new self(
            __('Unknown internal error.'),
            [$exception->getMessage()],
            $exception->getTraceAsString(),
        );
    }

    public function toArray(): array
    {
        return [
            'primaryError' => $this->primaryError,
            'otherErrors' => $this->otherErrors,
            'data' => $this->data,
        ];
    }
}
