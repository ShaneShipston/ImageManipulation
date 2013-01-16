<?php
/**
 * Image Manipulation - Easily Manipulate Images.
 * 
 * @version 1.0.1
 */
class Image 
{
	private $image;
	private $image_type;
	private $image_filename;
	
	/**
	 * Class Contructor
	 * Provides the opportunity to load an image
	 *
	 * @since 1.0.0
	 * 
	 * @param string $filename Intial Image Path + Name
	 */
	public function __construct($filename = '') 
	{
		if(!function_exists('gd_info')) throw new Exception('GD2 library not available');
		if(!empty($filename)) $this->load($filename);
	}
	
	/**
	 * Load an Image for Manipulation
	 *
	 * @since 1.0.0
	 * 
	 * @param string $filename Intial Image Path + Name
	 * @return object $this
	 */
	public function load($filename) 
	{
		if(!file_exists($filename)) throw new Exception('Image does not exist');
		
		try 
		{
			$image_info = getimagesize($filename);
		} 
		catch(Exception $e) 
		{
			// Ignore Errors
		}
		
		if(empty($image_info)) throw new Exception('File is not an image');
		
		$this->image_filename = $filename;
		$this->image_type = $image_info[2];
		
		if( $this->image_type == IMAGETYPE_JPEG ) 
			$this->image = imagecreatefromjpeg($filename);
		elseif( $this->image_type == IMAGETYPE_GIF ) 
			$this->image = imagecreatefromgif($filename);
		elseif( $this->image_type == IMAGETYPE_PNG ) 
			$this->image = imagecreatefrompng($filename);
		
		return $this;
	}
	
	/**
	 * Output Raw Image
	 *
	 * @since 1.0.0
	 * 
	 * @param string $image_type Output Image Type
	 * @param boolean $header Output header information
	 * @return object $this
	 */
	public function output($image_type = '', $header = true) 
	{
		if(empty($image_type)) $image_type = $this->image_type;
		
		if($image_type == IMAGETYPE_JPEG) 
		{
			if($header) header('Content-Type: image/jpeg');
			imagejpeg($this->image);
		} 
		elseif( $image_type == IMAGETYPE_GIF ) 
		{
			if($header) header('Content-Type: image/gif');
			imagegif($this->image);         
		}
		elseif( $image_type == IMAGETYPE_PNG ) 
		{
			if($header) header('Content-Type: image/png');
			imagepng($this->image);
		}
		
		return $this;
	}
	
	/**
	 * Return Raw Image Data
	 *
	 * @since 1.0.0
	 * 
	 * @return string Raw image code
	 */
	public function raw() 
	{
		return $this->image;
	}
	
	/**
	 * Save Image
	 *
	 * @since 1.0.0
	 * 
	 * @param string $filename Save Image Path + Name
	 * @param string $image_type Image Type
	 * @param integer $compression - Image Quality
	 * @return object|string $this | New Image Name
	 */
	public function save($filename = '', $image_type = '', $compression = 90) 
	{
		$return = false;
		
		if(empty($image_type)) $image_type = $this->image_type;
		
		// Generate File name + Save to same directory as original
		if(empty($filename)) 
		{ 
			$path = explode("/", $this->image_filename);
			$path[count($path) - 1] = md5(microtime().$path[count($path) - 1]);
			$filename = implode("/", $path).image_type_to_extension($image_type);
			$return = true;
		}
		
		if($image_type == IMAGETYPE_JPEG) 
			imagejpeg($this->image,$filename,$compression);
		elseif( $image_type == IMAGETYPE_GIF ) 
			imagegif($this->image,$filename);         
		elseif( $image_type == IMAGETYPE_PNG ) 
			imagepng($this->image,$filename);
		
		if($return) 
			return $filename; 
		
		return $this;
	}
	
	/**
	 * Grab Image Width
	 *
	 * @since 1.0.0
	 * 
	 * @return integer Image Width
	 */
	public function getWidth() 
	{
		return imagesx($this->image);
	}
	
	/**
	 * Grab Image Height
	 *
	 * @since 1.0.0
	 * 
	 * @return integer Image Height
	 */
	public function getHeight() 
	{
		return imagesy($this->image);
	}
	
