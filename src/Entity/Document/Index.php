<?php

namespace App\Entity\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\IndexRepository")
 */
class Index
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Scheme", inversedBy="fields")
     */
    private $scheme;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $expr;


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


    public function getExpr(): ?string
    {
        return $this->expr;
    }

    public function setExpr(string $expr): self
    {
        $this->expr = $expr;

        return $this;
    }


    //Move functions??

    private function evalExpr(?string $expr)
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
        foreach ($this->getKeyValues() as $keyVal) {
            $key = $keyVal['key'];
            $value = $keyVal['value'];

            if ($key instanceof PersonField) {
                if (null === $key->getSlug()) {
                    continue;
                }
                $key = $key->getSlug();
            }
            if ($value instanceof PersonValue) {
                $value = $value->getValue();
            }

            $vars[$key] = $value;
        }
        $vars['auth'] = $this->getAuth();

        try {
            return $lang->evaluate($expr, $vars);
        } catch (\Exception $e) {
            return null;
        }
    }

}
