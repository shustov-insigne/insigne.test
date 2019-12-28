<?php

namespace App\EventListener;

use App\Entity\CacheableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EntityLifecycleSubscriber implements EventSubscriber
{
	/**
	 * @return array|string[]
	 */
	public function getSubscribedEvents()
	{
		return [
            Events::postPersist,
            Events::postUpdate,
			Events::postRemove,
		];
	}

    /**
     * @param CacheableInterface $object
     */
	private function clearCache(CacheableInterface $object)
    {
        $pool = new FilesystemAdapter('app.public_api', 0, 'cache');
        $pool->deleteItems($object->getCacheKeys());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof CacheableInterface) {
            $this->clearCache($entity);
        }
    }

	/**
	 * @param LifecycleEventArgs $args
	 */
	public function postUpdate(LifecycleEventArgs $args)
	{
		$entity = $args->getObject();
		if ($entity instanceof CacheableInterface) {
			$this->clearCache($entity);
		}
	}

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof CacheableInterface) {
            $this->clearCache($entity);
        }
    }
}