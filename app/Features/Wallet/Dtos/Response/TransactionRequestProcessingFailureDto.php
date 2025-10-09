<?php

namespace App\Features\Wallet\Dtos\Response;

class TransactionRequestProcessingFailureDto extends TransactionRequestProcessingResultDto
{
    public function __construct(
        private readonly string $primaryError,
        private readonly ?array $otherErrors = null,
        private readonly mixed $data = null,
    )
    {
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    public static function fromException(\Exception $exception): self
    {
        $otherErrors = [$exception->getMessage()];
        if ($exception->getPrevious() !== null) {
            $otherErrors[] = $exception->getPrevious()->getMessage();
        }

        return new self(
            __('Unknown internal error.'),
            $otherErrors,
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