	/**
	 * Height Resize, Maintain Proportions
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $height New Image Height
	 * @param boolean $expand Allow Image to be Resized Larger than Input Image
	 * @return object $this
	 */
	public function resizeToHeight($height, $expand = false) 
	{
		if($this->getHeight() > $height || $expand) 
		{
			$ratio = $height / $this->getHeight();
			$width = round($this->getWidth() * $ratio);
			$this->resize($width, $height);
		}
		
		return $this;
	}
	
	/**
	 * Width Resize, Maintain Proportions
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $width New Image Width
	 * @param boolean $expand Allow Image to be Resized Larger than Input Image
	 * @return object $this
	 */
	public function resizeToWidth($width, $expand = false) 
	{
		if($this->getWidth() > $width || $expand) 
		{
			$ratio = $width / $this->getWidth();
			$height = round($this->getHeight() * $ratio);
			$this->resize($width, $height);
		}
		
		return $this;
	}
	
	/**
	 * Scale Image to Percentage of Input Size
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $scale Percentage of Input Size
	 * @return object $this
	 */
	public function scale($scale) 
	{
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getheight() * $scale / 100; 
		$this->resize($width, $height);
		
		return $this;
	}
	
	/**
	 * Resize Image
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $width New Image Width
	 * @param integer $height New Image Height
	 * @param boolean $maintain Maintain Aspect Ratio
	 * @return object $this
	 */
	public function resize($width, $height, $maintain = false) 
	{
		$new_image = imagecreatetruecolor($width, $height);
		
		if($maintain) 
		{
			if($width / $this->getWidth() * $this->getHeight() > $height) 
				$this->resizeToWidth($width);
			else 
				$this->resizeToHeight($height);
			
			return $this;
		}
		
		$new_image = $this->alpha_blend($new_image);
		
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
		
		return $this;
	}
	
	/**
	 * Crop Image
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $width New Image Width
	 * @param integer $height New Image Height
	 * @param integer|string $x_offset X Axis Offset
	 * @param integer|string $y_offset Y Axis Offset
	 * @return object $this
	 */
	public function crop($width, $height, $x_offset = 0, $y_offset = 0) 
	{
		$new_image = imagecreatetruecolor($width, $height);
		$new_image = $this->alpha_blend($new_image);
		
		if(is_string($x_offset)) 
		{
			if($x_offset == 'left') 
				$x_offset = 0;
			elseif($x_offset == 'right') 
				$x_offset = $this->getWidth() - $width;
			elseif($x_offset == 'center') 
				$x_offset = ($this->getWidth() - $width) / 2;
			else 
				$x_offset = intval($x_offset);
		}
		
		if(is_string($y_offset)) 
		{
			if($y_offset == 'top') 
				$y_offset = 0;
			elseif($y_offset == 'bottom') 
				$y_offset = $this->getHeight() - $height;
			elseif($y_offset == 'center') 
				$y_offset = ($this->getHeight() - $height) / 2;
			else 
				$y_offset = intval($y_offset);
		}
		
		imagecopyresampled($new_image, $this->image, 0, 0, $x_offset, $y_offset, $width, $height, $width, $height);
		$this->image = $new_image;
		
		return $this;
	}
	
	/**
	 * Clear Memory Used
	 *
	 * @since 1.0.0
	 * 
	 * @return object $this
	 */
	public function destroy() 
	{
		imagedestroy($this->image);
		
		return $this;
	}
	
