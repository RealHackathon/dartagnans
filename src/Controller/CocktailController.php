<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Form\BotType;
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

        $form = $this->createForm(BotType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $userResponse = $form->getData();
            dump($request);
            dump($userResponse);die;

            return $this->redirectToRoute('task_success');
        }


        return $this->render('cocktail/index.html.twig', ['form' => $form->createView()]);
    }
}
