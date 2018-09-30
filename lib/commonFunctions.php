<?php

function removeKeyFromJSONArrayByProperty($propertyName, $propertyValue, $array) {
	$index = 0;
	$indexToBeRemoved = " ";
	foreach ($array as $key) {
		if ($key[$propertyName] == $propertyValue) {
          echo $indexToBeRemoved = $index;
		}
		$index = $index + 1;
	}
	unset($array[$indexToBeRemoved]);
	return $array;
}

function updateArrayByProperty ($identifierPropertyName, $identifierPropertyValue,$propertyName, $propertyValue, $array){
     $index = 0;
     $indexToBeReplaced = "";
     foreach ($array as $key) {
     	if ($key[$identifierPropertyName] == $identifierPropertyValue) {
           		$indexToBeReplaced  = $index;
     	}
     	$index = $index + 1;
     }
     for ($i = 0; $i < count($propertyName); $i++) {
     	$propName = $propertyName[$i];
     	$propValue = $propertyValue[$i];
     	$array[$indexToBeReplaced][$propName] = $propValue;
     }
     return $array;
}


function addArrayToExistingArray($propertyName, $propertyValue, $array) {
     $index = 0;
     foreach ($array as $key) { 
     	$index =  $index + 1;
     }
     $index = $index + 1;
     for ($i = 0; $i < count($propertyName); $i++) {
     	$propName = $propertyName[$i];
     	$propValue = $propertyValue[$i];
     	$array[$index][$propName] = $propValue;
     }
     return $array;
}



?>