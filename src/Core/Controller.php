<?php

namespace App\Core;

class Controller {
    protected function model($model) {
        $modelClass = "App\\Models\\" . $model;
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        throw new \Exception("Model $model not found");
    }

    protected function view($view, $data = []) {
        // Extract data to variables
        extract($data);

        $viewFile = ROOT_PATH . "/views/" . $view . ".php";
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new \Exception("View $view not found");
        }
    }

    /**
     * Alias helper for admin controllers to render admin views.
     * Keeping it simple so existing admin views continue to work.
     */
    protected function viewAdmin($view, $data = []) {
        return $this->view($view, $data);
    }
}