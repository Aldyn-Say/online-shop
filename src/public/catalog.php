<?php
session_start();
require_once 'Catalog.php';

$catalog = new Catalog();
$products = $catalog->getAllProducts();

require_once './catalog_page.php';
