<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/paymentCard")
     */
    public function numberAction()
    {
        $card = $this->get('database_connection')->fetchAssoc('SELECT number, holder FROM payment_card');

        return new JsonResponse([
            'number' => $card['number'],
            'holder' => $card['holder'],
        ]);
    }
}
