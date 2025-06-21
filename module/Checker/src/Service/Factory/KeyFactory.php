<?php

declare(strict_types=1);

namespace Checker\Service\Factory;

use Checker\Mapper\Key as KeyMapper;
use Checker\Service\Key as KeyService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class KeyFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): KeyService {
        /** @var KeyMapper $keyMapper */
        $keyMapper = $container->get(KeyMapper::class);

        return new KeyService($keyMapper);
    }
}
