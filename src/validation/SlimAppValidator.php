<?php

declare(strict_types=1);

namespace horstoeko\slimapp\validation;

use Respect\Validation\Validator;
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
        return $this->ValidateData($request->getParsedBody(), $rules);
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @param array $rules
     * @return SlimAppValidator
     */
    public function ValidateData(array $data, array $rules): SlimAppValidator
    {
        $this->clearErrors();

        foreach ($rules as $field => $rule) {
            try {
                if ($rule->getName() == "") {
                    $rule->setName(ucfirst($field));
                }
                $rule->assert($data[$field]);
            } catch (NestedValidationException $exception) {
                /*
                // TODO: Implement translator in Validator
                $exception->setParam('translator', function ($message) {
                    return $this->translate($message);
                });
                */
                $this->errors[$field] = $exception->getMessages();
            }
        }

        $this->storeErrorsInSession();

        return $this;
    }

    /**
     * Store Errors in session
     *
     * @return SlimAppValidator
     */
    public function StoreErrorsInSession()
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
        if (!isset($this->container)) {
            return $message;
        }
        return $this->translator->trans($message, [], $this->translationDomain);
    }

    /**
     * Returns true if validation failed
     *
     * @return bool
     */
    public function Failed()
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
}
