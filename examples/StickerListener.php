<?php

use Mateodioev\Bots\Telegram\Types\{InputFile, Sticker};
use Mateodioev\TgHandler\Events\Types\AllEvent;
use Mateodioev\TgHandler\Filters\FilterMessageMediaSticker;

#[FilterMessageMediaSticker]
class StickerListener extends AllEvent
{
    public function execute(array $args = [])
    {
        $sticker       = $this->ctx()->message()->sticker();
        $randomSticker = $this->pickOneSticker($sticker);

        $this->api()->sendSticker(
            $this->ctx()->getChatId(),
            InputFile::fromId($randomSticker)
        );
    }

    // DONT use  this shit
    private function pickOneSticker(Sticker $sticker): string
    {
        $id = $sticker->file_id;
        $stickers = $this->privateDb()->get('sticker_id', []);

        if (in_array($id, $stickers)) {
            return $stickers[\array_rand($stickers)];
        }

        $this->saveSticker($id);
        $stickers = $this->privateDb()->get('sticker_id', []);
        return $stickers[\array_rand($stickers)];
    }

    private function saveSticker(string $id): void
    {
        $olds = $this->privateDb()->get('sticker_id', []);

        $this->privateDb()->save('sticker_id', [$id, ...$olds]);
    }
}
