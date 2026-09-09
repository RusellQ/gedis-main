<?php
$hotFile = __DIR__ . '/../public/hot';
if (file_exists($hotFile)) {
    unlink($hotFile);
    echo "DELETED HOT FILE SUCCESSFULLY\n";
} else {
    echo "HOT FILE DOES NOT EXIST\n";
}
