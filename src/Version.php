<?php
declare(strict_types=1);

namespace ReiaDev;

class Version {
    const MAJOR = 0;
    const MINOR = 0;
    const PATCH = 1;

    public function get(): string {
        return self::MAJOR . "." . self::MINOR . "." . self::PATCH;
    }
}
