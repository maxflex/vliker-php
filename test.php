<?php
$array = array
(
  '0' => 'Furniture/Chair',
  '1' => 'Furniture/Sofa/L Shaped',
  '2' => 'Furniture/Storage/Crockery Unit',
  '5' => 'Furniture/Sofa/1 Seater',
  '7' => 'Furniture/Sofa/2 Seater',
  '9' => 'Furniture/Sofa/3 Seater',
  '14' => 'Furniture/Storage/TV Unit',
);

foreach ($array as $dir_string) {
	// Explode dir string to  pieces
	$breadcrumbs = explode("/", $dir_string);

	// Generate a string form of the $tree array
	$dir .= "&tree";
	foreach ($breadcrumbs as $folder_name) {
		$dir .= "[$folder_name]";
	}
}

// Create $tree from string
parse_str("tree" . $dir);

// output
print_r($tree);
	
	
	