<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use Controllers\Aggregator;
use Views\Template\Otokomae;

$agg = new Aggregator();

ob_start();
$agg->refresh(true);
$context = array('disp' => ob_get_clean());

$context['layout'] = 'layout.php';
$otokomae = new Otokomae();
$otokomae->embed('admin.php', $context);
