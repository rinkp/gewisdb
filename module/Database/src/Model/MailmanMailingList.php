<?php

declare(strict_types=1);

namespace Database\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * A model for representing mailing lists in Mailman
 */
#[Entity]
class MailmanMailingLists
{
    /**
     * The identifier of the mailing list in Mailman.
     */
    #[Id]
    #[Column(type: 'string')]
    protected string $mailmanId;

    
}