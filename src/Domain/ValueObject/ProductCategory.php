<?php

namespace App\Domain\ValueObject;

enum ProductCategory: string
{
    case Ring     = 'Ring';
    case Earrings = 'Earrings';
    case Necklace = 'Necklace';
    case Bracelet = 'Bracelet';
}
