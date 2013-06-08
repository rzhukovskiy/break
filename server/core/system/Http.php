<?php
class Http {
    public function redirect($url) {
        header('Location: '.$url);
    }

    public function notFound() {
        header('HTTP/1.0 404 Not Found');
    }

    public function serverError() {
        header('HTTP/1.1 500 Internal Server Error');
    }
}
