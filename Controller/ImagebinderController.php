<?php

App::uses('FilebinderAppController', 'Filebinder.Controller');
App::uses('FilebinderController', 'Filebinder.Controller');
App::uses('ImageMake', 'Imagebinder.Lib');

class ImagebinderController extends FilebinderController {

    public $name = 'Imagebinder';

    /**
     * loader
     * file loader
     *
     * @param string $model
     * @param string $model_id
     * @param string $fieldName
     * @param string $hash
     * @return
     */
    public function loader($model = null, $model_id = null, $fieldName = null, $fileName = null){
        
        $this->layout = false;
        $this->autoRender = false;
        Configure::write('debug', 0);

        if (!$model || $model_id == null || !$fieldName || empty($this->request->query['key']) || empty($this->request->query['expire'])) {
            throw new NotFoundException(__('Invalid access'));
            return;
        }
        $key = $this->request->query['key'];
        $expire = $this->request->query['expire'];

        if ($expire < time()) {
            throw new NotFoundException(__('Invalid access'));
            return;
        }
        
        $secret = $this->Session->read('Filebinder.secret');

        if (Security::hash($model . $model_id . $fieldName . $secret . $expire) !== $key) {
            throw new NotFoundException(__('Invalid access'));
            return;
        }

        $this->loadModel($model);

        if ($model_id == 0) {
            // tmp file
            $tmpPath = CACHE;
            if (!empty($this->{$model}->bindFields)) {
                foreach ($this->{$model}->bindFields as $value) {
                    if ($value['field'] === $fieldName && !empty($value['tmpPath'])) {
                        $tmpPath = $value['tmpPath'];
                    }
                }
            }
            $filePath = $tmpPath . $fileName;
        } else {
            $query = array();
            $query['recursive'] = -1;
            $query['fields'] = array($this->{$model}->primaryKey,
                                     $fieldName);
            $query['conditions'] = array($this->{$model}->primaryKey => $model_id);
            $file = $this->{$model}->find('first', $query);

            if (empty($fileName)) {
                $fileName = $file[$model][$fieldName]['file_name'];
            }
            $fileContentType = $file[$model][$fieldName]['file_content_type'];
            $filePath = $file[$model][$fieldName]['file_path'];
        }

        if (!file_exists($filePath)) {
            throw new NotFoundException(__('Invalid access'));
            return;
        }

        // コンテンツタイプが画像の場合
        if ( !empty($fileContentType) && preg_match('#^image/(gif|jpeg|jpg|png)#is', $fileContentType) ) {

            // namedかqueryパラメータでwidth/heightが指定されていたら確保
            $width = false;
            if ( isset($this->request->query['width']) ) {
                $width = $this->request->query['width'];
            } elseif ( isset($this->request->params['named']['width']) ) {
                $width = $this->request->params['named']['width'];
            }
            $height = false;
            if ( isset($this->request->query['height']) ) {
                $height = $this->request->query['height'];
            } elseif ( isset($this->request->params['named']['height']) ) {
                $height = $this->request->params['named']['height'];
            }
            
            // $width/$heightが整数かどうかのチェック
            if (!preg_match('/^0$|^-?[1-9][0-9]*$/', $width) ) {
                $width = false;
            }
            if (!preg_match('/^0$|^-?[1-9][0-9]*$/', $height) ) {
                $height = false;
            }
            
            if ( $width !== false || $height !== false ) {
                $originalFile = new File($filePath);

                // 画像PATH/幅/画像ファイル名
                $p = null;
                if ( $width !== false ) {
                    $p .= 'w'.$width;
                }
                if ( $height !== false ) {
                    $p .= 'h'.$height;
                }
                $thumbnail = $originalFile->Folder->pwd().DS.$p.DS.$originalFile->name;
                
                // thumbnailが存在しなかったら生成
                if ( !file_exists($thumbnail) ) {

                    // originalファイルからthumbnailファイルをコピー
                    $thumbnailFile = new File($thumbnail, true);
                    $originalFile->copy($thumbnailFile->path);

                    // リサイズ
                    ImageMake::resize($thumbnailFile->path, $width, $height);
                    
                    // filesize関数のキャッシュをクリアする
                    clearstatcache();
                }

                // ファイルPATHをthumbnailのPATHに差し替える
                $filePath = $thumbnail;
            }
        }
        
//        if (strstr(env('HTTP_USER_AGENT'), 'MSIE')) {
//            $fileName = mb_convert_encoding($fileName,  "SJIS", "UTF-8");
//            header('Content-Disposition: inline; filename="'. $fileName .'"');
//        } else {
//            header('Content-Disposition: attachment; filename="'. $fileName .'"');
//        }

        header('Content-Length: '. filesize($filePath));
        if (!empty($fileContentType)) {
            header('Content-Type: ' . $fileContentType);
        } else if (class_exists('FInfo')) {
            $info  =  new FInfo(FILEINFO_MIME_TYPE);
            $fileContentType = $info->file($filePath);
            header('Content-Type: ' . $fileContentType);
        } else if (function_exists('mime_content_type')) {
            $fileContentType = mime_content_type($filePath);
            header('Content-Type: ' . $fileContentType);
        }

        ob_end_clean(); // clean
        readfile($filePath);
    }

}
