<?php
$db = require __DIR__ . '/db.dist.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=localhost;dbname=test';
$db['user'] = 'root';
$db['password'] = '';

return $db;
