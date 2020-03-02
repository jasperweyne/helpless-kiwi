<?php

namespace App\Entity\Document\Field;

use App\Entity\Document\Document;
use App\Entity\Document\Scheme\AbstractScheme;

//General field interface 
interface FieldInterface
{
    public function getId(): ?string;
    
    public function getName(): ?string;

    public function getValueType(): ?string;

    public function getScheme(): ?AbstractScheme;

   
    
    public function __toString();
}
