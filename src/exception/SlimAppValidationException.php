<?php

declare(strict_types=1);

namespace horstoeko\slimapp\exception;

use Throwable;
use DomainException;
use horstoeko\slimapp\validation\SlimAppValidator;

class SlimAppValidationException extends DomainException
{
    /**
     * @var ValidationResult|null
     */
    private $validator;

    /**
     * Undocumented function
     *
     * @param string $message
     * The Exception message to throw
     * @param SlimAppValidator|null $validator
     * The validator object
     * @param integer $code
     * The Exception code
     * @param Throwable $previous
     * The previous throwable used for the exception chaining
     */
    public function __construct(
        string $message,
        SlimAppValidator $validator = null,
        int $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->validator = $validator;
    }

    /**
     * Get the validation result.
     *
     * @return SlimAppValidator|null The validation result
     */
    public function getValidator(): ?SlimAppValidator
    {
        return $this->validator;
    }

    /**
     * Get errors from the validator object
     *
     * @return array
     */
    public function getValidatorErrors(): array
    {
        if ($this->getValidator() != null) {
            return $this->getValidator()->GetErrors();
        }

        return [];
    }
}