	/**
	 * Maintain Transparent backgrounds
	 *
	 * @since 1.0.0
	 */
	public function alpha_blend($new_image) 
	{
		if($this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG)
		{
			imagecolortransparent($this->image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
		}
		
		return $new_image;
	}
	
	/**
	 * Rotate Image
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $angle Rotation Angle, in Degrees
	 * @param integer $bg Background color
	 * @return object $this
	 */
	public function rotate($angle, $bg = 0) 
	{
		$this->image = imagerotate($this->image, $angle, $bg);
		
		return $this;
	}
	
	/**
	 * Attach Watermark
	 *
	 * @since 1.0.0
	 * 
	 * @param string $img Watermark Path + Image Name
	 * @param string|integer $x_offset X Axis Offset From Right
	 * @param string|integer $y_offset Y Axis Offset From Bottom
	 * @param integer $opacity Percentage of Original Opacity
	 * @param integer $width New Watermark Width
	 * @param integer $height New Watermark Height
	 * @return object $this
	 */
	public function watermark($img, $x_offset = 0, $y_offset = 0, $opacity = 70, $width = 0, $height = 0) 
	{
		$watermark = new Image($img);
		
		if(!empty($width) && !empty($height)) 
			$watermark->resize($width, $height);
		elseif(!empty($width)) 
			$watermark->resizeToWidth($width);
		elseif(!empty($height)) 
			$watermark->resizeToHeight($height);
		
		$mark = $watermark->opacity($opacity)->raw();
		
		$markW = $watermark->getWidth();
		$markH = $watermark->getHeight();
		$wide = $this->getWidth();
		$high = $this->getHeight();
		
		$new_image = imagecreatetruecolor($wide, $high);
		$new_image = $this->alpha_blend($new_image);
		
		if(is_string($x_offset)) 
		{
			if($x_offset == 'left') 
				$dest_x = 0;
			elseif($x_offset == 'right') 
				$dest_x = $wide - $markW;
			elseif($x_offset == 'center') 
				$dest_x = ($wide - $markW) / 2;
			else 
				$dest_x = intval($x_offset);
		} 
		else
		{
			$dest_x = $wide - $markW - $x_offset;
		}
		
		if(is_string($y_offset)) 
		{
			if($y_offset == 'left') 
				$dest_y = 0;
			elseif($y_offset == 'right') 
				$dest_y = $high - $markH;
			elseif($y_offset == 'center') 
				$dest_y = ($high - $markH) / 2;
			else 
				$dest_y = intval($y_offset);
		} 
		else 
		{
			$dest_y = $high - $markH - $y_offset;
		}
		
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $wide, $high, $wide, $high);
		imagecopy($new_image, $mark, $dest_x, $dest_y, 0, 0, $markW, $markH);
		
		$watermark->destroy();
		
		$this->image = $new_image;
		return $this;
	}
	
	/**
	 * Generate a Reflection (Changes output to PNG)
	 *
	 * @since 1.0.0
	 * 
	 * @param integer $height Reflection Height
	 * @param integer $gap Gap Between Image and Reflection
	 * @param integer $strength Starting Transparency (0 - 127, 0 being opaque)
	 * @return object $this
	 */
	public function reflection($height, $gap = 0, $strength = 120) 
	{
		$new_height = $this->getHeight() + $height + $gap;
		
		$output = imagecreatetruecolor($this->getWidth(), $new_height);
		imagealphablending($output, false);
		
		$bg = imagecolortransparent($output, imagecolorallocatealpha($output, 255, 255, 255, 127));
		imagefill($output, 0, 0, $bg);
		imagefilledrectangle($output, 0, 0, $this->getWidth(), $this->getHeight(), $bg1);
		imagecopyresampled($output , $this->image , 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
		
		$reflection_section = imagecreatetruecolor($this->getWidth(), 1);
		imagealphablending($reflection_section, false);
		
		$bg1 = imagecolortransparent($reflection_section, imagecolorallocatealpha($reflection_section, 255, 255, 255, 127));
		imagefill($reflection_section, 0, 0, $bg1);
		
		for ($y = 0; $y < $height; $y++) 
		{
			$t = ((127 - $strength) + ($strength * ($y / $height)));
			imagecopy($reflection_section, $output, 0, 0, 0, $this->getHeight()  - $y, $this->getWidth(), 1);
			imagefilter($reflection_section, IMG_FILTER_COLORIZE, 0, 0, 0, $t);
			imagecopyresized($output, $reflection_section, $a, $this->getHeight() + $y + $gap, 0, 0, $this->getWidth() - (2*$a), 1, $this->getWidth(), 1);
		}
		
		imagesavealpha($output, true);
		
		$this->image_type = IMAGETYPE_PNG;
		$this->image = $output;
		
		return $this;
	}
	
	/**
	 * Modify Opacity
	 *
	 * @since 1.0.0
	 * 
	 * @param string $opacity Percentage of original opacity
	 * @return object $this
	 */
	public function opacity($opacity) 
	{
		$new_image = imagecreatetruecolor($this->getWidth(), $this->getHeight());
		
		$black = imagecolorallocate($new_image, 0, 0, 0);
		imagecolortransparent($new_image, $black);
		imagefilledrectangle($new_image, 0, 0, $this->getWidth(), $this->getHeight(), $black);
		imagecopymerge($new_image, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $opacity);
		
		$this->image_type = IMAGETYPE_PNG;
		$this->image = $new_image;
		
		return $this;
	}
}
