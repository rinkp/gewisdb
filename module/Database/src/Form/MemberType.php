<?php

namespace Database\Form;

use Application\Model\Enums\MembershipTypes;
use Database\Model\Member;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterProviderInterface;

class MemberType extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->add(array(
            'name' => 'type',
            'type' => 'radio',
            'options' => array(
                'label' => 'Lidmaatschapstype',
                'value_options' => array(
                    MembershipTypes::Ordinary->value => 'Gewoon lid - Ingeschreven bij faculteit M&CS',
                    MembershipTypes::External->value => 'Extern lid - Speciaal toegelaten door het bestuur',
                    MembershipTypes::Graduate->value => 'Afgestudeerde - Was lid en is speciaal toegelaten door het bestuur',
                    MembershipTypes::Honorary->value => 'Erelid - Speciaal benoemd door de ALV'
                )
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => 'Wijzig type'
            )
        ));
    }

    /**
     * Specification of input filter.
     */
    public function getInputFilterSpecification(): array
    {
        return array(
            'type' => array(
                'required' => true
            )
        );
    }
}
