<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UsuarioController extends Controller
{
    /**
     * @Route("/usuario", name="usuario")
     */
    public function index()
    {
        return $this->render('usuario/index.html.twig', [
            'controller_name' => 'UsuarioController',
        ]);
    }

    /**
     * @Route("/usuario/login", name="login")
     * @Template("usuario/login.html.twig")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return [
            'lastUsername' => $username,
            'error' => $error
        ];
    }

    /**
     *@Route("/painel", name="painel")
     */
    public function painel()
    {
        return new Response("<h1>painel</h1>");
    }
}
