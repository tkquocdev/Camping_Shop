<?php
namespace App\Core;

use App\Core\Router;

class App {
    public $router;

    public function __construct() {
        $this->router = new Router();
    }

    public function run() {
        $this->router->dispatch();
    }
}