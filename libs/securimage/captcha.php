<?php
require_once 'securimage.php';
$img = new Securimage();
$img->image_height = 50;
$img->image_width  = 150;
$img->perturbation = 0.75;
$img->code_length = 5;
$img->charset = '0123456789';
$img->show();