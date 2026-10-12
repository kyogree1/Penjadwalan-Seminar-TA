<?php
// Isolated acceptance entry point; never modifies public/hot or development data.
$root = 'C:/Users/Tenma/enrolify';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && is_file($root.'/public'.$path)) {
    return false;
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
config(['filesystems.disks.local.root' => getenv('ACCEPTANCE_SCRATCH').'/private', 'session.files' => getenv('ACCEPTANCE_SCRATCH').'/sessions']);
Illuminate\Support\Facades\Vite::useHotFile(getenv('ACCEPTANCE_SCRATCH').'/unused-hot');
$app->handleRequest(Illuminate\Http\Request::capture());
