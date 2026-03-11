<?php

namespace Core\Model;


class AppController extends Controller {

    protected $template = 'navigation';
    protected $templateLogin = 'default';
    protected $templateImpression = 'formatImpression';

    public function __construct(){
        $this->viewPath = ROOT.'/ArretsCaisses/Views/';
    }

}

?>