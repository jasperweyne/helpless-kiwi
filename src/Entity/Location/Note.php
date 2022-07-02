<?php

namespace App\Entity\Location;

use App\Entity\Group\Group;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @GQL\Type
 * @GQL\Description("A note/comment about a location, authored by a group.")
 */
class Note
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var ?string
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @GQL\Field(type="String!")
     * @GQL\Description("The textual description of the note/comment.")
     *
     * @var string
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="notes")
     * @ORM\JoinColumn
     * @GQL\Field(type="Group")
     * @GQL\Description("The group that authored the note/comment. If empty, added by an admin.")
     *
     * @var ?Group
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Location::class, inversedBy="notes")
     * @ORM\JoinColumn(nullable=false)
     * @GQL\Field(type="Location!")
     * @GQL\Description("The location which is described by the note/comment.")
     * @Assert\NotBlank
     *
     * @var Location
     */
    private $location;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?Group
    {
        return $this->author;
    }

    public function setAuthor(?Group $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }
}
