<?php

namespace App\Controller;

use App\Entity\Cocktail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CocktailController extends Controller
{
    /**
     * @Route("/", name="cocktail")
     */
    public function index(Request $request, SessionInterface $session)
    {

        if (!isset($cocktails) || !isset($ingredients)) {
            $string = file_get_contents("../public/api/cocktail.json");
            $json = json_decode($string, true);
            $cocktails = [];
            foreach ($json as $cocktail) {
                $cocktails[] = new Cocktail($cocktail);
            }

            $string = file_get_contents("../public/api/ingredient.json");
            $ingredients = json_decode($string, true);

            $session->set('coktails', $cocktails);
            $session->set('ingredients', $ingredients);


            $session->set('round', 0);

        } else {
            $cocktails = $session->get('coktails');
            $ingredients = $session->get('ingredients');

            $round = $session->get('round');
            $round++;
            $session->set('round', $round);
        }

        $chats = [];
        if ($session->get('round') == 0) {
            $chats[] = "Bonjour je suis Drinky.";
            $chats[] = "Je vais vous proposer un coktail.";

            $chats[] = "Co";
        }

        return $this->render('cocktail/index.html.twig', [
            'cocktails' => $cocktails,
            'ingredients' => $ingredients,
        ]);
    }

    public function selectOne(int $id, array $coktails): Cocktail
    {
        foreach ($coktails as $coktail) {
            if ($coktail->getId() == $id) {
                return $coktail;
            }
        }
    }

}
