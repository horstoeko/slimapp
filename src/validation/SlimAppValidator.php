<?php

declare(strict_types=1);

namespace horstoeko\slimapp\validation;

use Respect\Validation\Validator as v;
use horstoeko\slimapp\exception\SlimAppValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Symfony\Component\Translation\Translator;
use SlimSession\Helper as SessionHelper;
use Psr\Http\Message\ServerRequestInterface as Request;

class SlimAppValidator
{
    /**
     * @var string
     */
    const SESSION_NAMESPACE = "slimappvalidator";

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var \SlimSession\Helper
     */
    protected $sessionHelper;

    /**
     * @var string
     */
    protected $translationDomain = "slimbaseapp";

    /**
     * @var boolean
     */
    protected $dontStoreErrorsInSession = false;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param Translator $translator
     * @param SessionHelper $sessionHelper
     * @param array $options
     */
    public function __construct(Translator $translator, SessionHelper $sessionHelper, array $options)
    {
        $this->translator = $translator;
        $this->sessionHelper = $sessionHelper;

        if (is_array($options)) {
            foreach ($options as $optionName => $optionValue) {
                if (!property_exists($this, $optionName)) {
                    continue;
                }
                $this->$optionName = $optionValue;
            }
        }
    }

    /**
     * Clear error list
     *
     * @return SlimAppValidator
     */
    public function clearErrors()
    {
        $this->errors = [];
        $this->storeErrorsInSession();
        return $this;
    }

    /**
     * Validates data presented as an PSR-Request by the given rules
     *
     * @param array $data
     * @param array $rules
     * @return SlimAppValidator
     */
    public function validateRequest(Request &$request, array $rules): SlimAppValidator
    {
        return $this->validateData($request->getParsedBody(), $rules);
    }

    /**
     * Validate data presented as an array
     *
     * @param array $data
     * @param array $rules
     * @return SlimAppValidator
     */
    public function validateData(array $data, array $rules): SlimAppValidator
    {
        $this->clearErrors();

        foreach ($rules as $field => $rule) {
            try {
                $isOptional = $this->isOptionalField($field);
                if ($isOptional == true) {
                    $rule = v::key($field, null, false)->setName(ucfirst($field))->assert($data);
                } else {
                    $rule = v::key($field)->setName(ucfirst($field))->assert($data);
                }
            } catch (NestedValidationException $exception) {
                $exception->setParam('translator', function ($message) {
                    return $this->translate($message);
                });
                $this->errors[$field] = $exception->getMessages();
            }
        }

        if ($this->failed()) {
            $this->storeErrorsInSession();
            return $this;
        }

        foreach ($rules as $field => $rule) {
            try {
                $isOptional = $this->isOptionalField($field);
                if ($rule->getName() == "") {
                    $rule->setName(ucfirst($field));
                }
                if ($isOptional == true) {
                    if (!v::key($field)->validate($data)) {
                        continue;
                    }
                }
                $rule->assert($data[$field]);
            } catch (NestedValidationException $exception) {
                $exception->setParam('translator', function ($message) {
                    return $this->translate($message);
                });
                $this->errors[$field] = $exception->getMessages();
            }
        }

        $this->storeErrorsInSession();

        return $this;
    }

    /**
     * Validates data presented as an PSR-Request by the given rules
     * If validation failes a SlimAppValidationException will thrown
     *
     * @param Request $request
     * @param array $rules
     * @return SlimAppValidator
     * @throws SlimAppValidationException
     */
    public function validateRequestWithException(Request &$request, array $rules): SlimAppValidator
    {
        $this->validateRequest($request, $rules);

        if ($this->failed()) {
            throw new SlimAppValidationException("Validation failed", $this);
        }

        return $this;
    }

    /**
     * Validate data presented as an array
     * If validation failes a SlimAppValidationException will thrown
     *
     * @param array $data
     * @param array $rules
     * @return SlimAppValidator
     */
    public function validateDataWithException(array $data, array $rules): SlimAppValidator
    {
        $this->validateData($data, $rules);

        if ($this->failed()) {
            throw new SlimAppValidationException("Validation failed", $this);
        }

        return $this;
    }

    /**
     * Store Errors in session
     *
     * @return SlimAppValidator
     */
    public function storeErrorsInSession()
    {
        if ($this->dontStoreErrorsInSession !== false) {
            $this->sessionHelper->set(self::SESSION_NAMESPACE, $this->errors);
        }
        return $this;
    }

    /**
     * Perform translation of an error message given by $message
     *
     * @param string $message
     * @return string
     */
    private function translate($message)
    {
        return $this->translator->trans($message, [], $this->translationDomain);
    }

    /**
     * Returns true if validation failed
     *
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }

    /**
     * Return the list of occurred errors
     *
     * @return array
     */
    public function GetErrors()
    {
        return $this->errors;
    }

    /**
     * Get the first occurred error
     *
     * @return string
     */
    public function getFirstError()
    {
        if (count($this->errors) == 0) {
            return "";
        }

        return reset($this->errors)[0];
    }

    /**
     * Check the validation field name for it
     * optionallity, returns the cleaned field name
     *
     * @param string $field
     * @return boolean
     */
    private function isOptionalField(string &$field): bool
    {
        if (strtoupper(substr($field, 0, 2)) == "O:") {
            $field = substr($field, 2);
            return true;
        }

        return false;
    }
}
