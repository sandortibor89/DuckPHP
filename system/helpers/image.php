<?php
namespace helper;

class Image {

	public function aspectratio(int $w, int $h, $return_string = true) {
		$gcd = gmp_strval(gmp_gcd($w,$h));
		return $return_string ? $w / $gcd . ":" . $h / $gcd : [$w / $gcd, $h / $gcd];
	}

	public function resize(string $file, int $w = null, int $h = null, bool $enlarge = false, int $t_w = null, int $t_h = null): bool {
		try {
			// check file, and MIME type
			if (!file_exists($file) || !in_array($mime = mime_content_type($file), ['image/jpeg','image/png'])) { return false; }
			list($w_i, $h_i) = getimagesize($file);
			// check aspect ratio
			if ((is_null($w) || $w < 1) && (is_null($h) || $h < 1)) {
				return false;
			} elseif ((is_null($w) || $w < 1) || (is_null($h) || $h < 1)) {
				$aspectratio = $this -> aspectratio($w_i, $h_i, false);
			} else {
				$aspectratio = $this -> aspectratio($w, $h, false);
			}
			// thumb
			if ((is_null($t_w) || $t_w < 1) && (is_null($t_h) || $t_h < 1)) {
				$thumb = false;
			} elseif ((is_null($w) || $w < 1) || (is_null($h) || $h < 1)) {
				$thumb = true;
				$t_aspectratio = $aspectratio;
			} else {
				$thumb = true;
				$t_aspectratio = $this -> aspectratio($t_w, $t_h, false);
			}
			$gcd_w = reset($aspectratio);
			$gcd_h = end($aspectratio);
			if (is_null($w) || $w < 1) { $w = $gcd_w * $h / $gcd_h; }
			if (is_null($h) || $h < 1) { $h = $gcd_h * $w / $gcd_w; }
			$w_o = $w_i;
			$h_o = $gcd_h * $w_o / $gcd_w;
			if ($h_i < $h_o) {
				$h_o = $h_i;
				$w_o = $gcd_w * $h_o / $gcd_h;
			}
			$x_o = $w_i - $w_o;
			$y_o = $h_i - $h_o;
			if ($w_o > $w || $h_o > $h || $enlarge) {
				$n_w = $w;
				$n_h = $h;
			} else {
				$n_w = $w_o;
				$n_h = $h_o;
			}
			if ($thumb) {
				$t_gcd_w = reset($t_aspectratio);
				$t_gcd_h = end($t_aspectratio);
				if (is_null($t_w) || $t_w < 1) { $t_w = $t_gcd_w * $t_h / $t_gcd_h; }
				if (is_null($t_h) || $t_h < 1) { $t_h = $t_gcd_h * $t_w / $t_gcd_w; }
				$t_w_o = $w_i;
				$t_h_o = $t_gcd_h * $t_w_o / $t_gcd_w;
				if ($h_i < $t_h_o) {
					$t_h_o = $h_i;
					$t_w_o = $t_gcd_w * $t_h_o / $t_gcd_h;
				}
				$t_x_o = $w_i - $t_w_o;
				$t_y_o = $h_i - $t_h_o;
			}
			switch($mime) {
				case 'image/jpeg': $img_i = @imagecreatefromjpeg($file); break;
				case 'image/png': $img_i = @imagecreatefrompng($file); break;
			}
			$img_o = imagecreatetruecolor($n_w, $n_h);
			if ($thumb) { $t_img_o = imagecreatetruecolor($t_w, $t_h); }
			if ($mime == 'image/png') {
				imagealphablending($img_o, false);
				imagesavealpha($img_o, true);
				$transparent = imagecolorallocatealpha($img_o, 255, 255, 255, 127);
				imagefilledrectangle($img_o, 0, 0, $x_o/2, $y_o/2, $transparent);
				if ($thumb) {
					imagealphablending($t_img_o, false);
					imagesavealpha($t_img_o, true);
					$transparent = imagecolorallocatealpha($t_img_o, 255, 255, 255, 127);
					imagefilledrectangle($t_img_o, 0, 0, $t_x_o/2, $t_y_o/2, $transparent);
				}
			}
			imagecopyresampled($img_o, $img_i, 0, 0, $x_o/2, $y_o/2, $n_w, $n_h, $w_o, $h_o);
			if ($thumb) {
				imagecopyresampled($t_img_o, $img_i, 0, 0, $t_x_o/2, $t_y_o/2, $t_w, $t_h, $t_w_o, $t_h_o);
				$t_file = pathinfo($file, PATHINFO_DIRNAME).DS.pathinfo($file, PATHINFO_FILENAME).'_t.'.strtolower(pathinfo($file, PATHINFO_EXTENSION));
			}
			switch($mime) {
				case 'image/jpeg':
					imagejpeg($img_o,$file,100);
					if ($thumb) { imagejpeg($t_img_o,$t_file,90); }
				break;
				case 'image/png':
					imagepng($img_o,$file);
					if ($thumb) { imagepng($t_img_o,$t_file); }
				break;
			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function resize2($original_filename, $max_w, $max_h, $thumb = false, $thumb_w = null, $thumb_h = null) {
		try {
			$gcd = $this -> gcd($max_w, $max_h);
			$gcd_w = $max_w / $gcd;
			$gcd_h = $max_h / $gcd;
			$extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
			$thumb_filename = pathinfo($original_filename, PATHINFO_DIRNAME).DS.pathinfo($original_filename, PATHINFO_FILENAME).'_t.'.$extension;
			list($w_i, $h_i) = getimagesize($original_filename);
			$w_o = $w_i;
			$h_o = $gcd_h * $w_o / $gcd_w;
			if ($h_i < $h_o) {
				$h_o = $h_i;
				$w_o = $gcd_w * $h_o / $gcd_h;
			}
			$x_o = $w_i - $w_o;
			$y_o = $h_i - $h_o;
			switch($extension) {
				case 'jpg':
				case 'jpeg': $img_i = @imagecreatefromjpeg($original_filename); break;
				case 'png': $img_i = @imagecreatefrompng($original_filename); break;
			}
			if ($x_o + $w_o > $w_i) {
				$w_o = $w_i - $x_o;
			}
			if ($y_o + $h_o > $h_i) {
				$h_o = $h_i - $y_o;
			}
			if ($w_o > $max_w || $h_o > $max_h) {
				$n_w = $max_w;
				$n_h = $max_h;
			} else {
				$n_w = $w_o;
				$n_h = $h_o;
			}
			$img_o = imagecreatetruecolor($n_w, $n_h);
			$img_o_t = imagecreatetruecolor(320, 180);
			if ($extension == 'png') {
				imagealphablending($img_o, false);
				imagealphablending($img_o_t, false);
				imagesavealpha($img_o, true);
				imagesavealpha($img_o_t, true);
				$transparent = imagecolorallocatealpha($img_o, 255, 255, 255, 127);
				$transparent_t = imagecolorallocatealpha($img_o_t, 255, 255, 255, 127);
				imagefilledrectangle($img_o, 0, 0, $x_o/2, $y_o/2, $transparent);
				imagefilledrectangle($img_o_t, 0, 0, $x_o/2, $y_o/2, $transparent_t);
			}
			imagecopyresampled($img_o, $img_i, 0, 0, $x_o/2, $y_o/2, $n_w, $n_h, $w_o, $h_o);
			imagecopyresampled($img_o_t, $img_i, 0, 0, $x_o/2, $y_o/2, 320, 180, $w_o, $h_o);
			switch($extension) {
				case 'jpg':
				case 'jpeg': imagejpeg($img_o,$original_filename,100); imagejpeg($img_o_t,$thumb_filename,100); break;
				case 'png': imagepng($img_o,$original_filename); imagepng($img_o_t,$thumb_filename); break;
			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

}
?>
