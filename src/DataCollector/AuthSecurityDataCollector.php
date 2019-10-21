<?php

namespace App\DataCollector;

use App\Entity\Security\Auth;
use Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSecurityDataCollector extends SecurityDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        parent::collect($request, $response, $exception);

        $token = $this->data['token'];
        if (null !== $token && $token->getUser() instanceof Auth) {
            $this->data['user'] = $token->getUser()->getPerson()->getCanonical();
        }
    }
}
