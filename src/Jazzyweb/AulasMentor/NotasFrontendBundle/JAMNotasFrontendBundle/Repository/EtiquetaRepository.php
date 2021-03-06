<?php

namespace Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Etiqueta;
use Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Usuario;

/**
 * EtiquetaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EtiquetaRepository extends EntityRepository
{
    public function findByUsuarioOrderedByTexto($username)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery(
                "SELECT e FROM JAMNotasFrontendBundle:Etiqueta e
              JOIN  e.usuario u where u.id = :username ORDER BY e.texto ASC")
            ->setParameters(array('username' => $username));

        return $query->getResult();
    }

    public function findOneByTextoAndUsuario($etiqueta, $usuario)
    {
        if ($usuario instanceof Usuario) {
            $username = $usuario->getUsername();
        } else {
            $username = $usuario;
        }

        if ($etiqueta instanceof Etiqueta) {
            $id_etiqueta = $etiqueta->getId();
        } else {
            $id_etiqueta = $etiqueta;
        }

        $query = $this->getEntityManager()
            ->createQuery(
                "SELECT e FROM JAMNotasFrontendBundle:Etiqueta e
              JOIN e.usuario u
              WHERE e.texto = :id_etiqueta and u.username=:username")
            ->setParameters(array(
                    'username' => $username,
                    'id_etiqueta' => $id_etiqueta)
            );

        return $query->getOneOrNullResult();
    }
}
