<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
$locale = getopt("a:")['a'];
$scraper = new Scraper();
$scraper->reset($locale);