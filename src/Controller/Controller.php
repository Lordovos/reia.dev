<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class Controller {
    protected \Twig\Environment $twig;

    public function __construct(\Twig\Environment $twig) {
        $this->twig = $twig;
    }
    protected function render(string $template, array $data): void {
        $version = new \ReiaDev\Version();
        $data["version"] = $version->get();

        echo $this->twig->render($template, $data);
    }
}
