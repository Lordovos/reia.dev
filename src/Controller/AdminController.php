<?php
declare(strict_types=1);

namespace ReiaDev\Controller;

class AdminController extends Controller {
    public function index(): void {
        $this->hasUser();
        $this->isAdministrator();

        $userModel = new \ReiaDev\Model\UserModel();
        $users = $userModel->findAll();

        foreach ($users as $key => $user) {
            if ($user["role"] !== \ReiaDev\Role::UNVERIFIED_USER) {
                unset($users[$key]);
            }
        }
        $this->render("admin.twig", [
            "users" => $users
        ]);
    }
    public function deleteUploadedImage(int $id): void {
        $this->hasUser();
        $this->isAdministrator();

        $homeModel = new \ReiaDev\Model\HomeModel();
        $uploadedImage = $homeModel->findUploadedImageId($id);

        if ($uploadedImage) {
            $fileUrl = __DIR__ . "/../../public" . $uploadedImage["url"];

            if (file_exists($fileUrl)) {
                unlink($fileUrl);
            }
            $homeModel->removeUploadedImage($id);
            $this->flash->success("Removed the image successfully.");
        } else {
            $this->flash->error("No image found to delete.");
        }
        $this->flash->setMessages();
        header("Location: /upload");
    }
    public function banUser(int $id): void {
        $this->hasUser();
        $this->isAdministrator();

        $userModel = new \ReiaDev\Model\UserModel();
        $user = $userModel->findId($id);

        if ($user) {
            $userModel->updateRole(\ReiaDev\Role::BANNED_USER, $id);
            $this->flash->success("User " . $user["username"] . " banned.");
            $this->flash->setMessages();
            header("Location: /user/" . $user["username"]);
        } else {
            $this->flash->error("User does not exist.");
            $this->flash->setMessages();
            header("Location: /");
        }
    }
    public function unbanUser(int $id): void {
        $this->hasUser();
        $this->isAdministrator();

        $userModel = new \ReiaDev\Model\UserModel();
        $user = $userModel->findId($id);

        if ($user) {
            $userModel->updateRole(\ReiaDev\Role::USER, $id);
            $this->flash->success("User " . $user["username"] . " unbanned.");
            $this->flash->setMessages();
            header("Location: /user/" . $user["username"]);
        } else {
            $this->flash->error("User does not exist.");
            $this->flash->setMessages();
            header("Location: /");
        }
    }
    public function verifyUser(int $id): void {
        $this->hasUser();
        $this->isAdministrator();

        $userModel = new \ReiaDev\Model\UserModel();
        $user = $userModel->findId($id);

        if ($user) {
            $userModel->updateRole(\ReiaDev\Role::USER, $id);
            $this->flash->success("User " . $user["username"] . " verified.");
            $this->flash->setMessages();
            header("Location: /user/" . $user["username"]);
        } else {
            $this->flash->error("User does not exist.");
            $this->flash->setMessages();
            header("Location: /");
        }
    }
    public function deleteUser(int $id): void {
        $this->hasUser();
        $this->isAdministrator();

        $userModel = new \ReiaDev\Model\UserModel();
        $user = $userModel->findId($id);

        if ($user) {
            $userModel->delete($id);
            $this->flash->success("User " . $user["username"] . " deleted.");
            $this->flash->setMessages();
            header("Location: /admin");
        } else {
            $this->flash->error("User does not exist.");
            $this->flash->setMessages();
            header("Location: /");
        }
    }
}
