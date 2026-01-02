<?php

$x=5;

//The function wont print any value because the vaue x is assigned at out of the function
//If i assigned a X valye in the function, it will display the value
function TestFunction()
{
    $x=8;
    echo "The x value is $x";
}

//It will print the valye of x is 5
echo "The value of x is $x";
?>