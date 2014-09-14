<?php

namespace liao0007\yii2\cropimage;

use Yii;
use \yii\base\Object;

class CropImage extends Object
{

    //get original image & turn into a resource

    //get required dimensions

    //resize image to required dimensions within aspect ratio

    //check whether width or height are wrong

    //get number of pixels required to pad either width or height

    //halve them (or nearly halve them, with a one pixel offset if the total padding amount is an odd number)

    //add equal amount of whitespace on each half of the image, either vertically or horizontally

    //return the resource

    public $imgurl;
    public $imgsize;
    public $orig_im;

    function crop($w, $h)
    {
        $this->imgsize = @getImageSize($this->imgurl);
        if (!$this->imgsize) {
            return false;
        }
        if (substr(strtolower($this->imgurl), -4) == "jpeg" || substr(strtolower($this->imgurl), -3) == "jpg") {
            $this->orig_im = ImageCreateFromJPEG($this->imgurl);
        } elseif (substr(strtolower($this->imgurl), -3) == "gif") {
            $this->orig_im = ImageCreateFromGif($this->imgurl);
        } elseif (substr(strtolower($this->imgurl), -3) == "png") {
            $this->orig_im = ImageCreateFromPng($this->imgurl);
        }        

        $newim = $this->resizeImage($this->orig_im, $this->imgsize[0], $this->imgsize[1], $w, $h);

        //return Array($newImage, $neww, $newh);
        if ($newim[1] < $w || $newim[2] < $h) {
            //add more width or height

            //create new white image
            $paddedimg = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($paddedimg, 255, 255, 255);
            imagefill($paddedimg, 0, 0, $white);
            //stick the thumb in the middle of the new white image
            $pastex = floor(($w - $newim[1]) / 2);
            $pastey = floor(($h - $newim[2]) / 2);

            imagecopy($paddedimg, $newim[0], $pastex, $pastey, 0, 0, $newim[1], $newim[2]);

            //imagefill($paddedimg, 0, 0, $white);

            return $paddedimg;
            /*
            bool imagecopymerge ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h , int $pct )
            Copy a part of src_im onto dst_im starting at the x,y coordinates src_x , src_y with a width of src_w and a height of src_h . The portion defined will be copied onto the x,y coordinates, dst_x and dst_y .
            */

        } else {
            //pass through
            return $newim[0];
        }

    }

    function resizeImage($image, $iw, $ih, $maxw, $maxh)
    {
        if ($iw > $maxw || $ih > $maxh) {
            if ($iw > $maxw && $ih <= $maxh) { //too wide, height is OK
                $proportion = ($maxw * 100) / $iw;
                $neww = $maxw;
                $newh = ceil(($ih * $proportion) / 100);
            } else if ($iw <= $maxw && $ih > $maxh) { //too high, width is OK
                $proportion = ($maxh * 100) / $ih;
                $newh = $maxh;
                $neww = ceil(($iw * $proportion) / 100);
            } else { //too high and too wide

                if ($iw / $maxw > $ih / $maxh) { //width is the bigger problem
                    $proportion = ($maxw * 100) / $iw;
                    $neww = $maxw;
                    $newh = ceil(($ih * $proportion) / 100);
                } else { //height is the bigger problem
                    $proportion = ($maxh * 100) / $ih;
                    $newh = $maxh;
                    $neww = ceil(($iw * $proportion) / 100);
                }
            }
        } else { //size up if src image smaller than target
            $targetProportion = $maxw/$maxh;
            $currentProportion = $iw/$ih;

            if($currentProportion >= $targetProportion) {
                //拉宽即可
                $neww = $maxw;
                $newh = ceil(($neww * $ih) / $iw);
            } else {
                //拉高即可
                $newh = $maxh;
                $neww = ceil(($newh * $iw) / $ih);
            }
        }

        if (function_exists("imagecreatetruecolor")) { //GD 2.0=good!
            $newImage = imagecreatetruecolor($neww, $newh);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $neww, $newh, $iw, $ih);
        } else { //GD 1.8=only 256 colours
            $newImage = imagecreate($neww, $newh);
            imagecopyresized($newImage, $image, 0, 0, 0, 0, $neww, $newh, $iw, $ih);
        }
        return Array($newImage, $neww, $newh);
    }

}//class

/*
$im = new CropImage("http://mysite.com/myimage.jpg");
imagejpeg($im -> crop(250, 156));
*/



