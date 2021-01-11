<?php

declare(strict_types=1);

namespace horstoeko\slimapp\action;

use horstoeko\slimapp\twig\SlimAppTwig;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use horstoeko\slimapp\validation\SlimAppValidator;
use Symfony\Component\Translation\Translator;

abstract class SlimAppHtmlAction extends SlimAppBaseAction
{
    /**
     * @var SlimAppTwig
     */
    protected $twig;

    /**
     * @param LoggerInterface $logger
     * @param SlimAppTwig     $twig
     */
    public function __construct(LoggerInterface $logger, Capsule $capsule, SlimAppValidator $validator, Translator $translator, SlimAppTwig $twig)
    {
        parent::__construct($logger, $capsule, $validator, $translator);

        $this->twig = $twig;
    }

    /**
     * Resolve twig template names
     *
     * @return string[]
     */
    protected function resolveTemplate(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        return $this->twig->renderExtended(
            $this->response,
            $this->resolveTemplate(),
            $data ?? []
        )->withStatus($statusCode);
    }
}
