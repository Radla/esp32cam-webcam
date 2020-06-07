<?php
// newest_file
$files = scandir('data', SCANDIR_SORT_DESCENDING);
$newest_file = $files[0];
