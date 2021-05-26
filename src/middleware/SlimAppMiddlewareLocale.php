<?php

declare(strict_types=1);

namespace horstoeko\slimapp\middleware;

use horstoeko\slimapp\system\SlimAppDirectories;
use horstoeko\stringmanagement\FileUtils;
use horstoeko\stringmanagement\PathUtils;
use \Psr\Container\ContainerInterface;
use \PSr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use \Symfony\Component\Translation\Translator;

class SlimAppMiddlewareLocale extends SlimAppMiddlewareBase
{
    /**
     * @var array
     */
    protected $availablelanguagecodes = [];

    /**
     * @var string
     */
    protected $defaultlanguagecode = "";

    /**
     * @var string
     */
    protected $overridelanguagecode = "";

    /**
     * @var boolean
     */
    protected $strictmatchlanguagecode = false;

    /**
     * @var boolean
     */
    protected $dosetlocale = true;

    /**
     * @var array
     */
    protected $languagelocalemaps = [];

    /**
     * @var array
     */
    protected $unknownlanguagecodemaps = [];

    /**
     * @var \stdClass
     */
    protected $languageDefinition;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    private $translator;

    /**
     * @var \horstoeko\slimapp\system\SlimAppDirectories
     */
    private $directories;

    /**
     * Constructor
     *
     * @param Translator $translator
     * @param SlimAppDirectories $directories
     * @param array $options
     */
    public function __construct(Translator $translator, SlimAppDirectories $directories, array $options)
    {
        $this->translator = $translator;
        $this->directories = $directories;

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
     * Handle middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        return $this->process($request, $handler);
    }

    /**
     * Handle middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $this->request = $request;

        $this->findLanguage();

        // Handle Request and Response

        $request = $request
            ->withAttribute('localeall', $this->languageDefinition->language_complete)
            ->withAttribute('languageall', $this->languageDefinition->language_complete)
            ->withAttribute('language', $this->languageDefinition->language)
            ->withAttribute('country', $this->languageDefinition->country)
            ->withAttribute('contentlanguage', $this->languageDefinition->ContentLanguage)
            ->withAttribute('contentlanguage2', $this->languageDefinition->ContentLanguage2);

        // Handle translator

        $this->translator->setLocale($this->languageDefinition->language_complete);

        $translationfileName = FileUtils::combineFilenameWithFileextension($this->languageDefinition->language_complete, "php");

        $translationfiles = [
            [PathUtils::combinePathWithFile($this->directories->getvendorsettingsdirectory(), $translationfileName), "slimbaseapp"],
            [PathUtils::combinePathWithFile($this->directories->getcustomsettingsdirectory(), $translationfileName), "slimapp"],
        ];

        foreach ($translationfiles as $translationfile) {
            if (!file_exists($translationfile[0])) {
                continue;
            }

            $this->translator->addResource(
                'php',
                $translationfile[0],
                $this->languageDefinition->language_complete,
                $translationfile[1]
            );
        }

        // Handle System Locale

        if (isset($this->languageDefinition->locale_1)) {
            if (!setlocale(LC_ALL, $this->languageDefinition->locale_1)) {
                if (isset($this->languageDefinition->locale_2)) {
                    @setlocale(LC_ALL, $this->languageDefinition->locale_2);
                }
            }
        }

        // Finished

        $response = $handler->handle($request)
            ->withHeader("Content-Language", $this->languageDefinition->ContentLanguage2);

        return $response;
    }

    /**
     * Try to find out the correct language
     * 1. Look for override language
     * 2. Look at the "Accept-Language" header
     * 3. Look for the default language
     *
     * @param Request $request
     *
     * @return string
     */
    private function findLanguage()
    {
        $language = false;

        if ($this->overridelanguagecode) {
            $language = $this->matchoverridelanguage($this->overridelanguagecode);
        }

        if (!$language) {
            $language = $this->matchheader($this->request->getHeader("Accept-language"));
        }

        if (!$language) {
            $language = $this->defaultlanguagecode;
        }

        if (!$language) {
            throw new \Exception("Cannot find an applicable language. Giving up.");
        }

        $this->languageDefinition = new \stdClass();
        $this->languageDefinition->language_complete =
            $language;
        $this->languageDefinition->language =
            $this->getLanguageFromCode($language);
        $this->languageDefinition->country =
            $this->getCountryFromCode($language);
        $this->languageDefinition->locale_1 =
            $this->dosetlocale ? $this->mapLanguageCodeToLocale($language) : null;
        $this->languageDefinition->locale_2 =
            $this->dosetlocale ? $this->mapLanguageCodeToLocale($this->defaultlanguagecode) : null;
        $this->languageDefinition->ContentLanguage =
            $this->languageCodeToContentLanguageCode($language);
        $this->languageDefinition->ContentLanguage2 =
            $this->languageCodeToContentLanguageCode2($language);

        return $this->languageDefinition;
    }

