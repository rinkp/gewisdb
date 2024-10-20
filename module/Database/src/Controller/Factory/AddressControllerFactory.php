<?php

namespace Database\Controller\Factory;

use Database\Controller\AddressController;
use Database\Service\Member as MemberService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AddressControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return AddressController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): AddressController {
        /** @var MemberService $memberService */
        $memberService = $container->get(MemberService::class);

        return new AddressController($memberService);
    }
}
