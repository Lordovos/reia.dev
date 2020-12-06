<?php
declare(strict_types=1);

$router->get("/", function () use ($controllers) {
    $controllers["home_controller"]->index();
});
$router->get("/about", function () use ($controllers) {
    $controllers["home_controller"]->about();
});
$router->get("/register", function () use ($controllers) {
    $controllers["register_controller"]->index();
});
$router->post("/register", function () use ($controllers) {
    $controllers["register_controller"]->register();
});
$router->get("/login", function () use ($controllers) {
    $controllers["login_controller"]->index();
});
$router->post("/login", function () use ($controllers) {
    $controllers["login_controller"]->login();
});
$router->get("/logout", function () use ($controllers) {
    $controllers["login_controller"]->logout();
});
$router->get("/user/([a-zA-Z0-9_-]+)", function (string $username) use ($controllers) {
    $controllers["user_controller"]->index($username);
});
$router->get("/search", function () use ($controllers) {
    $controllers["home_controller"]->search("");
});
$router->get("/search/(.*)", function (string $term) use ($controllers) {
    $controllers["home_controller"]->search($term);
});
$router->post("/search", function () use ($controllers) {
    $controllers["home_controller"]->newSearch();
});
$router->get("/upload", function () use ($controllers) {
    $controllers["home_controller"]->upload();
});
$router->post("/upload", function () use ($controllers) {
    $controllers["home_controller"]->uploadImage();
});
$router->mount("/admin", function () use ($router, $controllers) {
    $router->get("/", function () use ($controllers) {
        $controllers["admin_controller"]->index();
    });
    $router->get("/uploaded-images/delete/(\d+)", function (int $id) use ($controllers) {
        $controllers["admin_controller"]->deleteUploadedImage($id);
    });
    $router->get("/user/ban/(\d+)", function (int $id) use ($controllers) {
        $controllers["admin_controller"]->banUser($id);
    });
    $router->get("/user/unban/(\d+)", function (int $id) use ($controllers) {
        $controllers["admin_controller"]->unbanUser($id);
    });
    $router->get("/user/verify/(\d+)", function (int $id) use ($controllers) {
        $controllers["admin_controller"]->verifyUser($id);
    });
});
$router->mount("/wiki", function () use ($router, $controllers) {
    $router->get("/", function () use ($controllers) {
        $controllers["wiki_controller"]->index();
    });
    $router->get("/new", function () use ($controllers) {
        $controllers["wiki_controller"]->newArticle("");
    });
    $router->get("/new/(.*)", function (string $slug) use ($controllers) {
        $controllers["wiki_controller"]->newArticle($slug);
    });
    $router->post("/new", function () use ($controllers) {
        $controllers["wiki_controller"]->publishArticle();
    });
    $router->get("/download/([a-z0-9-]+)", function (string $slug) use ($controllers) {
        $controllers["wiki_controller"]->downloadArticle($slug);
    });
    $router->get("/history/([a-z0-9-]+)", function (string $slug) use ($controllers) {
        $controllers["wiki_controller"]->articleHistory($slug);
    });
    $router->get("/([a-z0-9-]+)", function (string $slug) use ($controllers) {
        $controllers["wiki_controller"]->readArticle($slug);
    });
    $router->get("/([a-z0-9-]+)/revision/(\d+)", function (string $slug, int $revisionId) use ($controllers) {
        $controllers["wiki_controller"]->readArticleRevision($slug, $revisionId);
    });
    $router->get("/edit/([a-z0-9-]+)", function (string $slug) use ($controllers) {
        $controllers["wiki_controller"]->editArticle($slug);
    });
    $router->post("/edit/([a-z0-9-]+)", function (string $slug) use ($controllers) {
        $controllers["wiki_controller"]->updateArticle($slug);
    });
    $router->get("/edit/([a-z0-9-]+)/revision/(\d+)", function (string $slug, int $revisionId) use ($controllers) {
        $controllers["wiki_controller"]->editArticleRevision($slug, $revisionId);
    });
});
$router->set404(function () use ($controllers) {
    header("HTTP/1.1 404 Not Found");
    $controllers["home_controller"]->notFound();
});
