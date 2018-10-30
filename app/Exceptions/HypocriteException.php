<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.13
 * Time: 9:44
 */

namespace App\Exceptions;

use Throwable;

/**
 * 仅维护者可见、仅用于调试、或安全敏感的信息
 * 普通用户可见、可理解、非安全敏感的信息
 * 将以上两种错误信息分开处理
 */
class HypocriteException extends \Exception {

    private $adminOnlyMessage;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {

        if (!is_array($message)) $message = [$message];

        $this->adminOnlyMessage = $message[1] ?? $message[0];

        parent::__construct($message[0], $code, $previous);
    }

    public function setAdmin(bool $isAdmin = null) {

        if (null === $isAdmin) {
            $isAdmin = env('APP_DEBUG');
        }

        if ($isAdmin) {
            $this->message = $this->adminOnlyMessage;
        }

    }

}
