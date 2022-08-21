<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use App\Repository\ClientUserRepository;
use App\Service\ArticlesProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    
    private $userRepository;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $articlesProvider;
    private $em;
    
    public function __construct(
        ClientUserRepository $userRepository, 
        UrlGeneratorInterface $urlGenerator, 
        CsrfTokenManagerInterface $csrfTokenManager, 
        UserPasswordEncoderInterface $passwordEncoder,
        ArticlesProvider $articlesProvider,
        EntityManagerInterface $em
    ) {
       $this->userRepository = $userRepository;
       $this->urlGenerator = $urlGenerator;
       $this->csrfTokenManager = $csrfTokenManager;
       $this->passwordEncoder = $passwordEncoder;
       $this->articlesProvider = $articlesProvider;
       $this->em = $em;
    }
    
    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(SELF::LOGIN_ROUTE);
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === SELF::LOGIN_ROUTE && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        $request->getSession()->set(
            'remember_me',
            $request->request->get('_remember_me')
        );
        
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $csrfToken = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (! $this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new InvalidCsrfTokenException();
        }
        $user = $this->userRepository->findOneBy(['email' => $credentials['email']]);
                
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if($this->passwordEncoder->isPasswordValid($user, $credentials['password']) == $user->getPassword()) {
            $token = $this->articlesProvider->getToken($credentials['email'], $credentials['password']);
            
            if($token) {
                $user->setToken($token);

                $this->em->persist($user);
                $this->em->flush();

                return true;
            }
        }
        
        return false;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        $path = $this->getTargetPath($request->getSession(), $providerKey);
        return new RedirectResponse($path ?: $this->urlGenerator->generate('app_articles'));
    }
}
