<?php
require_once dirname(__DIR__)."/Controller/POSController.php";

class Router
{
    public array $routes;

    public function __construct()
    {
        $this->routes = [
            "/" => [
                "controller" => "POSController",
                "action" => "index",
            ],
            "/pos" => [
                "controller" => "POSController",
                "action" => "index",
            ],
            "/pos/client" => [
                "controller" => "POSController",
                "action" => "choisirClient",
            ],
            "/pos/panier/ajouter" => [
                "controller" => "POSController",
                "action" => "ajouterArticle",
            ],
            "/pos/panier/retirer" => [
                "controller" => "POSController",
                "action" => "retirerArticle",
            ],
            "/pos/panier/vider" => [
                "controller" => "POSController",
                "action" => "viderPanier",
            ],
            "/pos/vente" => [
                "controller" => "POSController",
                "action" => "creerVente",
            ],
        ];
    }

    public function run(): void
    {
        $uri = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);

        $controllerName = $this->routes[$uri]["controller"] ?? null;

        if ($controllerName !== null && class_exists($controllerName)) {
            $controller = new $controllerName();
            $action = $this->routes[$uri]["action"] ?? null;

            if ($action !== null && method_exists($controller, $action)) {
                $controller->$action();
            }
        } else {
            http_response_code(404);
            echo "<h1>404 - Page non trouvée</h1>";
        }
    }
}
