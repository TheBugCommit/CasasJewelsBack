<?php

namespace App\Application\Exception;

final class OrderNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("La comanda amb ID {$id} no s'ha trobat.");
    }
}
