<?php

namespace Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Nota
 *
 * @ORM\Table(name="nota")
 * @ORM\Entity(repositoryClass="Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Repository\NotaRepository")
 */
class Nota
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=255)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="texto", type="text")
     */
    private $texto;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date")
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    ////ASOCIACIONES////

    /**
     * @ORM\ManyToOne(targetEntity="Usuario")
     */
    private $usuario;

    /**
     * @ORM\ManyToMany(targetEntity="Etiqueta", inversedBy="notas")
     */
    private $etiquetas;

    /** @var UploadedFile $file */
    private $file;

    ////FIN ASOCIACIONES////

    public function __construct()
    {
        $this->etiquetas = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titulo
     *
     * @param string $titulo
     * @return Nota
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set texto
     *
     * @param string $texto
     * @return Nota
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;

        return $this;
    }

    /**
     * Get texto
     *
     * @return string 
     */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Nota
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Nota
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set usuario
     *
     * @param \Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Usuario $usuario
     * @return Nota
     */
    public function setUsuario(Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Add etiquetas
     *
     * @param \Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Etiqueta $etiquetas
     * @return Nota
     */
    public function addEtiqueta(Etiqueta $etiquetas)
    {
        $this->etiquetas[] = $etiquetas;

        return $this;
    }

    /**
     * Remove etiquetas
     *
     * @param \Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Entity\Etiqueta $etiquetas
     */
    public function removeEtiqueta(Etiqueta $etiquetas)
    {
        $this->etiquetas->removeElement($etiquetas);
    }

    /**
     * Get etiquetas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtiquetas()
    {
        return $this->etiquetas;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     * @return Nota
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/documents';
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        // we use the original file name here but you should
        // sanitize it at least to avoid any security issues
        $fileName = md5(uniqid()).'.'.$this->file->guessExtension();

        // move takes the target directory and then the target filename to move to
        $this->file->move($this->getUploadRootDir(), $fileName);

        // set the path property to the filename where you'ved saved the file
        $this->path = $fileName;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

}
