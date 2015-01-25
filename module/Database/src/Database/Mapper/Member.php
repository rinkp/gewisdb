<?php

namespace Database\Mapper;

use Database\Model\Member as MemberModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

class Member
{

    /**
     * Doctrine entity manager.
     *
     * @var EntityManager
     */
    protected $em;


    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Search for a member.
     *
     * @param string $query
     *
     * @return MemberModel
     */
    public function search($query)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('m')
            ->from('Database\Model\Member', 'm')
            ->where("CONCAT(LOWER(m.firstName), ' ', LOWER(m.lastName)) LIKE :name")
            ->orWhere("CONCAT(LOWER(m.firstName), ' ', LOWER(m.middleName), ' ', LOWER(m.lastName)) LIKE :name");

        $qb->setParameter(':name', '%' . strtolower($query) . '%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find a member (by lidnr).
     *
     * And calculate memberships.
     *
     * @param int $lidnr
     *
     * @return MemberModel
     */
    public function find($lidnr)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('m, r')
            ->from('Database\Model\Member', 'm')
            ->where('m.lidnr = :lidnr')
            ->leftJoin('m.installations', 'r')
            ->andWhere('(r.function = \'Lid\' OR r.function IS NULL)');

        // discharges
        $qbn = $this->em->createQueryBuilder();
        $qbn->select('d')
            ->from('Database\Model\SubDecision\Discharge', 'd')
            ->join('d.installation', 'x')
            ->where('x.meeting_type = r.meeting_type')
            ->andWhere('x.meeting_number = r.meeting_number')
            ->andWhere('x.decision_point = r.decision_point')
            ->andWhere('x.decision_number = r.decision_number')
            ->andWhere('x.number = r.number');

        // destroyed discharge decisions
        $qbnd = $this->em->createQueryBuilder();
        $qbnd->select('b')
            ->from('Database\Model\SubDecision\Destroy', 'b')
            ->join('b.target', 'z')
            ->where('z.meeting_type = d.meeting_type')
            ->andWhere('z.meeting_number = d.meeting_number')
            ->andWhere('z.point = d.decision_point')
            ->andWhere('z.number = d.decision_number');

        $qbn->andWhere($qbn->expr()->not(
            $qbn->expr()->exists($qbnd->getDql())
        ));

        $qb->andWhere($qb->expr()->not(
            $qb->expr()->exists($qbn->getDql())
        ));

        // destroyed installation decisions
        $qbd = $this->em->createQueryBuilder();
        $qbd->select('a')
            ->from('Database\Model\SubDecision\Destroy', 'a')
            ->join('a.target', 'y')
            ->where('y.meeting_type = r.meeting_type')
            ->andWhere('y.meeting_number = r.meeting_number')
            ->andWhere('y.point = r.decision_point')
            ->andWhere('y.number = r.decision_number');

        $qb->andWhere($qb->expr()->not(
            $qb->expr()->exists($qbd->getDql())
        ));

        $qb->setParameter(':lidnr', $lidnr);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Persist a member model.
     *
     * @param MemberModel $member Member to persist.
     */
    public function persist(MemberModel $member)
    {
        $this->em->persist($member);
        $this->em->flush();
    }

    /**
     * Get the repository for this mapper.
     *
     * @return Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('Database\Model\Member');
    }

}
