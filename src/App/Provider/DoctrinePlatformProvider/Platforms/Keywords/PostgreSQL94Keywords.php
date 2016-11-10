<?php

namespace App\Provider\DoctrinePlatformProvider\Platforms\Keywords;

use Doctrine\DBAL\Platforms\Keywords\PostgreSQL92Keywords;

class PostgreSQL94Keywords extends PostgreSQL92Keywords
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'PostgreSQL94';
    }

    /**
     * {@inheritdoc}
     *
     * @link http://www.postgresql.org/docs/9.4/static/sql-keywords-appendix.html
     */
    protected function getKeywords()
    {
        $parentKeywords = array_diff(parent::getKeywords(), array(
            'OVER',
        ));

        return array_merge($parentKeywords, array(
            'LATERAL',
        ));
    }
}
