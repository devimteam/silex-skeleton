<?php

namespace App\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository as GedmoLogEntryRepository;
use Gedmo\Tool\Wrapper\EntityWrapper;

/**
 * Class LogEntryRepository
 */
class LogEntryRepository extends GedmoLogEntryRepository
{
    /**
     * @param object $entity
     *
     * @return array
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getLogEntriesWithRelations($entity) : array
    {
        $result = [];

        $logs = $this->getLogEntries($entity);

        if (!$logs) {
            return $result;
        }

        $wrapped = new EntityWrapper($entity, $this->_em);
        $associationMappings = $wrapped->getMetadata()->getAssociationMappings();

        foreach ($logs as $logEntry) {
            $data = $logEntry->getData();

            array_walk($data, function ($value, $field) use (&$data, $associationMappings) {
                $data[$field] = $this->mapRelation($associationMappings, $field, $value);
            });

            $logEntry->setData($data);

            $result[] = $logEntry;
        }
        return $result;
    }

    /**
     * @param array $associationMappings
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    private function mapRelation($associationMappings, $field, $value)
    {
        if (!array_key_exists($field, $associationMappings)) {
            return $value;
        }

        $mapping = $associationMappings[$field];

        if (!$value) {
            return null;
        }
        $entity = $this->_em->find($mapping['targetEntity'], $value);
        $this->_em->refresh($entity);

        return $entity;
    }

}
