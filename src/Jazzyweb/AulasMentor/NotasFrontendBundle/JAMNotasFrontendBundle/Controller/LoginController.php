<?php
namespace Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Controller;

use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Usuario;
use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Form\Type\RegistroType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class LoginController extends Controller
{

    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        return $this->render(
            'JAMNotasFrontendBundle:Login:login.html.twig',
            array(
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    public function registroAction(Request $request) {

        $usuario = new Usuario();

        $form = $this->createForm(RegistroType::class, $usuario);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $serviceRegistro = $this->get('jam_notas_frontend.registro');
            $serviceRegistro->registra($usuario, $form->get('password')->getData());

            return $this
                ->render(
                    'JAMNotasFrontendBundle:Login:registro_success.html.twig',
                    array('usuario' => $usuario)
                );
        }

        return $this
            ->render(
                'JAMNotasFrontendBundle:Login:registro.html.twig',
                array('form' => $form->createView())
            );
    }

    public function activarAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();

        $usuario = $em->getRepository('JAMNotasFrontendBundle:Usuario')
            ->findOneByTokenRegistro($request->get('token'));

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no registrado');
        }

        $usuario->setIsActive(true);
        $em->persist($usuario);

        $em->flush();

        return $this
            ->render(
                'JAMNotasFrontendBundle:Login:activar_success.html.twig',
                array('usuario' => $usuario)
            );
    }
}