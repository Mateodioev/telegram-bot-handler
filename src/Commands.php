<?php 

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\TelegramLogger as Logger;
use Mateodioev\TgHandler\Updates;
use Mateodioev\Utils\Arrays;
use UnexpectedValueException, ReflectionMethod, ReflectionException, RuntimeException;

use function in_array, is_string, strtolower, trim, str_replace, substr, is_callable,
  call_user_func_array, str_contains, forward_static_call_array, array_merge;

/**
 * Add commands midleware, before, handler
 */
class Commands extends Updates
{

  protected $fn_result = null;

  protected array $registry = [
    'on' => [], // Midlewares
    'message' => [], // Text commands
    'callback' => [], // Callback commands (buttons)
    'inline' => [], // Inline mode
  ];

  private array $allowed_types = ['message', 'callback', 'inline'];
  
  public string $namespace = '';
  public string $bot_username = '';
  public array $cmd_prefix = [];


  public function __construct(string $namespace = '', array $cmd_prefix = [])
  {
    $this->namespace = $namespace;
    $this->cmd_prefix = $cmd_prefix;
  }

  public function setBotUsername(string $botUsername)
  {
    $this->bot_username = $botUsername;
    return $this;
  }

  /**
   * Register commands
   * @param string $type txt, callback, inline
   * @param string|array $cmd_name Cmds to register
   * @param $fn Function to call (function, method class, anonymous function, etc)
   * @param $vars Params to use in $dn
   */
  public function register(string $type, string|array $cmd_name, $fn, $vars)
  {
    if (!in_array($type, $this->allowed_types)) {
      throw new UnexpectedValueException('Invalid type ' . $type);
    }

    if (is_string($cmd_name)) $cmd_name = [$cmd_name];

    foreach ($cmd_name as $cmd) {
      $this->registry[$type][$cmd] = [
        'cmd' => $cmd,
        'fn' => $fn,
        'params' => $vars
      ];
    }

    return $this;
  }

  /**
   * Register midlewares for update type
   */
  public function on(string $type, $fn, $params = [])
  {
    $this->registry['on'][$type][] = [
      'fn' => $fn,
      'params' => $params
    ];
    return $this;
  }

  public function CmdMessage($cmd_name, $fn, $params = [])
  {
    $this->register('message', $cmd_name, $fn, $params);
    return $this;
  }

  public function CmdCallback($cmd_name, $fn, $params = [])
  {
    $this->register('callback', $cmd_name, $fn, $params);
    return $this;
  }

  public function CmdInline($cmd_name, $fn, $params=[])
  {
    $this->register('inline', $cmd_name, $fn, $params);
    return $this;
  }

  /**
   * Get cmd from string
   * - Input: `/cmd_name param1 param2`
   * - Output: `cmd_name`
   */
  public function getCmdFromString(string $str): string
  {
    $str = strtolower($str);
    $str = trim(str_replace(['@', $this->bot_username], '',  $str));
    $txt = Arrays::MultiExplode([' ', '@'], $str)[0];
    
    if (in_array($txt[0], $this->cmd_prefix)) {
      return substr($txt, 1);
    } return '';
  }

  public function getCmdOnCallback(string $str): string
  {
    $cmd = strtolower($str);
    $cmd = trim(str_replace(['@', $this->bot_username], '',  $cmd));

    return Arrays::MultiExplode([' ', '@'], $cmd)[0] ?? '';
  }

  /**
   * Call a function
   * @throws RuntimeException
   */
  private function Invoker($fn, $params = []): void
  {
    if (is_callable($fn)) {
      $this->fn_result = call_user_func_array($fn, $params);
    } elseif (str_contains($fn, '@') != false || str_contains($fn, '::') != false) {
      list($controller, $method) = Arrays::MultiExplode(['@', '::'], $fn);
      
      if ($this->getNamespace() != '') {
        $controller = $this->getNamespace() . '\\' . $controller;
      }

      try {
        $reflectedMethod = new ReflectionMethod($controller, $method);

        if ($reflectedMethod->isPublic() && !$reflectedMethod->isAbstract()) {
          if ($reflectedMethod->isStatic()) {
            $this->fn_result = forward_static_call_array(array($controller, $method), $params);
          } else {
            // Get instance for nom-static methods
            if (is_string($controller)) $controller = new $controller;

            $this->fn_result = call_user_func_array([$controller, $method], $params);
          }
        } else {
          Logger::Fatal('Function: ' . $fn . ' is invalid callable');
          throw new RuntimeException('Invalid callable ' . $controller . '->' . $method);
        }
      } catch (ReflectionException $e) {
        Logger::Fatal('Can\'t create callabe: ' . $e);
        throw new RuntimeException('Fail to call create ' . $controller . '->' . $method);
      }
    }
  }

  public function Handle($fn, string $action_type = '', bool $is_on = false)
  {
    if ($is_on) {
      foreach ($fn as $midleware) {
        $params = array_merge($midleware['params'], [$this]);
        $this->Invoker($midleware['fn'], $params);
      }
    }
    if ($action_type == 'message') {
      $cmd = $this->getCmdFromString($this->getText());
    } else {
      $cmd = $this->getCmdOnCallback($this->getText());
    }
    
    if (isset($fn[$cmd])) {
      $fn = $fn[$cmd];
      $params = array_merge($fn['params'], [$this]);
      $this->Invoker($fn['fn'], $params);
    }
  }

  public function Run($fn = null)
  {
    $action_type = $this->getType();

    if (isset($this->registry['on'][$action_type])) {
      # Midlewares
      $this->Handle($this->registry['on'][$action_type], $action_type, true);
    }

    # Commands
    if (isset($this->registry[$action_type])) {
      $this->Handle($this->registry[$action_type], $action_type);
    }

    if ($fn != null && is_callable($fn)) {
      $fn($this);
    }
  }
  
  /**
   * Set namespace for all commands
   */
  public function setNamespace(string $namespace)
  {
    $this->namespace .= $namespace;
    return $this;
  }

  /**
   * Get current namespace
   */
  public function getNamespace(): string
  {
    return $this->namespace;
  }

  /**
   * Return function result
   */
  public function getFnResult(): mixed
  {
    return $this->fn_result;
  }

  public function getCommands()
  {
    return $this->registry;
  }
}
