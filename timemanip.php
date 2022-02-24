<?php
$dateObj= json_decode('{"start":"2022-02-10T21:00:00.000","end":"2022-02-17T06:00:00.000"}', true);

//var_dump(date("Y-m-d H:i:s", strtotime($dateObj['start'])));

//var_dump(date("Y-m-d H:i:s", strtotime($dateObj['start'])) > date("Y-m-d H:i:s", strtotime($dateObj['end'])));

$i = '';
$i = false;
var_dump($i);