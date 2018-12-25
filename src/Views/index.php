<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use Controllers\Aggregator;
use Views\Template\Otokomae;

$agg = new Aggregator();
$items = $agg->getChannelsAndEpisodes();
$context = array('items'=>$items);
$context['layout'] = 'layout.php';
$otokomae = new Otokomae();
$otokomae->embed('template.php', $context);