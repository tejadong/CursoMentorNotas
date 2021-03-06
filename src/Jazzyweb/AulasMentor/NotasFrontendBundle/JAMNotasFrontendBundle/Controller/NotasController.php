<?php

namespace Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Controller;

use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Etiqueta;
use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Form\Type\NotaType;
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

        list($etiquetas, $notas, $notaSeleccionada) = $this->dameEtiquetasYNotas();

        // creamos un formulario para borrar la nota
        if ($notaSeleccionada instanceof Nota) {
            $deleteForm = $this
                ->createDeleteForm($notaSeleccionada->getId())
                ->createView();
        } else {
            $deleteForm = null;
        }

        return $this
            ->render(
                'JAMNotasFrontendBundle:Notas:index.html.twig',
                array(
                    'etiquetas' => $etiquetas,
                    'notas' => $notas,
                    'nota_seleccionada' => $notaSeleccionada,
                    'delete_form' => $deleteForm,
                )
            );
    }

    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
            ;
    }

    public function nuevaAction(Request $request)
    {
        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        $em = $this->getDoctrine()->getManager();

        $nota = new Nota();
        $newForm = $this->createForm(new NotaType(), $nota);

        $newForm->handleRequest($request);

        if ($newForm->isValid()) {
            $usuario = $this->getUser();

            $item = $request->get('item');
            $this->actualizaEtiquetas($nota, $item['tags'], $usuario);

            $nota->setUsuario($usuario);
            $nota->setFecha(new \DateTime());

            if ($nota->getFile() != null)
                $nota->upload($usuario->getUsername());

            $em->persist($nota);

            $em->flush();

            $request->getSession()->set('nota.seleccionada.id', $nota->getId());

            return $this->redirect($this->generateUrl('jamn_homepage'));
        }

        return $this
            ->render(
                'JAMNotasFrontendBundle:Notas:crearOEditar.html.twig',
                array(
                    'etiquetas' => $etiquetas,
                    'notas' => $notas,
                    'nota_seleccionada' => $nota,
                    'new_form' => $newForm->createView(),
                    'edita' => false,
                )
            );

    }

    public function editarAction(Request $request)
    {
        $id = $request->get('id');
        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        $em = $this->getDoctrine()->getManager();

        $nota = $em->getRepository('JAMNotasFrontendBundle:Nota')->find($id);

        if (!$nota) {
            throw $this
                ->createNotFoundException('No se ha podido encontrar esa nota');
        }

        $editForm = $this->createForm(new NotaType(), $nota);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $usuario = $this->getUser();

            $item = $request->get('item');
            $this->actualizaEtiquetas($nota, $item['tags'], $usuario);

            $nota->setFecha(new \DateTime());

            if ($nota->getFile() != null)
                $nota->upload($usuario->getUsername());

            $em->persist($nota);

            $em->flush();

            return $this->redirect($this->generateUrl('jamn_homepage'));
        }

        return $this
            ->render(
                'JAMNotasFrontendBundle:Notas:crearOEditar.html.twig',
                array(
                    'etiquetas' => $etiquetas,
                    'notas' => $notas,
                    'nota_seleccionada' => $nota,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'edita' => true,
                )
            );
    }

    public function borrarAction(Request $request)
    {
        $session = $request->getSession();
        $form = $this->createDeleteForm($request->get('id'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em
                ->getRepository('JAMNotasFrontendBundle:Nota')
                ->find($request->get('id'));

            if (!$entity) {
                throw $this->createNotFoundException('Esa nota no existe.');
            }

            $em->remove($entity);
            $em->flush();

            $session->set('nota.seleccionada.id', '');
        }

        return $this->redirect($this->generateUrl('jamn_homepage'));
    }

    public function miEspacioAction()
    {
        $params = 'Los datos de la página de inicio del espacio premium';
        return
            $this
                ->render(
                    'JAMNotasFrontendBundle:Notas:index.html.twig',
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
    protected function dameEtiquetasYNotas()
    {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getUser();

        $busqueda_tipo = $session->get('busqueda.tipo');

        $busqueda_valor = $session->get('busqueda.valor');

        // Etiquetas. Se pillan todas
        $etiquetas = $em->getRepository('JAMNotasFrontendBundle:Etiqueta')->
        findByUsuarioOrderedByTexto($usuario);

        // Notas. Se pillan según el filtro almacenado en la sesión
        if ($busqueda_tipo == 'por_etiqueta' && $busqueda_valor != 'todas') {
            $notas = $em
                ->getRepository('JAMNotasFrontendBundle:Nota')
                ->findByUsuarioAndEtiqueta($usuario, $busqueda_valor);
        } elseif ($busqueda_tipo == 'por_termino') {
            $notas = $em
                ->getRepository('JAMNotasFrontendBundle:Nota')
                ->findByUsuarioAndTermino($usuario, $busqueda_valor);
        } else {
            $notas = $em
                ->getRepository('JAMNotasFrontendBundle:Nota')
                ->findByUsuarioOrderedByFecha($usuario);
        }

        $nota_seleccionada = null;
        if (count($notas) > 0) {
            $nota_selecionada_id = $session->get('nota.seleccionada.id');
            if (!is_null($nota_selecionada_id) && $nota_selecionada_id != '') {
                $nota_seleccionada = $em
                    ->getRepository('JAMNotasFrontendBundle:Nota')
                    ->findOneById($nota_selecionada_id);
            } else {
                $nota_seleccionada = $notas[0];
            }
        }

        return array($etiquetas, $notas, $nota_seleccionada);
    }

    public function dameNotasAction($_format)
    {
        list($etiquetas, $notas, $nota_seleccionada) = $this->dameEtiquetasYNotas();

        return $this->render('JAMNotasFrontendBundle:Notas:dameNotas.'.$_format.'.twig', array(
            'notas' => $notas
        ));
    }

    protected function actualizaEtiquetas($nota, $tags, $usuario) {

        if (count($tags) == 0) {
            $tags = array();
        }
        $em = $this->getDoctrine()->getManager();

        $nota->getEtiquetas()->clear();


        foreach ($tags as $tag) {
            $etiqueta = $em->getRepository('JAMNotasFrontendBundle:Etiqueta')->findOneByTextoAndUsuario($tag, $usuario);

            if (!$etiqueta instanceof Etiqueta) {
                $etiqueta = new Etiqueta();
                $etiqueta->setTexto($tag);
                $etiqueta->setUsuario($usuario);
                $em->persist($etiqueta);
            }

            $nota->addEtiqueta($etiqueta);
        }

        $em->flush();
    }
}
