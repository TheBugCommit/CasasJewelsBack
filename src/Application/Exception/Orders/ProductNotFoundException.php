<?php

namespace App\Application\Exception\Orders;

final class ProductNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("El producte amb ID {$id} no s'ha trobat.");
    }
}
