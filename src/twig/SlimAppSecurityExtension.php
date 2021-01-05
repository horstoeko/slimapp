<?php

declare(strict_types=1);

namespace horstoeko\slimapp\twig;

use Twig\TwigFunction;
use horstoeko\slimapp\twig\SlimAppTwigExtension;
use horstoeko\slimapp\security\SlimAppLoginManager;

/**
 * SlimApp Security Extensions for twig
 */
class SlimAppSecurityExtension extends SlimAppTwigExtension
{
    /**
     * LoginManager reference
     *
     * @var \horstoeko\slimapp\security\SlimAppLoginManager
     */
    protected $loginManager;

    /**
     * Constructor
     *
     * @param SlimAppLoginManager $loginManager
     */
    public function __construct(SlimAppLoginManager $loginManager)
    {
        $this->loginManager = $loginManager;
    }

    /**
     * Get common name for this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'slimappsecurity';
    }

    /**
     * Get available functions in this extension
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('IsSignedIn', array($this, 'isSignedIn')),
            new TwigFunction('IsAdminSignedIn', array($this, 'isAdminSignedIn')),
            new TwigFunction('SignedInUser', array($this, 'signedInUser')),
            new TwigFunction('SignedInUserFirstname', array($this, 'signedInUserFirstname')),
            new TwigFunction('SignedInUserLastname', array($this, 'signedInUserLastname')),
            new TwigFunction('SignedInUserId', array($this, 'signedInUserId')),
        ];
    }

    /**
     * Get if anyone is signed in
     *
     * @return bool
     */
    public function isSignedIn()
    {
        return $this->loginManager->isSignedIn();
    }

    /**
     * Get if anyone with admin permissions is signed in
     *
     * @return bool
     */
    public function isAdminSignedIn()
    {
        return $this->loginManager->isAdminSignedIn();
    }

    /**
     * Get the signed-in user information
     *
     * @return string
     */
    public function signedInUser()
    {
        return $this->loginManager->signedInUserName();
    }

    /**
     * Get the first name of the signed in user
     *
     * @return string
     */
    public function signedInUserFirstname()
    {
        return $this->loginManager->signedInUserFirstname();
    }

    /**
     * Get the last name of the signed in user
     *
     * @return string
     */
    public function signedInUserLastname()
    {
        return $this->loginManager->signedInUserLastname();
    }

    /**
     * Get the id of the signed in user
     *
     * @return string
     */
    public function signedInUserId()
    {
        return $this->loginManager->signedInUserId();
    }
}
