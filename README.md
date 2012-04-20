Image Manipulation
==================

Easily Manipulate Images

Requirements
------------

- PHP 5.2+
- GD/GD2

Examples
--------

Resize an image

	$image = new Image();
	$image->load('original.jpg');
	$image->resize(500,500);
	$image->save('new.jpg');

Or to make life easier

	$image- = new Image('original.jpg');
	$image->resize(500,500)->save('new.jpg');