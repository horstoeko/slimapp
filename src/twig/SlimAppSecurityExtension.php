<?php

declare(strict_types=1);

namespace horstoeko\slimapp\twig;

use Twig\TwigFunction;
use horstoeko\slimapp\twig\SlimAppTwigExtension;
use horstoeko\slimapp\security\SlimAppLoginManager;

/**
 * XSlim Security Extensions for twig
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
        return 'xslimsecurity';
    }

    /**
     * Get available functions in this extension
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('IsSignedIn', array($this, 'IsSignedIn')),
            new TwigFunction('IsAdminSignedIn', array($this, 'IsAdminSignedIn')),
            new TwigFunction('SignedInUser', array($this, 'SignedInUser')),
            new TwigFunction('SignedInUserFirstname', array($this, 'SignedInUserFirstname')),
            new TwigFunction('SignedInUserLastname', array($this, 'SignedInUserLastname')),
            new TwigFunction('SignedInUserId', array($this, 'SignedInUserId')),
        ];
    }

    /**
     * Get if anyone is signed in
     *
     * @return bool
     */
    public function IsSignedIn()
    {
        return $this->loginManager->isSignedIn();
    }

    /**
     * Get if anyone with admin permissions is signed in
     *
     * @return bool
     */
    public function IsAdminSignedIn()
    {
        return $this->loginManager->isAdminSignedIn();
    }

    /**
     * Get the signed-in user information
     *
     * @return string
     */
    public function SignedInUser()
    {
        return $this->loginManager->signedInUserName();
    }

    /**
     * Get the first name of the signed in user
     *
     * @return string
     */
    public function SignedInUserFirstname()
    {
        return $this->loginManager->SignedInUserFirstname();
    }

    /**
     * Get the last name of the signed in user
     *
     * @return string
     */
    public function SignedInUserLastname()
    {
        return $this->loginManager->SignedInUserLastname();
    }

    /**
     * Get the id of the signed in user
     *
     * @return string
     */
    public function SignedInUserId()
    {
        return $this->loginManager->SignedInUserId();
    }
}
