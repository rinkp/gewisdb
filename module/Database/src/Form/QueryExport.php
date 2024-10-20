<?php

namespace Database\Form;

use Laminas\Form\Element\Select;
use Laminas\InputFilter\InputFilterProviderInterface;

class QueryExport extends Query implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->add([
            'name' => 'type',
            'type' => Select::class,
            'options' => [
                'value_options' => [
                    'csv' => 'CSV',
                ],
            ],
        ]);

        $this->get('submit')->setAttribute('value', 'export');
        $this->get('submit')->setLabel('Exporteer');
    }

    public function getInputFilterSpecification(): array
    {
        $filter = parent::getInputFilterSpecification();
        $filter += [
            'type' => [
                'required' => true,
            ],
        ];

        return $filter;
    }
}
