<?php

namespace App\Controller;

use App\Form\CandidateFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        if ($this->getUser()) {
            return $this->redirectToRoute('app_candidate_register');
        }
        return $this->render('home/index.html.twig', [
            'user'=>$user
        ]);
    }
    // #[Route('/test', name: 'app_candid')]
    // public function index1(Request $request): Response
    // {
    //     $user = $this->getUser();
    //     $candidate = $user->getCandidate();


    //     $form = $this->createForm(CandidateFormType::class, $candidate);

    //     $form->handleRequest($request);
        
    //     return $this->render('candidate/index.html.twig', [
    //         'user'=>$user,
    //         'form' => $form->createView(),
    //         'candidate'=>$candidate,
    //     ]);
    // }
    // #[Route('/test1', name: 'app_about')]
    // public function index2(Request $request): Response
    // {
    //     $user = $this->getUser();
    //     $candidate = $user->getCandidate();
        
    //     return $this->render('home/test1.html.twig', [
    //         'user'=>$user,
    //         'candidate'=>$candidate,
    //         'examinationLanguage' => $candidate->getLanguage()
    //     ]);
    // }
}
