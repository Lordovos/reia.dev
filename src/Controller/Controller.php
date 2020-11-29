<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class Controller {
    protected \ReiaDev\Model\Model $model;
    protected \Twig\Environment $twig;
    protected \ReiaDev\Flash $flash;

    public function __construct(\ReiaDev\Model\Model $model, \Twig\Environment $twig, \ReiaDev\Flash $flash) {
        $this->model = $model;
        $this->twig = $twig;
        $this->flash = $flash;
    }
    protected function render(string $template, array $data): void {
        $data["flash"] = $this->flash->getMessages();
        $version = new \ReiaDev\Version();
        $data["version"] = $version->get();

        echo $this->twig->render($template, $data);
    }
}