    /**
     * Matches the preferred locales list to your application locales
     * Accepts an array formated as locale => weight
     * e.g. ["en_GB" => 1.0, "en" => 0.8]
     *
     * @param array $list
     *
     * @return mixed (string | boolean)
     */
    private function match($list)
    {
        arsort($list);

        foreach ($list as $l => $w) {
            $currentlanguagecode = $this->normalizeCode($l);

            foreach ($this->availablelanguagecodes as $availablelanguagecode) {
                $availablelanguagecode = $this->normalizeCode($availablelanguagecode);

                if ($this->strictmatchlanguagecode) {
                    if ($availablelanguagecode == $currentlanguagecode) {
                        return $availablelanguagecode;
                    }
                } else {
                    if (strstr($availablelanguagecode, $currentlanguagecode)) {
                        return $availablelanguagecode;
                    }
                }
            }
        }

        return false;
    }

    /**
     * matches any of the the locale codes in the request headers
     * returns best fit locale code if match found
     * or false if no match found
     *
     * @param string $request
     *
     * @return mixed
     */
    private function matchheader($headers)
    {
        $headers = explode(",", implode(",", $headers));
        $prefs = [];

        foreach ($headers as $str) {
            list($l, $w) = array_merge(explode(";q=", $str), ["1.0"]);
            $code = $this->sanitiseCode($l);

            if ($this->isValidCode($code)) {
                $prefs[$code] = (float) $w;
            }
        };

        return $this->match($prefs);
    }

    /**
     * matches the locale code given in the overridelocale variable
     * returns the matched $app_locale code if found
     * or false if no match found
     *
     * @param string $overridelocale
     *
     * @return mixed
     */
    private function matchoverridelanguage($str = null)
    {
        $code = $this->sanitiseCode($str);

        if ($this->isValidCode($code)) {
            return $this->match([
                $code => 1,
            ]);
        }

        return false;
    }

    /**
     * Sanitises a locale code string accepting a-z dash - and underscore _
     *
     * @param string $str
     *
     * @return void
     */
    private function sanitiseCode($str)
    {
        return preg_replace("/[^a-zA-Z\_\-]/", "", $str);
    }

    /**
     * normalizes a locale code for str comparison,
     * e.g. from en-gb to en_GB or en to en_EN -> en_EN is mapped to en_GB
     *
     * @param string $str
     *
     * @return void
     *
     */
    private function normalizeCode($str)
    {
        $sp = explode("_", strtolower(str_replace('-', '_', $str)));
        $nd = strtolower($sp[0]) . '_' . (isset($sp[1]) ? strtoupper($sp[1]) : strtoupper($sp[0]));
        $nd = isset($this->unknownlanguagecodemaps[$nd]) ? $this->unknownlanguagecodemaps[$nd] : $nd;
        return $nd;
    }

    /**
     * get the language from a code in the form en_GB
     * e.g. en_GB returns en
     *
     * @param string $str
     *
     * @return void
     *
     */
    private function getLanguageFromCode($str)
    {
        $sp = explode("_", $this->normalizeCode($str));
        return $sp[0];
    }

    /**
     * get the country from a code in the form en_GB
     * e.g. en_GB returns GB
     *
     * @param string $str
     *
     * @return void
     *
     */
    private function getCountryFromCode($str)
    {
        $sp = explode("_", $this->normalizeCode($str));
        return $sp[1];
    }

    /**
     * undocumented function
     *
     * @param string $str
     *
     * @return boolean
     *
     * @author Ian Grindley
     */
    private function isValidCode($str)
    {
        if (1 === preg_match("/(^[a-z]{2}$|^[a-z]{2}(_|-)[a-z|A-Z]{2}$)/", $str)) {
            return true;
        }

        return false;
    }

    /**
     * Map language code to system locale
     *
     * @param string $languageCode
     *
     * @return string
     */
    private function mapLanguageCodeToLocale($languageCode)
    {
        return isset($this->languagelocalemaps[$languageCode]) ?
            $this->languagelocalemaps[$languageCode] :
            $languageCode;
    }

    /**
     * Convert language code to the needed format of the content-language header
     * eg. de_DE would be converted to de-DE
     *
     * @param <type> $languageCode
     *
     * @return <type>
     */
    protected function languageCodeToContentLanguageCode($languageCode)
    {
        return $this->getLanguageFromCode($languageCode); // . "-" . $this->getCountryFromCode($languageCode);
    }

    /**
     * Returns the locale code (for use with LocaleManagement)
     *
     * @param <type> $languageCode
     *
     * @return <type>
     */
    protected function languageCodeToContentLanguageCode2($languageCode)
    {
        return $this->getLanguageFromCode($languageCode) . "-" . $this->getCountryFromCode($languageCode);
    }
}
