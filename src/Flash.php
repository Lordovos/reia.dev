<?php
declare(strict_types=1);

namespace ReiaDev;

class Flash {
    const MESSAGE_SUCCESS = "success";
    const MESSAGE_ERROR = "error";
    const MESSAGE_WARNING = "warning";
    const MESSAGE_INFO = "info";
    public array $icons = [
        self::MESSAGE_SUCCESS => "check",
        self::MESSAGE_ERROR => "times",
        self::MESSAGE_WARNING => "exclamation-triangle",
        self::MESSAGE_INFO => "info-circle"
    ];
    public array $messages;

    public function __construct() {
        $this->messages = [];
    }
    public function addMessage(string $text, string $type): void {
        $this->messages[] = [
            "text" => $text,
            "type" => $type,
            "icon" => $this->icons[$type]
        ];
    }
    public function success(string $text): void {
        $this->addMessage($text, self::MESSAGE_SUCCESS);
    }
    public function error(string $text): void {
        $this->addMessage($text, self::MESSAGE_ERROR);
    }
    public function warning(string $text): void {
        $this->addMessage($text, self::MESSAGE_WARNING);
    }
    public function info(string $text): void {
        $this->addMessage($text, self::MESSAGE_INFO);
    }
    public function setMessages(): void {
        $_SESSION["flash"] = $this->messages;
    }
    public function getMessages(): ?array {
        $messages = $_SESSION["flash"] ?? null;
        unset($_SESSION["flash"]);
        return $messages;
    }
    public function hasErrors(): bool {
        foreach ($this->messages as $message) {
            if ($message["type"] === self::MESSAGE_ERROR) {
                return true;
            }
        }
        return false;
    }
}
