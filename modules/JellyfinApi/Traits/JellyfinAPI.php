<?php

namespace Modules\JellyfinApi\Traits;

use Modules\JellyfinApi\Traits\JellyfinAPI\Items;
use Modules\JellyfinApi\Traits\JellyfinAPI\Libraries;
use Modules\JellyfinApi\Traits\JellyfinAPI\System;
use Modules\JellyfinApi\Traits\JellyfinAPI\Users;

trait JellyfinAPI
{
    use Libraries;
    use Items;
    use System;
    use Users;
}
