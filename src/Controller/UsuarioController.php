<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UsuarioController extends Controller
{

    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

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
     * @Route("/usuario/cadastrar", name="cadastrar_usuario")
     * @Template("usuario/registro.html.twig")
     */
    public function cadastrar(Request $request, \Swift_Mailer $mailer)
    {
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $encoder = $this->get('security.password_encoder');
            $senha_cript = $encoder->encodePassword($usuario, $form->getData()->getPassword());
            $usuario->setSenha($senha_cript);
            $usuario->setToken(md5(uniqid()));
            $usuario->setRoles("ROLE_ADMIN");
            $this->em->persist($usuario);
            $this->em->flush();

            $mensagem = (new \Swift_Message($usuario->getNome(). ", ative sua conta no Microjobs Son"))
                ->setFrom('naoresponda@email.com')
                ->setTo([$usuario->getEmail() => $usuario->getNome()])
                ->setBody($this->renderView('emails/usuarios/registro.html.twig', [
                    'nome' => $usuario->getNome(),
                    'token' => $usuario->getToken()
                ]), 'text/html');

            $mailer->send($mensagem);

            $this->addFlash('success', "Cadastrado com sucesso. Verifique seu e-mail para concluir o cadastro.");
            return $this->redirectToRoute("default");
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("usuario/ativar-conta/{token}", name="email_ativar_conta")
     */
    public function ativar_conta($token)
    {
        $usuario = $this->em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
        $usuario->setStatus(true);
        $this->em->persist($usuario);
        $this->em->flush();

        $this->addFlash('success', "Usuário ativado com sucesso. Faça seu login.");
        return $this->redirectToRoute("login");

    }
}
