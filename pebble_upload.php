<?php
file_put_contents("data/pebble_data/".time(), file_get_contents('php://input'),FILE_APPEND);
?>