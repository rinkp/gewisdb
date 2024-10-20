<?php

namespace Database\Controller\Factory;

use Database\Controller\ProspectiveMemberController;
use Database\Service\Member as MemberService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProspectiveMemberControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return ProspectiveMemberController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): ProspectiveMemberController {
        /** @var MemberService $memberService */
        $memberService = $container->get(MemberService::class);

        return new ProspectiveMemberController($memberService);
    }
}
