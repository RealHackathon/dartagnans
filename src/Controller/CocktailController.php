<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Form\BotType;
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
        $session->start();

        //on teste si on est au premier tour
        if ($session->get('cocktails') == null || $session->get('ingredients') == null) {
            //si oui on initialise nos données
            $string = file_get_contents("../public/api/cocktail.json");
            $json = json_decode($string, true);
            $cocktails = [];
            foreach ($json as $cocktail) {
                $cocktails[] = new Cocktail($cocktail);
            }

            $string = file_get_contents("../public/api/ingredient.json");
            $ingredients = json_decode($string, true);

            $this->selectOneIngredient($ingredients);

            $session->set('cocktails', $cocktails);
            $session->set('ingredients', $ingredients);


            $session->set('round', 0);

        } else {
            //sinon on récupère nos données
            $cocktails = $session->get('cocktails');
            $ingredients = $session->get('ingredients');

            $round = $session->get('round');
            $round++;
            $session->set('round', $round);
        }

        $chatMessages = [];
        $userMessages = [];
        if ($session->get('round') == 0) {
            $session->set('round', 1);
            //lancement de la conversation
            $chatMessages[] = "Bonjour je suis Drinky.";
            $chatMessages[] = "Je vais vous proposer un cocktail.";

            $array = $this->selectOneIngredient($ingredients);
            $ingredientSelected = $array[0];
            $session->set('ingredientSelected', $ingredientSelected);
            $ingredients = $array[1];
            $session->set('ingredients', $ingredients);
            $chatMessages[] = "L'ingrédient suivant vous convient-il ?";
            $chatMessages[] = $ingredientSelected;

        } else {
            //récupérer le post si oui on ne garde que les cocktails avec l'ingrédient sinon on relance un autre tour
            if ($request->request->get('bot') !== null) {
                if (isset($request->request->get('bot')['yes'])) {
                    $userMessages[] = "Oui";
                    $cocktails = $session->get('cocktails');
                    $cocktails = $this->filterCocktailsByIngredient($session->get('ingredientSelected'), $cocktails);
                    $session->set('cocktails', $cocktails);
                    //on teste si il reste plus de 2 cocktails
                    if (count($session->get('cocktails')) < 2) {
                        if (count($session->get('cocktails')) < 1) {
                            $chatMessages[] = "Nous n'avons pas de cocktail à votre gout.";
                        } else {
                            $chatMessages[] = "Il reste un seul cocktail.";
                            $lastCocktail = array_shift($cocktails);
                            $chatMessages[] = 'Je vous propose le cocktail :';
                            $chatMessages[] = $lastCocktail->getName();
                        }

                    } else {
                        $chatMessages[] = "Je vais vous faire une autre proposition.";
                        //selection d'un nouvel ingrédient présent dans les cocktails restants
                        $results = $this->selectOneIngredient($session->get('ingredients'));
                        $ingredientSelected = $results[0];
                        $session->set('ingredientSelected', $ingredientSelected);
                        $session->set('ingredients', $results[1]);
                        dump($session);
                        while (!$this->testIngredientInCocktails($ingredientSelected, $session->get('cocktails'))) {
                            $results = $this->selectOneIngredient($session->get('ingredients'));
                            $ingredientSelected = $results[0];
                            $session->set('ingredientSelected', $ingredientSelected);
                            $session->set('ingredients', $results[1]);
                        }
                        dump($session);
                        $session->set('ingredientSelected', $ingredientSelected);

                        $chatMessages[] = "Je vous propose :";
                        $chatMessages[] = $ingredientSelected;

                    }
                } else {
                    $userMessages[] = "Non";

                    $array = $this->selectOneIngredient($ingredients);
                    $ingredientSelected = $array[0];
                    $session->set('ingredientSelected', $ingredientSelected);
                    $ingredients = $array[1];
                    $session->set('ingredients', $ingredients);
                    $chatMessages[] = "L'ingrédient suivant vous convient-il ?";
                    $chatMessages[] = $ingredientSelected;
                }

            }

            $chatMessages[] = '';
        }


        $form = $this->createForm(BotType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $userResponse = $form->getData();

        }

        return $this->render('cocktail/index.html.twig', [
            'cocktails' => $cocktails,
            'ingredients' => $ingredients,
            'chatMessages' => $chatMessages,
            'userMessages' => $userMessages,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset", name="newCocktail")
     */
    public function reset(Request $request, SessionInterface $session)
    {
        $session->invalidate();
        return $this->redirectToRoute("cocktail");
    }

    public function selectOneCocktail(int $id, array $cocktails): Cocktail
    {
        foreach ($cocktails as $cocktail) {
            if ($cocktail->getId() == $id) {
                return $cocktail;
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
        array_splice($ingredients, $index, 1);
        return [$ingredient, $ingredients];
    }

    public function filterCocktailsByIngredient(string $ingredientSelected, array $cocktails): array
    {
        $cocktailsSelected = [];
        foreach ($cocktails as $cocktail) {
            if (in_array($ingredientSelected, $cocktail->getIngredients())) {
                $cocktailsSelected[] = $cocktail;
            }
        }
        return $cocktailsSelected;
    }

    public function testIngredientInCocktails(string $ingredient, array $cocktails): bool
    {
        foreach ($cocktails as $cocktail) {
            if (in_array($ingredient, $cocktail->getIngredients())) {
                return true;
            }
        }
        return false;
    }
}


