<?php

namespace Database\Controller\Factory;

use Database\Controller\QueryController;
use Database\Service\Query as QueryService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class QueryControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return QueryController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): QueryController {
        /** @var QueryService $queryService */
        $queryService = $container->get(QueryService::class);

        return new QueryController($queryService);
    }
}
