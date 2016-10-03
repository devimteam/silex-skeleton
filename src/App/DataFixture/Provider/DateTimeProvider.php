<?php

namespace App\DataFixture\Provider;

use Faker\Provider\Base as BaseProvider;

class DateTimeProvider extends BaseProvider
{
    /**
     * @param $template
     *
     * @return \DateTime
     */
    public function dateTimeFromTemplate($template)
    {
        return new \DateTime($template);
    }
}
