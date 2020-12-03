<?php
declare(strict_types=1);

namespace ReiaDev\Controller;
/**
 * TODO: Add a preview button for articles.
 * TODO: Add revision history.
 * TODO: Implement invalid link checking for articles.
 * TODO: Implement a button for downloading articles.
 */
class WikiController extends Controller {
    const TITLE_MIN_LENGTH = 4;
    const TITLE_MAX_LENGTH = 32;
    private \ReiaDev\CSRFToken $csrfToken;

    public function __construct(\ReiaDev\Model\Model $model, \Twig\Environment $twig, \ReiaDev\Flash $flash, \ReiaDev\CSRFToken $csrfToken) {
        parent::__construct($model, $twig, $flash);
        $this->csrfToken = $csrfToken;

        if (!$this->csrfToken->get()) {
            $this->csrfToken->generate();
        }
    }
    public function index(): void {
        $articles = $this->model->findAll();

        $this->render("wiki/index.twig", [
            "articles" => $articles
        ]);
    }
    public function readArticle(string $slug): void {
        $article = $this->model->findSlug($slug);
        $body = null;
        $categories = null;

        if ($article) {
            $parser = new \Netcarver\Textile\Parser();
            $body = $parser->setDocumentType("html5")->parse(htmlspecialchars($article["body"], ENT_NOQUOTES));

            if ($article["categories"]) {
                $categories = explode(",", $article["categories"]);
            }
        } else {
            header("HTTP/1.1 404 Not Found");
        }
        $this->render("wiki/article.twig", [
            "article" => $article,
            "slug" => $slug,
            "body" => $body,
            "categories" => $categories
        ]);
    }
    public function newArticle(string $slug): void {
        $formInput = $_SESSION["form_input"] ?? null;
        unset($_SESSION["form_input"]);

        if (!$this->user) {
            $this->flash->error("Please log in to view this page.");
            $this->flash->setMessages();
            header("Location: /login");
            exit();
        }
        $this->render("wiki/new.twig", [
            "slug" => $slug,
            "form_input" => $formInput,
            "csrf_token" => $this->csrfToken->get()
        ]);
    }
    public function publishArticle(): void {
        $csrfToken = $_POST["csrf_token"] ?? "";
        $title = trim($_POST["title"]) ?? "";
        $slug = $this->toSlug($title);
        $body = $_POST["body"] ?? null;
        $categories = $_POST["categories"] ?? null;
        $categorySlugs = [];
        $isHidden = null;
        $isLocked = null;

        if ($this->user->isAdministrator()) {
            $isHidden = $_POST["is_hidden"] ?? "no";
            $isLocked = $_POST["is_locked"] ?? "no";
        }
        if ($this->csrfToken->verify($csrfToken)) {
            if (!$title) {
                $this->flash->error("Please enter a title.");
            } elseif (strlen($title) < self::TITLE_MIN_LENGTH || strlen($title) > self::TITLE_MAX_LENGTH) {
                $this->flash->error("Title must be between " . self::TITLE_MIN_LENGTH . " and " . self::TITLE_MAX_LENGTH . " characters.");
            }
            $articleExists = $this->model->findSlug($slug);

            if ($articleExists) {
                $this->flash->error("An article by this title already exists.");
            }
            if ($categories) {
                $c = explode(",", $categories);

                foreach ($c as $category) {
                    $categorySlugs[] = $this->toSlug($category);
                }
            }
        } else {
            $this->flash->error("Possible Cross-Site Request Forgery. Please contact the server administrator.");
        }
        if ($this->flash->hasErrors()) {
            $this->flash->setMessages();
            $_SESSION["form_input"] = [
                "title" => $title,
                "body" => $body,
                "categories" => $categories
            ];
            if ($this->user->isAdministrator()) {
                $_SESSION["form_input"][] = [
                    "is_hidden" => $isHidden,
                    "is_locked" => $isLocked
                ];
            }
            header("Location: /wiki/new/" . $title);
        } else {
            $date = new \DateTime("now", new \DateTimeZone("UTC"));
            $this->model->addArticle($title, $slug, $body, implode(",", $categorySlugs), $this->user->id, $date->format("Y-m-d H:i:s"));

            if ($this->user->isAdministrator()) {
                $isHidden = ($isHidden === "yes" ? 1 : 0);
                $isLocked = ($isLocked === "yes" ? 1 : 0);
                $this->model->hideArticle($isHidden, $slug);
                $this->model->lockArticle($isLocked, $slug);
            }
            $this->flash->success("Wiki article created successfully.");
            $this->flash->setMessages();
            header("Location: /wiki/" . $slug);
        }
    }
    public function editArticle(string $slug): void {
        $formInput = $_SESSION["form_input"] ?? null;
        unset($_SESSION["form_input"]);

        if (!$this->user) {
            $this->flash->error("Please log in to view this page.");
            $this->flash->setMessages();
            header("Location: /login");
            exit();
        }
        $article = $this->model->findSlug($slug);

        if ($article && $article["is_locked"]) {
            if (!$this->user->isAdministrator()) {
                $this->flash->error("This article is locked and cannot be modified.");
                $this->flash->setMessages();
                header("Location: /wiki/" . $slug);
                exit();
            }
        } elseif (!$article) {
            header("Location: /wiki/" . $slug);
        }
        $this->render("wiki/edit.twig", [
            "article" => $article,
            "slug" => $slug,
            "form_input" => $formInput,
            "csrf_token" => $this->csrfToken->get()
        ]);
    }
    public function updateArticle(string $slug): void {
        $csrfToken = $_POST["csrf_token"] ?? "";
        $article = $this->model->findSlug($slug);
        $body = $_POST["body"] ?? null;
        $categories = $_POST["categories"] ?? null;
        $categorySlugs = [];
        $isHidden = null;
        $isLocked = null;

        if ($this->user->isAdministrator()) {
            $isHidden = $_POST["is_hidden"] ?? "no";
            $isLocked = $_POST["is_locked"] ?? "no";
            $isHidden = ($isHidden === "yes" ? 1 : 0);
            $isLocked = ($isLocked === "yes" ? 1 : 0);
        }
        if ($this->csrfToken->verify($csrfToken)) {
            if ($article) {
                if ($this->user->isAdministrator()) {
                    if ($body === $article["body"] && $categories === $article["categories"] && $isHidden === $article["is_hidden"] && $isLocked === $article["is_locked"]) {
                        $this->flash->error("No changes found. Please modify the article before submitting.");
                    }
                } else {
                    if ($body === $article["body"] && $categories === $article["categories"]) {
                        $this->flash->error("No changes found. Please modify the article before submitting.");
                    }
                }
            }
            if ($categories) {
                $c = explode(",", $categories);

                foreach ($c as $category) {
                    $categorySlugs[] = $this->toSlug($category);
                }
            }
        } else {
            $this->flash->error("Possible Cross-Site Request Forgery. Please contact the server administrator.");
        }
        if ($this->flash->hasErrors()) {
            $this->flash->setMessages();
            $_SESSION["form_input"] = [
                "body" => $body,
                "categories" => $categories
            ];
            if ($this->user->isAdministrator()) {
                $_SESSION["form_input"][] = [
                    "is_hidden" => $isHidden,
                    "is_locked" => $isLocked
                ];
            }
            header("Location: /wiki/edit/" . $slug);
        } else {
            $date = new \DateTime("now", new \DateTimeZone("UTC"));
            $this->model->updateArticle($body, implode(",", $categorySlugs), $this->user->id, $date->format("Y-m-d H:i:s"), $slug);

            if ($this->user->isAdministrator()) {
                $this->model->hideArticle($isHidden, $slug);
                $this->model->lockArticle($isLocked, $slug);
            }
            $this->flash->success("Wiki article updated successfully.");
            $this->flash->setMessages();
            header("Location: /wiki/" . $slug);
        }
    }
    public function download(string $slug): void {
        if (!$this->user) {
            $this->flash->error("Please log in to view this page.");
            $this->flash->setMessages();
            header("Location: /login");
            exit();
        }
        $article = $this->model->findSlug($slug);

        if ($article) {
            $content = $article["body"];

            if ($content[-1] !== "\n") {
                $content .= "\n";
            }
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . $article["slug"] . ".textile");
            header("Content-Length: " . strlen($content));
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Expires: 0");
            header("pragma: public");
            echo $content;
        } else {
            $this->flash->error("No article found to download.");
            $this->flash->setMessages();
            header("Location: /wiki");
        }
    }
}
