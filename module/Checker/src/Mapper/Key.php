<?php

declare(strict_types=1);

namespace Checker\Mapper;

use Database\Model\Meeting as MeetingModel;
use Database\Model\SubDecision\Key\Granting as KeyGrantingModel;
use Database\Model\SubDecision\Key\Withdrawal as KeyWithdrawalModel;
use Doctrine\ORM\EntityManager;

/**
 * Key mapper
 */
class Key
{
    use Filter;

    /**
     * Constructor
     *
     * @param EntityManager $em Doctrine entity manager.
     */
    public function __construct(protected readonly EntityManager $em)
    {
    }

    /**
     * Returns all the key code grantings in a meeting
     *
     * @return KeyGrantingModel[]
     */
    public function findKeysGrantedDuringMeeting(MeetingModel $meeting): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('k')
            ->from(KeyGrantingModel::class, 'k')
            ->innerJoin('k.decision', 'd')
            ->innerJoin('d.meeting', 'm')
            ->where('m.number = :meeting_number')
            ->andWhere('m.type = :meeting_type')
            ->setParameter('meeting_number', $meeting->getNumber())
            ->setParameter('meeting_type', $meeting->getType());

        /** @var KeyGrantingModel[] $result */
        $result = $qb->getQuery()->getResult();

        return $this->filterDeleted($result);
    }

    /**
     * Returns all the key code withdrawals in a meeting
     *
     * @return KeyWithdrawalModel[]
     */
    public function findKeysWithdrawnDuringMeeting(MeetingModel $meeting): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('k')
            ->from(KeyWithdrawalModel::class, 'k')
            ->innerJoin('k.decision', 'd')
            ->innerJoin('d.meeting', 'm')
            ->where('m.number = :meeting_number')
            ->andWhere('m.type = :meeting_type')
            ->setParameter('meeting_number', $meeting->getNumber())
            ->setParameter('meeting_type', $meeting->getType());

        /** @var KeyWithdrawalModel[] $result */
        $result = $qb->getQuery()->getResult();

        return $this->filterDeleted($result);
    }
}
