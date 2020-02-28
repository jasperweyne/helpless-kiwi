<?php

namespace App\Entity\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\ExpressionRepository")
 */
class Expression implements FieldInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Scheme", inversedBy="expressions")
     */
    private $scheme;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $expr;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\AccesGroup")
     * @ORM\JoinColumn(name="edit_group", referencedColumnName="id", nullable=true)
     */
    private $canEdit;

     /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\AccesGroup")
     * @ORM\JoinColumn(name="view_group", referencedColumnName="id", nullable=true)
     */
    private $canView;



    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param string $id
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    
    public function getScheme(): ?Scheme
    {
        return $this->scheme;
    }

    public function setScheme(?Scheme $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }


    public function getExpression(): ?string
    {
        return $this->expr;
    }

    public function setExpression(string $expr): self
    {
        $this->expr = $expr;

        return $this;
    }

    public function getValueType(): ?string
    {
        return "Jochem";
    }


    public function evalValue(Document $document): ?string
    {
        
        return "joost";
    }
    
    public function getCanEdit(): ?AccesGroup
    {
        return $this->canEdit;
    }

    public function setCanEdit(?AccesGroup $edit): self
    {
        $this->canEdit = $edit;

        return $this;
    }

    public function getCanView(): ?AccesGroup
    {
        return $this->canView;
    }

    public function setCanView(?AccesGroup $view): self
    {
        $this->canView = $view;

        return $this;
    }

    private function evalExpr(?string $expr,Document $document)
    {
        if (is_null($expr)) {
            return null;
        }

        $lang = new ExpressionLanguage();
        $lang->register('has', function ($str) {
            return 'isset(${'.$str.'})';
        }, function ($arguments, $str) {
            return array_key_exists($str, $arguments);
        });

        $lang->register('get', function ($str) {
            return '(${'.$str.'} ?? null)';
        }, function ($arguments, $str) {
            return $arguments[$str] ?? null;
        });

        $vars = [];
        foreach ($document->getKeyValues() as $keyVal) {
            $key = $keyVal['key'];
            $value = $keyVal['value'];

            if ($key instanceof Field) {
                if (null === $key->getSlug()) {
                    continue;
                }
                $key = $key->getSlug();
            }
            if ($value instanceof FieldValue) {
                $value = $value->getValue();
            }

            $vars[$key] = $value;
        }
        
        

        try {
            return $lang->evaluate($expr, $vars);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function __toString()
    {
        return $this->getName();
    }

}
