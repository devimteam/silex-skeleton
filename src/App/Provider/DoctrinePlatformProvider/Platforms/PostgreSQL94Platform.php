<?php

namespace App\Provider\DoctrinePlatformProvider\Platforms;

use App\Provider\DoctrinePlatformProvider\Platforms\Keywords\PostgreSQL94Keywords;
use Doctrine\DBAL\Platforms\PostgreSQL92Platform;

class PostgreSQL94Platform extends PostgreSQL92Platform
{
    /**
     * {@inheritdoc}
     */
    public function getJsonTypeDeclarationSQL(array $field)
    {
        if (!empty($field['jsonb'])) {
            return 'JSONB';
        }

        return 'JSON';
    }

    /**
     * {@inheritdoc}
     */
    protected function getReservedKeywordsClass()
    {
        return PostgreSQL94Keywords::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeDoctrineTypeMappings()
    {
        parent::initializeDoctrineTypeMappings();
        $this->doctrineTypeMapping['jsonb'] = 'json_array';
    }
}
