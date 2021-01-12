<?php

declare(strict_types=1);

namespace horstoeko\slimapp\action;

use Psr\Log\LoggerInterface;
use horstoeko\slimapp\twig\SlimAppTwig;
use Symfony\Component\Translation\Translator;
use horstoeko\slimapp\validation\SlimAppValidator;
use horstoeko\slimapp\security\SlimAppLoginManager;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;

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
    public function __construct(LoggerInterface $logger, Capsule $capsule, SlimAppValidator $validator, Translator $translator, SlimAppLoginManager $loginManager, SlimAppTwig $twig)
    {
        parent::__construct($logger, $capsule, $validator, $translator, $loginManager);

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
