<?php
declare(strict_types=1);

namespace ReiaDev;
/**
 * CSRF stands for Cross-Site Request Forgery.
 */
class CSRFToken {
    public function generate(): void {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    public function get(): ?string {
        $token = $_SESSION["csrf_token"] ?? null;
        return $token;
    }
    public function destroy(): void {
        unset($_SESSION["csrf_token"]);
    }
    public function verify(string $token): bool {
        return hash_equals($token, $this->get());
    }
}
