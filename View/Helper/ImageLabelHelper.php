<?php
App::uses('LabelHelper', 'Filebinder.View/Helper');

class ImageLabelHelper extends LabelHelper {

    /**
     * _makeSrc
     *
     * @param $file
     * @param $options
     * @return
     */
    function _makeSrc($file = null, $options = array()){
        $secret = $this->Session->read('Filebinder.secret');
        $prefix = empty($options['prefix']) ? '' : $options['prefix'];

        /**
         * S3 url
         */
        if (!empty($options['S3']) || !empty($options['s3'])) {
            return $this->_makeS3Url($file, $options);
        }

        $filePath = empty($file['file_path']) ? (empty($file['tmp_bind_path']) ? false : $file['tmp_bind_path']) : preg_replace('#/([^/]+)$#' , '/' . $prefix . '$1' , $file['file_path']);
        if (empty($file) || !$filePath) {
            return false;
        }

        if (preg_match('#' . WWW_ROOT . '#', $filePath)) {
            $src = preg_replace('#' . WWW_ROOT . '#', DS, $filePath);
            return $src;
        }

        if (!empty($file['tmp_bind_path'])) {
            if (empty($file['model_id']) || file_exists($file['tmp_bind_path'])) {
                $file['model_id'] = 0;
                $file['file_name'] = preg_replace('#.+/([^/]+)$#' , '$1' , $file['tmp_bind_path']);
            }
        }

        // over 1.3
        $prefixes = Configure::read('Routing.prefixes');

        if (!$prefixes && Configure::read('Routing.admin')) {
            $prefixes = Configure::read('Routing.admin');
        }

        $url = array();

        foreach ((array)$prefixes as $p) {
            $url[$p] = false;
        }

        $expire = Configure::read('Filebinder.expire') ? strtotime(Configure::read('Filebinder.expire')) : strtotime('+1 minute');

        $key = Security::hash($file['model'] . $file['model_id'] . $file['field_name'] . $secret . $expire);

        $url = array_merge($url, array(
                'plugin' => 'imagebinder',
                'controller' => 'imagebinder',
                'action' => 'loader',
                $file['model'],
                $file['model_id'],
                $file['field_name'],
                $prefix . $file['file_name'],
                '?' => array('key' => $key, 'expire' => $expire),
            ));

        // 幅の指定がされていたらqueryパラメータを追加
        if ( !empty($options['width']) ) {
            $url = Hash::merge($url, array(
                '?' => array(
                    'width' => $options['width']
                )
            ));
        }
        // 高さの指定がされていたらqueryパラメータを追加
        if ( !empty($options['height']) ) {
            $url = Hash::merge($url, array(
                '?' => array(
                    'height' => $options['height']
                )
            ));
        }

        return $url;
    }

}
