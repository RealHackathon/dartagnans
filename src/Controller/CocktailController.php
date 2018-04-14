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

            $this->selectOneIngredient($ingredients); die();

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

        $chatMessages = [];
        if ($session->get('round') == 0) {
            //lancement de la conversation
            $chatMessages[] = "Bonjour je suis Drinky.";
            $chatMessages[] = "Je vais vous proposer un coktail.";

            $array = $this->selectOneIngredient($ingredients);
            $ingredientSelected = $array[0];
            $ingredients = $array[1];
            $session->set('ingredients', $ingredients);
            $chatMessages[] = "L'ingrédient suivant vous convient-il ?";
            $chatMessages[] = $ingredientSelected;
        } else {

        }

        return $this->render('cocktail/index.html.twig', [
            'cocktails' => $cocktails,
            'ingredients' => $ingredients,
            'chatMessage' => $chatMessages,
        ]);
    }

    public function selectOneCocktail(int $id, array $coktails): Cocktail
    {
        foreach ($coktails as $coktail) {
            if ($coktail->getId() == $id) {
                return $coktail;
            }
        }
    }

    /**
     * Renvoie un tableau avec un ingrédient au hasard dépilé du tableau fourni en paramètre
     * @param array $ingredients
     * @return array
     */
    public function selectOneIngredient(array $ingredients): array
    {
        $index = array_rand($ingredients, 1);
        $ingredient = $ingredients[$index];
        array_splice($ingredients, $index,1);
        return [$ingredient, $ingredients];
    }

}
