<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class RegisterController extends Controller {
    private \ReiaDev\CSRFToken $csrfToken;
    const USERNAME_MIN_LENGTH = 2;
    const USERNAME_MAX_LENGTH = 24;
    const PASSWORD_MIN_LENGTH = 8;

    public function __construct(\ReiaDev\Model\Model $model, \Twig\Environment $twig, \ReiaDev\Flash $flash, \ReiaDev\CSRFToken $csrfToken) {
        parent::__construct($model, $twig, $flash);
        $this->csrfToken = $csrfToken;

        if (!$this->csrfToken->get()) {
            $this->csrfToken->generate();
        }
    }
    public function index(): void {
        $formInput = $_SESSION["form_input"] ?? null;
        unset($_SESSION["form_input"]);

        $this->render("register.twig", [
            "form_input" => $formInput,
            "csrf_token" => $this->csrfToken->get()
        ]);
    }
    public function register(): void {
        $csrfToken = $_POST["csrf_token"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];

        if ($this->csrfToken->verify($csrfToken)) {
            if (!$username) {
                $this->flash->error("Please enter a username.");
            } elseif (strlen($username) < self::USERNAME_MIN_LENGTH || strlen($username) > self::USERNAME_MAX_LENGTH) {
                $this->flash->error("Username must be between " . self::USERNAME_MIN_LENGTH . " and " . self::USERNAME_MAX_LENGTH . " characters.");
            }
            // Usernames can only contain alphanumeric characters, dashes, and underscores.
            if ($username && !preg_match("/^[a-zA-Z0-9_-]+$/", $username)) {
                $this->flash->error("Username may only contain alphanumeric charactes, dashes, and underscores.");
            }
            if (!$password) {
                $this->flash->error("Please enter a password.");
            } elseif (strlen($password) < self::PASSWORD_MIN_LENGTH) {
                $this->flash->error("Password must be at least " . self::PASSWORD_MIN_LENGTH . " or more characters.");
            }
            if (!$email) {
                $this->flash->error("Please enter an email address.");
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash->error("Please enter a valid email address.");
            }
            $user = $this->model->checkIfExists($username, $email);

            if ($user) {
                $this->flash->error("An account already exists using this username or e-mail address.");
            }
        } else {
            $this->flash->error("Possible Cross-Site Request Forgery. Please contact the server administrator.");
        }
        if ($this->flash->hasErrors()) {
            $this->flash->setMessages();
            $_SESSION["form_input"] = [
                "username" => $username,
                "email" => $email
            ];
            header("Location: /register");
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $date = new \DateTime("now", new \DateTimeZone("UTC"));
            $this->model->addUser($username, $passwordHash, $email, $date->format("Y-m-d H:i:s"), \ReiaDev\Role::UNVERIFIED_USER);
            $this->flash->success("User registered successfully.");
            $this->flash->setMessages();
            $this->csrfToken->destroy();
            header("Location: /");
        }
    }
}