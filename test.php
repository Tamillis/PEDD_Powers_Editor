<?php

$var = [
    "test" => "hi"
];

echo print_r($var);
echo "<br/>";
echo is_object($var) ? "It's an object" : "It's not an object";
echo "<br/>";
if($var == true) {
    echo "It's true :-)";
}
