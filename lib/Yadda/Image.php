<?php

class Yadda_Image {
	const JPEG = 'jpeg';
	
	/**
	 * Converts an image from one format to another.
	 *
	 * @param string $in Input filename
	 * @param string $out Output filename
	 * @param string $type Output type
	 * @throws Yadda_Image_Exception
	 * @return void
	 */
	public static function convert($in, $out, $type = self::JPEG) {
		
		// read input
		if (!file_exists($in)) {
			throw new Yadda_Image_Exception('Input file doesn\'t exist');
		}
		$image = self::_read($in);
		
		switch ($type) {
			case self::JPEG:
				@imagejpeg($image, $out, 100);
				break;
				
			default:
				imagedestroy($image);
				throw new Yadda_Image_Exception('Invalid output format');
		}
		
		// clean up
		imagedestroy($image);
		if (!file_exists($out)) {
			throw new Yadda_Image_Exception('Error creating output');
		}
	}
	
	/**
	 * Resizes an image.
	 * 
	 * @param string $in Input filename
	 * @param string $out Output filename
	 * @param array $config Resize config. Allowed keys are:
	 *                      width - int max width
	 *                      height - int - max height
	 *                      crop - boolean - whether or not to crop the image
	 * @param string $type Output format
	 * @throws Yadda_Image_Exception
	 * @return void
	 */
	public static function resize($in, $out, array $config, $type = self::JPEG) {
		
		// read input
		if (!file_exists($in)) {
			throw new Yadda_Image_Exception('Input file doesn\'t exist');
		}
		$source = self::_read($in);
		
		$sourceWidth = imagesx($source);
		$sourceHeight = imagesy($source);
		$sourceRatio = $sourceWidth / $sourceHeight;
		
		$destWidth = isset($config['width']) ? (int) $config['width'] : null;
		$destHeight = isset($config['height']) ? (int) $config['height'] : null;
		$crop = isset($config['crop']) ? (bool) $config['crop'] : false;
		
		if ($destWidth === null || $destHeight === null) {
			if ($destWidth === null) {
				$destWidth = round($destHeight / $sourceHeight * $sourceWidth);
			} else {
				$destHeight = round($destWidth / $sourceWidth * $sourceHeight);
			}
			$resized = imagecreatetruecolor($destWidth, $destHeight);
			imagecopyresampled($resized, $source, 0, 0, 0, 0, $destWidth, $destHeight, $sourceWidth, $sourceHeight);
		} else {
			$destRatio = $destWidth / $destHeight;
			if ($crop === false) {
				if ($destRatio > $sourceRatio) {
					$destWidth = round($destHeight / $sourceHeight * $sourceWidth);
				} else {
					$destHeight = round($destWidth / $sourceWidth * $sourceHeight);
				}
				$resized = imagecreatetruecolor($destWidth, $destHeight);
				imagecopyresampled($resized, $source, 0, 0, 0, 0, $destWidth, $destHeight, $sourceWidth, $sourceHeight);
			} else {
				$resized = imagecreatetruecolor($destWidth, $destHeight);
				if ($destRatio > $sourceRatio) {
					$sliverWidth = $sourceWidth;
					$sourceX = 0;
					$sliverHeight = round($sliverWidth / $destWidth * $destHeight);
					$sourceY = round(($sourceHeight - $sliverHeight) / 2);
				} else {
					$sliverHeight = $sourceHeight;
					$sourceY = 0;
					$sliverWidth = round($sliverHeight / $destHeight * $destWidth);
					$sourceX = round(($sourceWidth - $sliverWidth) / 2);
				}
				imagecopyresampled($resized, $source, 0, 0, $sourceX, $sourceY, $destWidth, $destHeight, $sliverWidth, $sliverHeight);
			}
		}
		
		// write the resized image
		switch ($type) {
			case self::JPEG:
				@imagejpeg($resized, $out, 100);
				break;
				
			default:
				imagedestroy($source);
				imagedestroy($resized);
				throw new Yadda_Image_Exception('Invalid output format');
		}
		
		// clean up
		imagedestroy($source);
		imagedestroy($resized);
		if (!file_exists($out)) {
			throw new Yadda_Image_Exception('Error creating output');
		}
	}
	
	/**
	 * Reads an image from disk into a resource.
	 * 
	 * @param string $filename Input filename
	 * @throws Yadda_Image_Exception
	 * @return resource
	 */
	protected static function _read($filename) {
		$image = @imagecreatefromjpeg($filename);
		if (!$image) {
			$image = @imagecreatefrompng($filename);
			if (!$image) {
				$image = @imagecreatefromgif($filename);
				if (!$image) {
					throw new Yadda_Image_Exception('Input file isn\'t a valid image');
				}
			}
		}
		return $image;
	}
}