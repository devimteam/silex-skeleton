<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\LogEntry as GedmoLogEntry;

/**
 * @ORM\Table(name="ext_log_entries")
 * @ORM\Entity(repositoryClass="App\Repository\LogEntryRepository")
 */
class LogEntry extends GedmoLogEntry
{

    /**
     * @param \DateTime|null $dateTime
     *
     * @return LogEntry
     */
    public function setLoggedAt(\DateTime $dateTime = null) : LogEntry
    {
        $this->loggedAt = $dateTime ?? new \DateTime();

        return $this;
    }
}
