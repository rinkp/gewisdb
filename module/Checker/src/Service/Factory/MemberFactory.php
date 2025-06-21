<?php

declare(strict_types=1);

namespace Checker\Service\Factory;

use Checker\Mapper\Member as MemberMapper;
use Checker\Service\Member as MemberService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MemberFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): MemberService {
        /** @var MemberMapper $memberMapper */
        $memberMapper = $container->get(MemberMapper::class);
        /** @var array $config */
        $config = $container->get('config');

        return new MemberService(
            $memberMapper,
            $config,
        );
    }
}
