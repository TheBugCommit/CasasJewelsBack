<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Product;
use App\Infrastructure\Cache\LayeredCache;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Product::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Product::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Product::class)]
final readonly class ProductCacheInvalidator
{
    public const string PREFIX_ITEM  = 'product_stable_';
    public const string KEY_COUNT    = 'products_count';
    public const string KEY_VERSION  = 'products_list_version';
    private const int VERSION_TTL    = 86400;

    public function __construct(
        private LayeredCache $cache
    ) {}

    public function postPersist(Product $product, PostPersistEventArgs $event): void
    {
        $this->bumpListVersion();
    }

    public function postUpdate(Product $product, PostUpdateEventArgs $event): void
    {
        $this->cache->invalidate(self::PREFIX_ITEM . $product->getId());
        $this->bumpListVersion();
    }

    public function postRemove(Product $product, PostRemoveEventArgs $event): void
    {
        $this->cache->invalidate(self::PREFIX_ITEM . $product->getId());
        $this->bumpListVersion();
    }

    private function bumpListVersion(): void
    {
        $this->cache->invalidate(self::KEY_COUNT);

        $version = (int) ($this->cache->getGlobal(self::KEY_VERSION) ?: 0);
        $this->cache->setGlobal(self::KEY_VERSION, $version + 1, self::VERSION_TTL);
    }
}
