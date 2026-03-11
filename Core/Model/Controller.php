<?php

namespace Core\Model;


class Controller {

    protected $template;
    protected $viewPath;
    protected $templateLogin;

    public function render($view, $variables=[]){
        ob_start();
        extract($variables);
        $page = explode('.',$view);
        require($this->viewPath .ucfirst($page[0]).'/'.$page[1].'.php');
        $content = ob_get_clean();
        require($this->viewPath.'Template/'.$this->template.'.php');
    }

    public function renderLogin($view, $variables=[]){
        ob_start();
        extract($variables);
        $page = explode('.',$view);
        require($this->viewPath .ucfirst($page[0]).'/'.$page[1].'.php');
        $content = ob_get_clean();
        require($this->viewPath.'Template/'.$this->templateLogin.'.php');
    }

    public function renderImpression($view, $variables=[]){
        ob_start();
        extract($variables);
        $page = explode('.',$view);
        require($this->viewPath .ucfirst($page[0]).'/'.$page[1].'.php');
        $content = ob_get_clean();
        require($this->viewPath.'Template/'.$this->templateLogin.'.php');
    }

}

?>