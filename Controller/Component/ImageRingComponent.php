<?php
App::uses('RingComponent', 'Filebinder.Controller/Component');

class ImageRingComponent extends RingComponent {

    /**
     * startUp
     *
     * @param $controller
     * @return
     */
    public function startUp(Controller $controller) {
        $controller->helpers[]  =  'Imagebinder.ImageLabel';
        if (!$this->Session->read('Filebinder.secret')) {
            if (Configure::read('Filebinder.secret')) {
                $this->Session->write('Filebinder.secret', Configure::read('Filebinder.secret'));
            } else {
                $this->Session->write('Filebinder.secret', Security::hash(time()));
            }
        }
    }

}