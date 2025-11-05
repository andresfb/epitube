<?php

declare(strict_types=1);

namespace App\Enums;

enum SpecialTagType: string
{
    case BANDED = 'banded';
    case DE_TITLE_WORDS = 'de_title_words';
    case RE_TITLE_WORDS = 're_title_words';
}
