<?php

declare(strict_types=1);

namespace Database\Mapper;

use Application\Model\Enums\MeetingTypes;
use Database\Model\SubDecision\Abrogation as AbrogationModel;
use Database\Model\SubDecision\Annulment as AnnulmentModel;
use Database\Model\SubDecision\Discharge as DischargeModel;
use Database\Model\SubDecision\Foundation as FoundationModel;
use Database\Model\SubDecision\Installation as InstallationModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use function strtolower;

class Organ
{
    public function __construct(protected readonly EntityManager $em)
    {
    }

    /**
     * Find all organs.
     *
     * @return FoundationModel[]
     */
    public function findAll(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Find an organ. Also calculate which are its current members.
     */
    public function find(
        MeetingTypes $meetingType,
        int $meetingNumber,
        int $decisionPoint,
        int $decisionNumber,
        int $sequence,
    ): ?FoundationModel {
        $qb = $this->em->createQueryBuilder();

        $qb->select('o', 'r')
            ->from(FoundationModel::class, 'o')
            ->where('o.meeting_type = :type')
            ->andWhere('o.meeting_number = :meeting_number')
            ->andWhere('o.decision_point = :decision_point')
            ->andWhere('o.decision_number = :decision_number')
            ->andWhere('o.sequence = :sequence')
            ->leftJoin('o.references', 'r')
            ->andWhere('r INSTANCE OF Database\Model\SubDecision\Installation');

        // discharges
        $qbn = $this->em->createQueryBuilder();
        $qbn->select('d')
            ->from(DischargeModel::class, 'd')
            ->join('d.installation', 'x')
            ->where('x.meeting_type = r.meeting_type')
            ->andWhere('x.meeting_number = r.meeting_number')
            ->andWhere('x.decision_point = r.decision_point')
            ->andWhere('x.decision_number = r.decision_number')
            ->andWhere('x.sequence = r.sequence');

        // annulled discharge decisions
        $qbnd = $this->em->createQueryBuilder();
        $qbnd->select('b')
            ->from(AnnulmentModel::class, 'b')
            ->join('b.target', 'z')
            ->where('z.meeting_type = d.meeting_type')
            ->andWhere('z.meeting_number = d.meeting_number')
            ->andWhere('z.point = d.decision_point')
            ->andWhere('z.number = d.decision_number');

        $qbn->andWhere($qbn->expr()->not(
            $qbn->expr()->exists($qbnd->getDql()),
        ));

        $qb->andWhere($qb->expr()->not(
            $qb->expr()->exists($qbn->getDql()),
        ));

        // annulled installation decisions
        $qbd = $this->em->createQueryBuilder();
        $qbd->select('a')
            ->from(AnnulmentModel::class, 'a')
            ->join('a.target', 'y')
            ->where('y.meeting_type = r.meeting_type')
            ->andWhere('y.meeting_number = r.meeting_number')
            ->andWhere('y.point = r.decision_point')
            ->andWhere('y.number = r.decision_number');

        $qb->andWhere($qb->expr()->not(
            $qb->expr()->exists($qbd->getDql()),
        ));

        $qb->setParameter(':type', $meetingType);
        $qb->setParameter(':meeting_number', $meetingNumber);
        $qb->setParameter(':decision_point', $decisionPoint);
        $qb->setParameter(':decision_number', $decisionNumber);
        $qb->setParameter(':sequence', $sequence);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findSimple(
        MeetingTypes $meetingType,
        int $meetingNumber,
        int $decisionPoint,
        int $decisionNumber,
        ?int $sequence = null,
    ): ?FoundationModel {
        $qb = $this->em->createQueryBuilder();

        $qb->select('f')
            ->from(FoundationModel::class, 'f')
            ->where('f.meeting_type = :meeting_type')
            ->andWhere('f.meeting_number = :meeting_number')
            ->andWhere('f.decision_point = :decision_point')
            ->andWhere('f.decision_number = :decision_number')
            ->andWhere('f.sequence = :sequence');

        $qb->setParameter(':meeting_type', $meetingType);
        $qb->setParameter(':meeting_number', $meetingNumber);
        $qb->setParameter(':decision_point', $decisionPoint);
        $qb->setParameter(':decision_number', $decisionNumber);
        $qb->setParameter(':sequence', $sequence);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findInstallationDecision(
        MeetingTypes $meetingType,
        int $meetingNumber,
        int $decisionPoint,
        int $decisionNumber,
        ?int $sequence = null,
    ): ?InstallationModel {
        $qb = $this->em->createQueryBuilder();

        $qb->select('i')
            ->from(InstallationModel::class, 'i')
            ->where('i.meeting_type = :meeting_type')
            ->andWhere('i.meeting_number = :meeting_number')
            ->andWhere('i.decision_point = :decision_point')
            ->andWhere('i.decision_number = :decision_number')
            ->andWhere('i.sequence = :sequence');

        $qb->setParameter(':meeting_type', $meetingType);
        $qb->setParameter(':meeting_number', $meetingNumber);
        $qb->setParameter(':decision_point', $decisionPoint);
        $qb->setParameter(':decision_number', $decisionNumber);
        $qb->setParameter(':sequence', $sequence);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Search for organ decisions.
     *
     * This is a really complicated query, we might want to create a
     * materialized view for organs, with a field if they are abrogated or not.
     *
     * And since events are implemented anyways, we might want to use that to
     * automatically process changes.
     *
     * @return FoundationModel[]
     */
    public function organSearch(
        string $query,
        bool $includeAbrogated = false,
    ): array {
        $qb = $this->em->createQueryBuilder();

        $qb->select('o, d, m')
            ->from(FoundationModel::class, 'o')
            ->where('LOWER(o.name) LIKE :name')
            ->orWhere('LOWER(o.abbr) LIKE :name')
            ->join('o.decision', 'd')
            ->join('d.meeting', 'm');

        // annulled foundation decisions
        $qbd = $this->em->createQueryBuilder();
        $qbd->select('b')
            ->from(AnnulmentModel::class, 'b')
            ->join('b.target', 'y')
            ->where('y.meeting_type = o.meeting_type')
            ->andWhere('y.meeting_number = o.meeting_number')
            ->andWhere('y.point = o.decision_point')
            ->andWhere('y.number = o.decision_number');

        $qb->andWhere($qb->expr()->not(
            $qb->expr()->exists($qbd->getDql()),
        ));

        if (!$includeAbrogated) {
            // we want to leave out organs that have been abrogated
            $qbn = $this->em->createQueryBuilder();
            $qbn->select('a')
                ->from(AbrogationModel::class, 'a')
                ->join('a.foundation', 'x')
                ->where('x.meeting_type = o.meeting_type')
                ->andWhere('x.meeting_number = o.meeting_number')
                ->andWhere('x.decision_point = o.decision_point')
                ->andWhere('x.decision_number = o.decision_number')
                ->andWhere('x.sequence = o.sequence');

            // leave out annulled abrogation decisions
            $qbnd = $this->em->createQueryBuilder();
            $qbnd->select('c')
                ->from(AnnulmentModel::class, 'c')
                ->join('c.target', 'z')
                ->where('z.meeting_type = a.meeting_type')
                ->andWhere('z.meeting_number = a.meeting_number')
                ->andWhere('z.point = a.decision_point')
                ->andWhere('z.number = a.decision_number');

            $qbn->andWhere($qbn->expr()->not(
                $qbn->expr()->exists($qbnd->getDql()),
            ));

            // add the subexpression
            $qb->andWhere($qb->expr()->not(
                $qb->expr()->exists(
                    $qbn->getDql(),
                ),
            ));
        }

        $qb->setParameter(':name', '%' . strtolower($query) . '%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the repository for this mapper.
     */
    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository(FoundationModel::class);
    }
}
