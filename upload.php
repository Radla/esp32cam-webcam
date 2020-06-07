<?php
$received = file_get_contents('php://input');
$time_str = date('y-m-d-His');
$fileToWrite = "cam_".$time_str.".jpg";
file_put_contents($fileToWrite, $received);
