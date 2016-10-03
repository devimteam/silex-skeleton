<?php

namespace App\Entity\Traits;

use \DateTime;
/**
 * Class SoftDeletableEntityTrait.
 */
trait SoftDeletableEntityTrait
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $deletedAt;

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param \Datetime|null $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(?DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted() : bool
    {
        return null !== $this->deletedAt;
    }
}
