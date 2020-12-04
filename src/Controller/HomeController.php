<?php
declare(strict_types=1);

namespace ReiaDev\Controller;
/**
 * Implement OpenGraph.
 * Add favicon.
 */
class HomeController extends Controller {
    const UPLOAD_MAX_SIZE = 1024 * 16;
    private \ReiaDev\CSRFToken $csrfToken;

    public function __construct(\ReiaDev\Model\Model $model, \Twig\Environment $twig, \ReiaDev\Flash $flash, \ReiaDev\CSRFToken $csrfToken) {
        parent::__construct($model, $twig, $flash);
        $this->csrfToken = $csrfToken;

        if (!$this->csrfToken->get()) {
            $this->csrfToken->generate();
        }
    }
    public function index(): void {
        $this->render("index.twig", []);
    }
    public function about(): void {
        $this->render("about.twig", []);
    }
    public function notFound(): void {
        $this->render("404.twig", []);
    }
    public function search(string $term): void {
        $results = $this->model->search($term);

        $this->render("search.twig", [
            "term" => $term,
            "results" => $results
        ]);
    }
    public function newSearch(): void {
        $term = $_POST["search"] ?? "";
        header("Location: /search/" . $term);
    }
    public function upload(): void {
        $this->hasUser();

        $uploadedImages = $this->model->findAllUploadedImages();

        $this->render("upload.twig", [
            "csrf_token" => $this->csrfToken->get(),
            "uploaded_images" => $uploadedImages
        ]);
    }
    public function uploadImage(): void {
        $csrfToken = $_POST["csrf_token"] ?? "";
        $targetDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777);
        }
        if ($_FILES["upload"]["error"] === 4) {
            $this->flash->error("No file uploaded.");
            $this->flash->setMessages();
            header("Location: /upload");
            exit();
        }
        $targetFile = $targetDir . basename($_FILES["upload"]["name"]);
        $uploadValid = true;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["upload"]["tmp_name"]);

        if ($this->csrfToken->verify($csrfToken)) {
            $uploadValid = ($check ? true : false);

            if (file_exists($targetFile)) {
                $this->flash->error("An image already exists.");
                $uploadValid = false;
            }
            if ($_FILES["upload"]["size"] > self::UPLOAD_MAX_SIZE) {
                $this->flash->error("The image's file size is too large.");
                $uploadValid = false;
            }
            if (!in_array($imageFileType, ["gif", "png", "jpg", "jpeg"])) {
                $this->flash->error("Invalid image type.");
                $uploadValid = false;
            }
        } else {
            $this->flash->error("Possible Cross-Site Request Forgery. Please contact the server administrator.");
            $uploadValid = false;
        }
        if (!$uploadValid) {
            $this->flash->setMessages();
        } elseif (move_uploaded_file($_FILES["upload"]["tmp_name"], $targetFile)) {
            $this->flash->success("Uploaded image " . basename($_FILES["upload"]["name"]) . " successfully.");
            $this->flash->setMessages();
            $date = new \DateTime("now", new \DateTimeZone("UTC"));
            $this->model->addUploadedImage("/uploads/" . basename($_FILES["upload"]["name"]), $check[0], $check[1], $this->user->id, $date->format("Y-m-d H:i:s"));
        } else {
            $this->flash->error("There was an issue uploading your image.");
            $this->flash->setMessages();
        }
        header("Location: /upload");
    }
}
