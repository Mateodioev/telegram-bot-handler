<?php 

namespace Mateodioev\TgHandler;

use Mateodioev\Utils\Arrays;
use stdClass;

use function property_exists, method_exists, count, array_shift, trim, str_replace;

/**
 * Get update info from telegram payload
 */
trait UpdatesTrait
{
  /**
   * Telegram json payload
   */
  protected stdClass $up;
    /**
   * Set telegram json(stdClass) payload
   */
  public function setUpdate(stdClass $update)
  {
    $this->up = $update;
    return $this;
  }

  /**
   * Return telegram payload
   */
  public function getUpdate(): stdClass {
    return $this->up;
  }

  public function __get($property)
  {
    $method = 'get' . $property;

    if (property_exists($this, $property)) {
      return $this->$property;
    } elseif (method_exists($this, $method)) {
      return $this->$method();
    } elseif (method_exists($this, $property)) {
      return $this->$property();
    } else {
      return $this->getUpdate()->$property ?? null;
    }
  }

  /**
   * Get sender User id
   */
  public function getUserId(): string
  {
    return $this->up->message->from->id
      ?? $this->up->callback_query->from->id
      ?? $this->up->inline_query->from->id
      ?? '';
  }

  /**
   * Return sender username
   */
  public function getUserName(): string
  {
    return $this->up->message->from->username
      ?? $this->up->callback_query->from->username
      ?? $this->up->inline_query->from->username
      ?? '';
  }

  /**
   * Get chat_id from message
   */
  public function getChatId(): string
  {
    return $this->up->message->chat->id
      ?? $this->up->callback_query->message->chat->id
      ?? '';
  }

  /**
   * Get message id
   */
  public function getMsgId(): string
  {
    return $this->up->message->message_id
      ?? $this->up->callback_query->message->message_id
      ?? '';
  }

  /**
   * Get chat type (private, group, supergroup, channel, etc)
   */
  public function getChatType(): string
  {
    return $this->up->message->chat->type
      ?? $this->up->callback_query->message->chat->type
      ?? '';
  }

  /**
   * Get message text
   */
  public function getText(): string
  {
    return $this->up->message->text
      ?? $this->up->callback_query->data
      ?? $this->up->inline_query->query
      ?? '';
  }

  public function getPayload(array $separator = [' ', '|']): string
  {
    $txt = $this->getText();
    $elements = Arrays::MultiExplode($separator, $txt);

    if (count($elements) == 1) {
      return '';
    } else {
      $cmd = array_shift($elements);
      return trim(str_replace($cmd, '', $txt));
    }
  }

  /**
   * Get Inline query id for answer query
   */
  public function getInlineId(): string {
    return $this->up->inline_query->id ?? '';
  }

  public function getInlineOffset(): string {
    return $this->up->inline_query->offset ?? '';
  }

  /**
   * Return callback query id
   */
  public function getCallbackId(): string
  {
    return $this->up->callback_query->id ?? '';
  }

  public function getFullName(): string
  {
    return trim($this->getFirstName() . ' ' . $this->getLastName());
  }

  public function getFirstName(): string
  {
    return $this->up->message->from->first_name 
      ?? $this->up->callback_query->from->first_name 
      ?? $this->up->message->reply_to_message->from->first_name 
      ?? $this->up->inline_query->from->first_name 
      ?? '';
  }

  public function getLastName(): string
  {
    return $this->up->message->from->last_name 
      ?? $this->up->callback_query->from->last_name 
      ?? $this->up->message->reply_to_message->from->last_name 
      ?? $this->up->inline_query->from->last_name
      ?? '';
  }

  public function getDocument(): stdClass|null
  {
    return $this->up->message->document
      ?? $this->up->message->reply_to_message->document
      ?? null;
  }

  public function getDocumentName(): string
  {
    return $this->getDocument()->file_name ?? '';
  }

  public function getDocumentId(): string
  {
    return $this->getDocument()->file_id ?? '';
  }

  public function getDocumentMime(): string
  {
    return $this->getDocument()->mime_type ?? '';
  }

  public function getDocumentUniqueId(): string
  {
    return $this->getDocument()->file_unique_id ?? '';
  }

  public function getInlineMsgId(): string
  {
    return $this->up->callback_query->inline_message_id ?? '';
  }

  /**
   * Get update type
   */
  public function getType(): string
  {
    if (isset($this->up->message->text)) {
      return 'message';
    } elseif (isset($this->up->message->photo)) {
      return 'photo';
    } elseif (isset($this->up->message->video)) {
      return 'video';
    } elseif (isset($this->up->message->audio)) {
      return 'audio';
    } elseif (isset($this->up->message->voice)) {
      return 'voice';
    } elseif (isset($this->up->message->document)) {
      return 'document';
    } elseif (isset($this->up->message->sticker)) {
      return 'sticker';
    } elseif (isset($this->up->message->venue)) {
      return 'venue';
    } elseif (isset($this->up->message->location)) {
      return 'location';
    } elseif (isset($this->up->inline_query)) {
      return 'inline';
    } elseif (isset($this->up->callback_query)) {
      return 'callback';
    } elseif (isset($this->up->message->new_chat_member)) {
      return 'new_chat_member';
    } elseif (isset($this->up->message->left_chat_member)) {
      return 'left_chat_member';
    } elseif (isset($this->up->message->new_chat_title)) {
      return 'new_chat_title';
    } elseif (isset($this->up->message->new_chat_photo)) {
      return 'new_chat_photo';
    } elseif (isset($this->up->message->delete_chat_photo)) {
      return 'delete_chat_photo';
    } elseif (isset($this->up->message->group_chat_created)) {
      return 'group_chat_created';
    } elseif (isset($this->up->message->channel_chat_created)) {
      return 'channel_chat_created';
    } elseif (isset($this->up->message->supergroup_chat_created)) {
      return 'supergroup_chat_created';
    } elseif (isset($this->up->message->migrate_to_chat_id)) {
      return 'migrate_to_chat_id';
    } elseif (isset($this->up->message->migrate_from_chat_id )) {
      return 'migrate_from_chat_id ';
    } elseif (isset($this->up->edited_message)) {
      return 'edited';
    } elseif (isset($this->up->message->game)) {
      return 'game';
    } elseif (isset($this->up->channel_post)) {
      return 'channel';
    } elseif (isset($this->up->edited_channel_post)) {
      return 'edited_channel';
    } else {
      return 'unknown';
    }
  }
}
