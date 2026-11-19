<?php

declare(strict_types=1);

namespace Modules\JellyfinApi\Traits;

use Modules\JellyfinApi\Traits\JellyfinAPI\Items;
use Modules\JellyfinApi\Traits\JellyfinAPI\Libraries;
use Modules\JellyfinApi\Traits\JellyfinAPI\System;
use Modules\JellyfinApi\Traits\JellyfinAPI\Users;

trait JellyfinAPI
{
    use Items;
    use Libraries;
    use System;
    use Users;
}
