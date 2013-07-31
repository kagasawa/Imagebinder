<?php

class ImagemakeComponent extends Component {



    /**
     * 画像をリサイズする.(Xサイズを基準)
     *
     * @access public
     * @author sakuragawa
     * @param
     * @return
     */

    function resize($imagePath, $baseSize, $quality = 75){
        if(file_exists($imagePath) === false){
            return false;
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
        $sizeX = ImageSX($image);
        $sizeY = ImageSY($image);

        // リサイズ後のサイズ
        $reSizeX = 0;
        $reSizeY = 0;

        if($baseSize == $sizeX){
            // 基準サイズ(リサイズ必要なし)
            ImageDestroy($image);
            return true;
        }



        // 元画像と基準サイズとの差
        $diffSizeX = $sizeX - $baseSize;

        //$diffSizeY = $sizeY - IMG_Y;

        // リサイズの倍率
        $mag = 1;

        // リサイズ後のサイズを計算
        $mag = $baseSize / $sizeX;
        $reSizeX = $baseSize;
        $reSizeY = $sizeY * $mag;

        // サイズ変更後の画像データを生成
        $outImage = ImageCreateTrueColor($reSizeX, $reSizeY);
        if(!$outImage){
            // リサイズ後の画像作成失敗
            return false;
        }

        // 画像リサイズ
        $ret = imagecopyresampled($outImage,$image,0,0,0,0,$reSizeX,$reSizeY,$sizeX,$sizeY);
        if($ret === false){
            // リサイズ失敗
            return false;
        }

        ImageDestroy($image);

        // 画像保存
        ImageJPEG($outImage,$imagePath, $quality);
        ImageDestroy($outImage);

        return true;
    }



    /**

     * 画像をリサイズする.(サムネイルサイズ)

     *

     * @access public

     * @author sakuragawa

     * @param

     * @return

     */

    function resize_s($imagePath, $baseSize = 80){

        if(file_exists($imagePath) === false){

            return false;

        }



        // 画像読み込み

        $image = ImageCreateFromJPEG($imagePath);

        if(!$image){

            // 画像の読み込み失敗

            return false;

        }



        // 画像の縦横サイズを取得

        $sizeX = ImageSX($image);

        $sizeY = ImageSY($image);



        // リサイズ後のサイズ

        $reSizeX = 0;

        $reSizeY = 0;



        // リサイズの倍率

        $mag = 1;



        // リサイズ後のサイズ計算

        // 縦横短い方で（同じなら横基準）

        if($sizeX > $sizeY){

            // 縦基準

            $diffSizeY = $sizeY - $baseSize;

            $mag = $baseSize / $sizeY;

            $reSizeY = $baseSize;

            $reSizeX = $sizeX * $mag;



        }else{

            // 横基準

            $diffSizeX = $sizeX - $baseSize;

            $mag = $baseSize / $sizeX;

            $reSizeX = $baseSize;

            $reSizeY = $sizeY * $mag;



        }



        // サイズ変更後の画像データを生成

        $outImage = ImageCreateTrueColor($reSizeX, $reSizeY);

        if(!$outImage){

            // リサイズ後の画像作成失敗

            return false;

        }



        // 画像リサイズ

        $ret = imagecopyresampled($outImage,$image,0,0,0,0,$reSizeX,$reSizeY,$sizeX,$sizeY);



        if($ret === false){

            // リサイズ失敗

            return false;

        }



        ImageDestroy($image);



        // 画像保存

        ImageJPEG($outImage,$imagePath);

        ImageDestroy($outImage);



        return true;

    }





}