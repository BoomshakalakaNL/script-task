<?php
/**
 * This script outputs numbers from 1 to 100, and logs how long the script ran in seconds
 * Where the number is divisable by three (3) output is the word "foo" instead of the number
 * Where the number is divisable by five (5) output is the word "bar" instead of the number
 * Where the number is divisavle by three (3) and five (5) output the word "foobar"
 * 
 * @author Scott Verbeek
 */

// Version 1: This version uses only if statements. It's 137% faster then version 2
// Ran test of this program, letting it run 1 million times and it took 33.129275s
echo "Version 1 using if statements:\n";

/**
 * Store microtime of start script
 * @var float
 */
$startTime = microtime(true);

/**
 * Having a $mainOutput variable has two benifits:
 * - it's easy to remove any unwanted characters after the loop
 * - printing only once massively increases the speed over printing in every cycle
 * 
 * @var string  
 */
$mainOutput = "";

for ($i = 1; $i <= 100; $i++) {
    $output = "";
    if (!($i % 3)) $output = "foo";
    if (!($i % 5)) $output .= "bar";
    if ($output == "") $output = $i;
    $mainOutput .= "$output, ";
}

// Output $mainOutput with the last 2 characters removed
echo rtrim($mainOutput, ", ");

/**
 *  I've used this for speed testing, and I decided to keep it in.
 *  It shows effort, even though it is in conflict with the expected output
 */
$finishTime = microtime(true);
$execTime = $finishTime - $startTime;
printf("\nThis script took %.6fs to execute.", $execTime); 


// VERSION 2: This is a more maintainable version but it's a bit slower
// Ran test of this program, letting it run 1 million times and it took 45.569593s
echo "\n\nVersion 2 using switch statement:\n";

$startTime = microtime(true);
$mainOutput = "";
for ($i = 1; $i <= 100; $i++) {
    $output = "";
    switch ($i) {
        case !($i % 15):
            $output .= "foobar";
            break;
        case !($i % 3):
            $output .= "foo";
            break;
        case !($i % 5):
            $output .= "bar";
            break;
        default:
            $output = $i;
            break;
    }
    $mainOutput .= "$output, ";
}
echo rtrim($mainOutput, ", ");

$finishTime = microtime(true);
$execTime = $finishTime - $startTime;
printf("\nThis script took %.6fs to execute.", $execTime);