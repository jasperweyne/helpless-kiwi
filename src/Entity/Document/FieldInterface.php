<?php

namespace App\Entity\Document;

//General field interface 
interface FieldInterface
{
    public function getId(): ?string;
    
    public function getName(): ?string;

    public function getValueType(): ?string;

    public function getScheme(): ?Scheme;

   
    
    public function __toString();
}
