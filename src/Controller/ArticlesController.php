<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticleType;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/articles')]
final class ArticlesController extends AbstractController
{
    #[Route('/', name: 'articles', methods:['GET'])]
    public function index(ArticlesRepository $ArticlesRepo): Response
    {
        
        $articles = $ArticlesRepo->findBy([], ["createdAt" => "DESC"]);
        
        return $this->render('articles/index.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/read/{id}', name: 'article', methods:['GET'])]
    public function read(ArticlesRepository $ArticlesRepo, int $id, Articles $article): Response
    {
    
        return $this->render('articles/read.html.twig', [
            'article' => $article
        ]);
    }


    #[Route('/new', name:'article_new', methods:['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // je crée une instance de Pokemon
        $article = new Articles();

        // permet de créer le form a partir du Type (les inputs) et de l'instance(les verifs des champs)
        $form = $this->createForm(ArticleType::class, $article);

        //recupere la requete en post ou get de ce formulaire (et seulement ce formulaire)
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $em->persist($article);  //equivalent du prepare
            $em->flush(); //equivalent du execute

            // revenir sur la page des pokemon (la route qui a le name 'pokemons')
            return $this->redirectToRoute('articles');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form
        ]);
    
    }

    #[Route('/delete/{id}', name:'article_delete', methods:['POST'])]
    public function delete(int $id, Request $request, Articles $article, EntityManagerInterface $em): Response //Pokemon $pokemon permet de se passer de $pokemon = $PokemonRepo->findOneBy(["id" => $id]);
    {
        if($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token')))
        {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', "votre article a été supprimé");
            return $this->redirectToRoute('articles');
        }

        else {
            $this->addFlash('error', "echec de la suppression");
            return $this->redirectToRoute('articles');
        }
      
    }

    #[Route('/edit/{id}', name:'article_edit', methods:['GET', 'POST'])]
    public function edit(int $id, Request $request, Articles $article, EntityManagerInterface $em): Response //Pokemon $pokemon permet de se passer de $pokemon = $PokemonRepo->findOneBy(["id" => $id]);
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $article->setUpdatedAt($date);
        $form = $this->createForm(ArticleType::class, $article);

        //recupere la requete en post ou get de ce formulaire (et seulement ce formulaire)
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
              
            $em->flush(); 
            $this->addFlash('success', "votre pokemon a été modifié");
            // revenir sur la page des pokemon (la route qui a le name 'pokemons')
            return $this->redirectToRoute('articles');
        }

         return $this->render('articles/edit.html.twig', [
            'form' => $form
        ]);
    }


}
