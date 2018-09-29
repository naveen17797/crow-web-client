<?php

function removeKeyFromJSONArrayByProperty($propertyName, $propertyValue, $array) {
	$index = 0;
	$indexToBeRemoved = " ";
	foreach ($array as $key) {
		$index = $index + 1;
		if ($key[$propertyName] == $propertyValue) {
           $indexToBeRemoved = $index;
		}
	}
	unset($array[$indexToBeRemoved]);
	return $array;
}




?>