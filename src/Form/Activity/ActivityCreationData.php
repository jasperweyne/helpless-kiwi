<?php

namespace App\Form\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;

class ActivityCreationData
{
    public Activity $activity;
    public ?int $price = null;
    public ?Location $newLocation = null;
}
