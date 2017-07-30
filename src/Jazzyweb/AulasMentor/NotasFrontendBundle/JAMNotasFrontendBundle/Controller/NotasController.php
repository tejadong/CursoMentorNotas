<?php

namespace Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Controller;

use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Nota;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NotasController extends Controller
{

    public function indexAction(Request $request)
    {
        $session = $request->getSession();

        $ruta = $request->get('_route');

        switch ($ruta)
        {
            case 'jamn_homepage':

                break;

            case 'jamn_conetiqueta':
                $session->set('busqueda.tipo', 'por_etiqueta');
                $session->set('busqueda.valor', $request->get('etiqueta'));
                $session->set('nota.seleccionada.id', '');

                break;

            case 'jamn_buscar':
                $session->set('busqueda.tipo', 'por_termino');
                $session->set('busqueda.valor', $request->get('termino'));
                $session->set('nota.seleccionada.id', '');

                break;
            case 'jamn_nota':
                $session->set('nota.seleccionada.id', $request->get('id'));

                break;
        }

        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        return $this->render('JAMNotasFrontendBundle:Notas:index.html.twig', array(
            'etiquetas' => $etiquetas,
            'notas' => $notas,
            'nota_seleccionada' => $nota_seleccionada,
        ));
    }

    public function nuevaAction(Request $request)
    {
        $session = $request->getSession();

//        if ($request->getMethod() == 'POST') {
//
//            // si los datos que vienen en la request son buenos guarda la nota
//
//            $session->getFlashBag()->add('mensaje', 'Se debería guardar la nota:'
//                    . $request->get('nombre') .
//                    '. Como aun no disponemos de un servicio para
//                          persistir los datos, mostramos la nota 1');
//
//            return $this->redirect($this->generateUrl('jamn_nota', array('id' => 1)));
//        }

        $nota = new Nota();

        $form = $this->createForm(Nota::class, $nota);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            if ( ($id = $em->getRepository('JAMNotasFrontendBundle:Nota')->nuevaNota($nota)) >= 1) {
                // Se procesa el formulario
                $session->get('session')->getFlashBag()->add('mensaje', 'Nota creada');
                return $this->redirect($this->generateUrl('jamn_nota', array( 'id' => $id)));
            }
        }

        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        return $this->render('JAMNotasFrontendBundle:Notas:nueva.html.twig',
            array(
                'etiquetas' => $etiquetas,
                'notas' => $notas,
                'nota_seleccionada' => $nota_seleccionada,
                'form' => $form->createView()
            )
        );

    }

    public function editarAction(Request $request)
    {
        $session = $request->getSession();

        // Se recupera la nota que viene en la request para ser editada

        $nota = array(
            'id' => $request->get('id'),
            'titulo' => 'nota',
        );


        if ($request->getMethod() == 'POST') {

            // si los datos que vienen en la request son buenos guarda la nota

            $session->getFlashBag()->add('mensaje', 'Se debería editar la nota:'
                . $request->get('titulo') .
                '. Como aún no disponemos de un servicio para persistir los
                         datos, la nota permanece igual');

            return $this->redirect(
                $this->generateUrl('jamn_nota',
                    array('id' => $request->get('id'))
                )
            );
        }

        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        return $this
            ->render('JAMNotasFrontendBundle:Notas:editar.html.twig',
                array(
                    'etiquetas' => $etiquetas,
                    'notas' => $notas,
                    'nota_a_editar' => $nota,
                ));
    }

    public function borrarAction(Request $request)
    {
        $session = $request->getSession();

        // borrado de la nota $request->get('id');

        $session->getFlashBag()->add('mensaje',
            'Se debería borrar la nota ' . $request->get('id'));

        $session->set('nota.seleccionada.id', '');

        return $this->forward('JAMNotasFrontendBundle:Notas:index');
    }

    public function miEspacioAction()
    {
        $params = 'Los datos de la página de inicio del espacio premium';
        return
            $this
                ->render(
                    'JAMNotasFrontendBundle:Notas:index',
                    array('params' => $params)
                );
    }

    public function rssAction()
    {

    }

    /**
     * Función Mock para poder desarrollar y probar la lógica de control.
     *
     * La función real que finalmente se implemente, utilizará el filtro
     * almacenado en la sesión y el modelo para calcular la etiquetas, notas
     * y nota seleccionada que en cada momento se deban pintar.
     */
//    protected function dameEtiquetasYNotas()
//    {
//        $session = $this->get('session');
//
//        $etiquetas = array(
//            array(
//                'id' => 1,
//                'texto' => 'etiqueta 1',
//            ),
//            array(
//                'id' => 2,
//                'texto' => 'etiqueta 2',
//            ),
//            array(
//                'id' => 3,
//                'texto' => 'etiqueta 3',
//            ),
//        );
//
//        $notas = array(
//            array(
//                'id' => 1,
//                'titulo' => 'nota 1',
//            ),
//            array(
//                'id' => 2,
//                'titulo' => 'nota 2',
//            ),
//            array(
//                'id' => 3,
//                'titulo' => 'nota 3',
//            ),
//        );
//
//        $nota_selecionada_id = $session->get('nota.seleccionada.id');
//        if(!$nota_selecionada_id)
//        {
//            $nota_selecionada_id = 1;
//        }
//
//        $nota_seleccionada = array(
//            'id' => $nota_selecionada_id,
//            'titulo' => 'nota '. $nota_selecionada_id,
//        );
//        return array($etiquetas, $notas, $nota_seleccionada);
//    }


    protected function dameEtiquetasYNotas()
    {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $usuario = $em->getRepository('JAMNotasFrontendBundle:Usuario')
            ->findOneByUsername('alberto');


        $busqueda_tipo = $session->get('busqueda.tipo');

        $busqueda_valor = $session->get('busqueda.valor');

        // Etiquetas. Se pillan todas
        $etiquetas = $em->getRepository('JAMNotasFrontendBundle:Etiqueta')->
        findByUsuarioOrderedByTexto($usuario);

        // Notas. Se pillan según el filtro almacenado en la sesión
        if ($busqueda_tipo == 'por_etiqueta' && $busqueda_valor != 'todas') {
            $notas = $em->getRepository('JAMNotasFrontendBundle:Nota')->
            findByUsuarioAndEtiqueta($usuario, $busqueda_valor);
        } elseif ($busqueda_tipo == 'por_termino') {
            $notas = $em->getRepository('JAMNotasFrontendBundle:Nota')->
            findByUsuarioAndTermino($usuario, $busqueda_valor);
        } else {
            $notas = $em->getRepository('JAMNotasFrontendBundle:Nota')->
            findByUsuarioOrderedByFecha($usuario);
        }


        $nota_selecionada_id = $session->get('nota.seleccionada.id','1');

        $nota_seleccionada = $em
            ->getRepository('JAMNotasFrontendBundle:Nota')
            ->findOneById($nota_selecionada_id);


        return array($etiquetas, $notas, $nota_seleccionada);
    }

    public function dameNotasAction($_format)
    {
        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        return $this->render('JAMNotasFrontendBundle:Notas:dameNotas.'.$_format.'.twig', array(
            'notas' => $notas
        ));
    }
}
