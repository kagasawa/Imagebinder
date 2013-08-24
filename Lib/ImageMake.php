<?php
/* SVN FILE: $Id$ */

/**
 * ImageMake Class
 *
 * @copyright     COPYRIGHTS (C) 2000-2013 Web-Promotions Limited. All Rights Reserved.
 * @link          http://www.web-prom.net/
 * @package       Utility
 * @headUrl       $HeadURL$
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license
 * 
 * @author        Hideyuki Kagasawa. (kagasawa@web-prom.net)
 */
class ImageMake {
    
    /**
     * resize
     * 
     * @param type $imagePath
     * @param type $width
     * @param type $height
     * @param type $quality
     * @return boolean
     */
    public function resize($imagePath, $width=false, $height=false, $quality = 80){
        if(file_exists($imagePath) === false){
            return false;
        }

        // $width/$heightが整数かどうかのチェック
        if (!preg_match('/^0$|^-?[1-9][0-9]*$/', $width) ) {
            $width = false;
        }
        if (!preg_match('/^0$|^-?[1-9][0-9]*$/', $height) ) {
            $height = false;
        }
            
        $imagetype = exif_imagetype($imagePath);
        if ( $imagetype === false ) {
            return false;
        }
        
        // 画像読み込み
        $image = false;

        switch ($imagetype) {
            case IMAGETYPE_GIF:
                $image = ImageCreateFromGIF($imagePath);
                break;
            case IMAGETYPE_JPEG:
                $image = ImageCreateFromJPEG($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = ImageCreateFromPNG($imagePath);
                break;
            default :
                return false;
        }
        
        // 画像の縦横サイズを取得
        $sizeWidth = ImageSX($image);
        $sizeHeight = ImageSY($image);
        $positionX = 0;
        $positionY = 0;
        
        if ( $width != false && $height == false ) {
            // 幅のみ指定されて画像サイズと同じであればリサイズの必要は無い
            if ( $sizeWidth == $width ) {
                ImageDestroy($image);
                return true;
            }
            
            // リサイズ後のサイズを計算
            $mag = $width / $sizeWidth;
            $reSizeWidth = $width;
            $reSizeHeight = round($sizeHeight * $mag);
            
            // サイズ変更後の画像データを生成
            $outImage = ImageCreateTrueColor($reSizeWidth, $reSizeHeight);

        } else if ( $width == false && $height != false ) {
            // 高さのみ指定されて画像サイズと同じであればリサイズの必要は無い
            if ( $sizeHeight == $height ) {
                ImageDestroy($image);
                return true;
            }
            
            // リサイズ後のサイズを計算
            $mag = $height / $sizeHeight;
            $reSizeHeight = $height;
            $reSizeWidth = round($sizeWidth * $mag);
            
            // サイズ変更後の画像データを生成
            $outImage = ImageCreateTrueColor($reSizeWidth, $reSizeHeight);

        } else if ( $width != false && $height != false ) {

            // 幅と高さが同じサイズであればリサイズの必要はない
            if ( $sizeHeight == $height && $sizeWidth == $width ) {
                ImageDestroy($image);
                return true;
            }
            
            // 求める画像サイズとの比を求める
            $widthGap = $sizeWidth / $width;
            $heightGap = $sizeHeight / $height;


            // 横より縦の比率が大きい場合は、求める画像サイズより縦長
            // => 縦の上下をカット
            if ($widthGap < $heightGap) {
                
                $mag = $width / $sizeWidth;
                $reSizeWidth = $width;
                $reSizeHeight = round($sizeHeight * $mag);
                
                $positionY = ceil((($heightGap - $widthGap) * $height) / 2);
                
            // 縦より横の比率が大きい場合は、求める画像サイズより横長
            // => 横の左右をカット
            } else if ($heightGap < $widthGap) {
                
                $mag = $height / $sizeHeight;
                $reSizeHeight = $height;
                $reSizeWidth = round($sizeWidth * $mag);
                
                $positionX = ceil((($widthGap - $heightGap) * $width) / 2);
                
            // 縦横比が同じなら、そのまま縮小
            } else {
                if ( $sizeWidth < $sizeHeight ) {
                    $mag = $width / $sizeWidth;
                    $reSizeWidth = $width;
                    $reSizeHeight = round($sizeHeight * $mag);
                } else {
                    $mag = $height / $sizeHeight;
                    $reSizeHeight = $height;
                    $reSizeWidth = round($sizeWidth * $mag);
                }
            }
            
            // サイズ変更後の画像データを生成
            $outImage = ImageCreateTrueColor($width, $height);

        } else {
            // サイズ指定エラー
            ImageDestroy($image);
            throw new NotFoundException();
        }
        
        if(!$outImage){
            // リサイズ後の画像作成失敗
            return false;
        }

        // 画像リサイズ
        $ret = imagecopyresampled($outImage,$image,0,0,$positionX,$positionY,$reSizeWidth,$reSizeHeight,$sizeWidth,$sizeHeight);
        if($ret === false){
            // リサイズ失敗
            return false;
        }

        // 元イメージのメモリを解放
        ImageDestroy($image);

        // 出力イメージを保存してメモリ解放
        ImageJPEG($outImage,$imagePath, $quality);
        ImageDestroy($outImage);

        return true;
    }
    
}
