<?php

namespace Database\Mapper\Factory;

use Database\Mapper\MailingList as MailingListMapper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MailingListFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return MailingListMapper
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): MailingListMapper {
        return new MailingListMapper($container->get('database_doctrine_em'));
    }
}
