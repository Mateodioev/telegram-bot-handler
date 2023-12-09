<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

enum MediaType
{
    case animation;
    case audio;
    case document;
    case photo;
    case sticker;
    case story;
    case video;
    case video_note;
    case voice;
}
