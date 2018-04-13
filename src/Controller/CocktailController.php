<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CocktailController extends Controller
{
    /**
     * @Route("/cocktail", name="cocktail")
     */
    public function index(Request $request)
    {
        dump($request);die();
        return $this->render('cocktail/index.html.twig');
    }
}
