<?php

namespace Checker\Service;

use Checker\Mapper\Member as MemberMapper;

class Member
{
    /**
     * @param MemberMapper $memberMapper
     * @param array $config
     */
    public function __construct(
        private readonly MemberMapper $memberMapper,
        private readonly array $config,
    ) {
    }

    /**
     * Fetch some members whose membership status should be checked.
     *
     * @return array
     */
    public function getMembersToCheck(): array
    {
        $config = $this->config['checker']['membership_api'];

        return $this->memberMapper->getMembersToCheck($config['max_total_requests'] - $config['max_manual_requests']);
    }

    /**
     * Get members who may require an adjustment to their membership type (based on whether their membership has ended).
     *
     * @return array
     */
    public function getEndingMembershipsWithNormalTypes(): array
    {
        return $this->memberMapper->getEndingMembershipsWithNormalTypes();
    }

    /**
     * Get members who require an adjustment to just their membership expiration.
     *
     * @return array
     */
    public function getExpiringMembershipsWithNormalTypes(): array
    {
        return $this->memberMapper->getExpiringMembershipsWithNormalTypes();
    }

    /**
     * Get members who are hidden or whose membership has expired.
     */
    public function getExpiredOrHiddenMembersWithAuthenticationKey(): array
    {
        return $this->memberMapper->getExpiredOrHiddenMembersWithAuthenticationKey();
    }

    /**
     * @return MemberMapper
     */
    public function getMemberMapper(): MemberMapper
    {
        return $this->memberMapper;
    }
}
