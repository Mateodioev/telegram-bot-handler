<?php

namespace Mateodioev\TgHandler\Filters;

enum MessageType
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
