<?php

namespace App\Controller;

use App\Entity\Cocktail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CocktailController extends Controller
{
    /**
     * @Route("/", name="cocktail")
     */
    public function index(Request $request)
    {
        $string = file_get_contents("../public/api/cocktail.json");
        $json = json_decode($string, true);
        $cocktails = [];

        foreach ($json as $cocktail) {
            $cocktails[] = new Cocktail($cocktail);
        }

        return $this->render('cocktail/index.html.twig', ['cocktails' => $cocktails]);
    }
}
