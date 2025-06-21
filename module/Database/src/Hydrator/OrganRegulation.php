<?php

declare(strict_types=1);

namespace Database\Hydrator;

use Application\Model\Enums\OrganTypes;
use Database\Model\Decision as DecisionModel;
use Database\Model\SubDecision\OrganRegulation as RegulationModel;
use DateTime;
use InvalidArgumentException;
use UnexpectedValueException;

use function boolval;

class OrganRegulation extends AbstractDecision
{
    /**
     * Budget hydration
     *
     * @param DecisionModel $object
     *
     * @throws InvalidArgumentException when $decision is not a Decision.
     */
    public function hydrate(
        array $data,
        $object,
    ): DecisionModel {
        $object = parent::hydrate($data, $object);

        $subdecision = new RegulationModel();
        $subdecision->setSequence(1);

        if (!($data['type'] instanceof OrganTypes)) {
            $data['type'] = OrganTypes::from($data['type']);
        }

        // Only allow committees, fraternities, and financial audit committees. This should already be handled by the
        // form, so this is just a fail-safe.
        if (
            OrganTypes::Committee !== $data['type']
            && OrganTypes::Fraternity !== $data['type']
            && OrganTypes::KCC !== $data['type']
        ) {
            throw new UnexpectedValueException('Unexpected organ type for organ regulation.');
        }

        $subdecision->setOrganType($data['type']);

        $date = new DateTime($data['date']);
        $subdecision->setDate($date);

        $subdecision->setAbbr($data['abbr']);
        $subdecision->setMember($data['author']);
        $subdecision->setVersion($data['version']);
        $subdecision->setApproval(boolval($data['approve']));
        $subdecision->setChanges(boolval($data['changes']));

        $subdecision->setDecision($object);

        return $object;
    }
}
