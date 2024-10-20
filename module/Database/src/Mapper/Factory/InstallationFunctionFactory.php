<?php

namespace Database\Mapper\Factory;

use Database\Mapper\InstallationFunction as InstallationFunctionMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InstallationFunctionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return InstallationFunctionMapper
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): InstallationFunctionMapper {
        return new InstallationFunctionMapper($container->get('database_doctrine_em'));
    }
}
