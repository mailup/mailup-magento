<?php

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS `mailup_filter_hints` (
  `filter_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `hints` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`filter_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

$this->endSetup();