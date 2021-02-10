<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

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
            "page_title" => $this->setTitle("Wiki"),
            "articles" => $articles
        ]);
    }
    public function readArticle(string $slug): void {
        $article = $this->model->findSlug($slug);
        $body = null;
        $categories = null;

        if ($article) {
            if (!$article["latest_revision"]) {
                $this->flash->error("No latest revision set. Please report this issue to an administrator.");
                $this->flash->setMessages();
            }
            if ($article["body"]) {
                $parser = new \Netcarver\Textile\Parser();
                $body = $parser->setDocumentType("html5")->parse(htmlspecialchars($article["body"], ENT_NOQUOTES));
            }
            if ($article["categories"]) {
                $categories = explode(",", $article["categories"]);
            }
        } else {
            header("HTTP/1.1 404 Not Found");
        }
        $this->render("wiki/article.twig", [
            "page_title" => !empty($article["title"]) ? $this->setTitle($article["title"]) : $this->setTitle($slug),
            "article" => $article,
            "slug" => $slug,
            "body" => $body,
            "categories" => $categories
        ]);
    }
    public function readArticleRevision(string $slug, int $revisionId): void {
        $article = $this->model->findRevisionId($slug, $revisionId);
        $body = null;
        $categories = null;

        if ($article) {
            if ($article["revision_article_id"] !== $article["id"]) {
                $this->flash->error("The revision you're trying to view is not associated with this article.");
                $this->flash->setMessages();
                header("Location: /wiki/" . $slug);
                exit();
            }
            if ($article["body"]) {
                $parser = new \Netcarver\Textile\Parser();
                $body = $parser->setDocumentType("html5")->parse(htmlspecialchars($article["body"], ENT_NOQUOTES));
            }
            if ($article["categories"]) {
                $categories = explode(",", $article["categories"]);
            }
        } else {
            header("HTTP/1.1 404 Not Found");
        }
        $this->render("wiki/article.twig", [
            "page_title" => !empty($article["title"]) ? $this->setTitle($article["title"]) : $this->setTitle($slug),
            "article" => $article,
            "slug" => $slug,
            "body" => $body,
            "categories" => $categories
        ]);
    }
    public function articleHistory(string $slug): void {
        $article = $this->model->findSlug($slug);
        $revisions = null;
        $categories = null;
        $pageTitle = "";

        if ($article) {
            $pageTitle = $article["title"];
            $revisions = $this->model->getRevisions($article["id"]);

            if ($article["categories"]) {
                $categories = explode(",", $article["categories"]);
            }
        } else {
            $this->flash->error("No revision history found.");
            $this->flash->setMessages();
            header("Location: /wiki/" . $slug);
            exit();
        }
        $this->render("/wiki/history.twig", [
            "page_title" => !empty($article["title"]) ? $this->setTitle($article["title"]) : $this->setTitle($slug),
            "article" => $article,
            "slug" => $slug,
            "revisions" => $revisions,
            "categories" => $categories
        ]);
    }
    public function newArticle(string $slug): void {
        $formConstraints = [
            "title_min_length" => self::TITLE_MIN_LENGTH,
            "title_max_length" => self::TITLE_MAX_LENGTH
        ];
        $formInput = $_SESSION["form_input"] ?? null;
        unset($_SESSION["form_input"]);
        $this->hasUser();

        $this->render("wiki/new.twig", [
            "page_title" => $slug ? $this->setTitle($slug) : $this->setTitle("New Wiki Article"),
            "slug" => $slug,
            "form_constraints" => $formConstraints,
            "form_input" => $formInput,
            "csrf_token" => $this->csrfToken->get()
        ]);
    }
    public function publishArticle(): void {
        $csrfToken = $_POST["csrf_token"] ?? "";
        $title = trim($_POST["title"]) ?? "";
        $slug = $this->toSlug($title);
        $body = $_POST["body"] ?? "";
        $categories = $_POST["categories"] ?? "";
        $categorySlugs = [];
        $isHidden = null;
        $isLocked = null;

        if ($this->user && $this->user->isAdministrator()) {
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
            if ($this->user && $this->user->isAdministrator()) {
                $_SESSION["form_input"][] = [
                    "is_hidden" => $isHidden,
                    "is_locked" => $isLocked
                ];
            }
            header("Location: /wiki/new/" . $title);
        } else {
            $date = new \DateTime("now", new \DateTimeZone("UTC"));
            $article = $this->model->addArticle($title, $slug, implode(",", $categorySlugs));
            $revision = $this->model->addRevision($body, "Initial revision.", $this->user->id, $date->format("Y-m-d H:i:s"), $article["id"]);
            $this->model->setLatestRevision($revision["id"], $article["id"]);

            if ($this->user && $this->user->isAdministrator()) {
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
        $this->hasUser();

        if (!$this->user) {
            $this->setPreviousUrl($_SERVER["REQUEST_URI"]);
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
            "page_title" => !empty($article["title"]) ? $this->setTitle($article["title"]) : $this->setTitle($slug),
            "article" => $article,
            "slug" => $slug,
            "form_input" => $formInput,
            "csrf_token" => $this->csrfToken->get()
        ]);
    }
    public function editArticleRevision(string $slug, int $revisionId): void {
        $this->hasUser();

        $article = $this->model->findRevisionId($slug, $revisionId);

        if ($article) {
            if ($article["revision_article_id"] !== $article["id"]) {
                $this->flash->error("The revision you're trying to edit is not associated with this article.");
                $this->flash->setMessages();
                header("Location: /wiki/" . $slug);
                exit();
            }
        } else {
            header("Location: /wiki/" . $slug);
        }
        $this->render("wiki/edit-revision.twig", [
            "page_title" => !empty($article["title"]) ? $this->setTitle($article["title"]) : $this->setTitle($slug),
            "article" => $article,
            "slug" => $slug
        ]);
    }
    public function updateArticle(string $slug): void {
        $csrfToken = $_POST["csrf_token"] ?? "";
        $article = $this->model->findSlug($slug);
        $body = $_POST["body"] ?? "";
        $categories = $_POST["categories"] ?? "";
        $categorySlugs = [];
        $reason = $_POST["reason"] ?? "";
        $isHidden = null;
        $isLocked = null;

        if ($this->user && $this->user->isAdministrator()) {
            $isHidden = $_POST["is_hidden"] ?? "no";
            $isLocked = $_POST["is_locked"] ?? "no";
            $isHidden = ($isHidden === "yes" ? 1 : 0);
            $isLocked = ($isLocked === "yes" ? 1 : 0);
        }
        if ($this->csrfToken->verify($csrfToken)) {
            if ($article) {
                if ($this->user && $this->user->isAdministrator()) {
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
                "categories" => $categories,
                "reason" => $reason
            ];
            if ($this->user && $this->user->isAdministrator()) {
                $_SESSION["form_input"][] = [
                    "is_hidden" => $isHidden,
                    "is_locked" => $isLocked
                ];
            }
            header("Location: /wiki/edit/" . $slug);
        } else {
            $date = new \DateTime("now", new \DateTimeZone("UTC"));
            $updatedArticle = $this->model->updateArticle(implode(",", $categorySlugs), $slug);

            if ($updatedArticle && $body !== $article["body"]) {
                $revision = $this->model->addRevision($body, $reason, $this->user->id, $date->format("Y-m-d H:i:s"), $article["id"]);
                $this->model->setLatestRevision($revision["id"], $article["id"]);
            }
            if ($this->user && $this->user->isAdministrator()) {
                $this->model->hideArticle($isHidden, $slug);
                $this->model->lockArticle($isLocked, $slug);
            }
            $this->flash->success("Wiki article updated successfully.");
            $this->flash->setMessages();
            header("Location: /wiki/" . $slug);
        }
    }
    public function previewArticle(): void {
        $body = $_REQUEST["body"] ?? "";
        $parser = new \Netcarver\Textile\Parser();
        $parsedBody = $parser->setDocumentType("html5")->parse(htmlspecialchars($body, ENT_NOQUOTES));
        echo $parsedBody;
    }
    public function downloadArticle(string $slug): void {
        $this->hasUser();

        $article = $this->model->findSlug($slug);

        if ($article) {
            $content = $article["body"];

            if (strlen($content) > 0) {
                /**
                 * Append a newline to the end of the file if one is not found.
                 */
                if ($content[-1] !== "\n") {
                    $content .= "\n";
                }
            } else {
                $content = "";
            }
            header("Content-Description: File Transfer");
            header("Content-Type: text/plain");
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
