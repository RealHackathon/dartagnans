<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Form\BotType;
use Symfony\Component\Form\Exception\LogicException;
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
        $chatMessages = [];
        $userMessages = [];

        //si on est en POST
        if ($request->request->get('bot') !== null) {

            //on récupère nos données
            $cocktails = $session->get('cocktails');
            $ingredients = $session->get('ingredients');

            //clic sur oui
            if (isset($request->request->get('bot')['yes'])){
                $session = $this->clicYes($session);
            }
            //clic sur non
            if (isset($request->request->get('bot')['no'])){
                $session = $this->clicNo($session);
            }
        } else {
            //si on est en GET c'est le premier tour

            //initialisation des données
            $string = file_get_contents("../public/api/cocktail.json");
            $json = json_decode($string, true);
            $cocktails = [];
            foreach ($json as $cocktail) {
                $cocktails[] = new Cocktail($cocktail);
            }

            $string = file_get_contents("../public/api/ingredient.json");
            $ingredients = json_decode($string, true);

            $session->set('cocktails', $cocktails);
            $session->set('ingredients', $ingredients);

            //lancement de la conversation
            $chatMessages[] = "Bonjour je suis Drinky.";
            $chatMessages[] = "Je vais vous proposer un cocktail.";

            $array = $this->selectOneIngredient($ingredients);
            if (count($array) == 0) {
                $chatMessages[] = "Nous n'avons plus rien à vous proposer.";
            } else {
                $ingredientSelected = $array[0];
                $session->set('ingredientSelected', $ingredientSelected);
                $ingredients = $array[1];
                $session->set('ingredients', $ingredients);
                $chatMessages[] = "L'ingrédient suivant vous convient-il ?";
                $chatMessages[] = $ingredientSelected;
            }

            $session->set('chatMessages', $chatMessages);
        }

        $form = $this->createForm(BotType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $userResponse = $form->getData();

        }

        return $this->render('cocktail/index.html.twig', [
            'cocktails' => $cocktails,
            'ingredients' => $ingredients,
            'chatMessages' => $session->get('chatMessages'),
            'userMessages' => $session->get('userMessages'),
            'form' => $form->createView(),
            'lastCocktail' => $session->get('lastCocktail')
        ]);
    }

    /**
     * @Route("/reset", name="newCocktail")
     */
    public function reset(Request $request, SessionInterface $session)
    {
        $session->start();
        $session->invalidate();
        return $this->redirectToRoute("cocktail");
    }

    /**
     * @param int $id
     * @param array $cocktails
     * @return Cocktail
     */
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
        if (count($ingredients) < 1) {
            return [];
        }
        $index = array_rand($ingredients, 1);
        $ingredient = $ingredients[$index];
        array_splice($ingredients, $index, 1);
        return [$ingredient, $ingredients];
    }

    /**
     * @param string $ingredientSelected
     * @param array $cocktails
     * @return array
     */
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

    /**
     * @param string $ingredient
     * @param array $cocktails
     * @return bool
     */
    public function testIngredientInCocktails(string $ingredient, array $cocktails): bool
    {
        foreach ($cocktails as $cocktail) {
            if (in_array($ingredient, $cocktail->getIngredients())) {
                return true;
            }
        }
        return false;
    }

    public function clicYes($session)
    {

        $userMessages[] = "Oui";
        $cocktails = $session->get('cocktails');
        $cocktails = $this->filterCocktailsByIngredient($session->get('ingredientSelected'), $cocktails);
        $session->set('cocktails', $cocktails);

        if (count($session->get('cocktails')) < 1) {
            $chatMessages[] = "Nous n'avons pas de cocktail à votre gout.";
        } else if (count($session->get('cocktails')) == 1) {

            $lastCocktail = array_shift($cocktails);
            $session->set('lastCocktail', $lastCocktail);
            $chatMessages[] = 'Je vous propose le cocktail :';
            $chatMessages[] = $lastCocktail->getName();
        } else {
            $chatMessages[] = "Nous avons encore du choix.";
            $chatMessages[] = "Je vais vous faire une autre proposition.";
            //selection d'un nouvel ingrédient présent dans les cocktails restants
            $results = $this->selectOneIngredient($session->get('ingredients'));
            $ingredientSelected = $results[0];
            $session->set('ingredientSelected', $ingredientSelected);
            $session->set('ingredients', $results[1]);

            while (!$this->testIngredientInCocktails($ingredientSelected, $session->get('cocktails'))) {
                $results = $this->selectOneIngredient($session->get('ingredients'));
                $ingredientSelected = $results[0];
                $session->set('ingredientSelected', $ingredientSelected);
                $session->set('ingredients', $results[1]);
            }
            $session->set('ingredientSelected', $ingredientSelected);

            $chatMessages[] = "Je vous propose :";
            $chatMessages[] = $ingredientSelected;
        }

        $session->set('chatMessages', $chatMessages);
        $session->set('userMessages', $userMessages);

        return $session;
    }

    public function clicNo($session)
    {
        $userMessages[] = "Non";

        $array = $this->selectOneIngredient($session->get('ingredients'));
        if ($array == []) {
            $chatMessages[] = "Nous n'avons plus d'ingrédients à vous proposer.";
        } else {
            $ingredientSelected = $array[0];
            $session->set('ingredientSelected', $ingredientSelected);
            $ingredients = $array[1];
            $session->set('ingredients', $ingredients);

            while (!$this->testIngredientInCocktails($ingredientSelected, $session->get('cocktails'))) {
                $results = $this->selectOneIngredient($session->get('ingredients'));
                if ($results == []) {
                    $chatMessages[] = "Nous n'avons plus d'ingrédients à vous proposer.";
                    $session->set('chatMessages', $chatMessages);
                    return $session;
                } else {
                    $ingredientSelected = $results[0];
                    $session->set('ingredientSelected', $ingredientSelected);
                    $session->set('ingredients', $results[1]);
                }

            }
            $session->set('ingredientSelected', $ingredientSelected);


            $chatMessages[] = "L'ingrédient suivant vous convient-il ?";
            $chatMessages[] = $ingredientSelected;
        }

        $session->set('chatMessages', $chatMessages);
        $session->set('userMessages', $userMessages);

        return $session;
    }
}


