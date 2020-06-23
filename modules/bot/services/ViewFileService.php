<?php


namespace app\modules\bot\services;

use app\modules\bot\components\Controller;

/**
 * Class ViewFileService
 *
 * @package app\modules\bot\services
 */
class ViewFileService
{
    /** @var Controller */
    public $controller;

    /**
     * @param string $viewFilename
     *
     * @return bool
     */
    public function isViewFileExists($viewFilename)
    {
        if (!is_readable($this->controller->getViewPath() . '/' . $viewFilename . '.php')) {
            return false;
        }

        return true;
    }

    /**
     * @param string $viewPath
     *
     * @return string
     */
    public function search($viewPath)
    {
        if ($this->isViewFileExists($viewPath)) {
            return $viewPath;
        }
        $tmpUrl = str_replace('_', '-', $viewPath);
        if ($this->isViewFileExists($tmpUrl)) {
            return $tmpUrl;
        }
        $viewPath = str_replace('edit-', 'set-', $viewPath);
        if ($this->isViewFileExists($viewPath)) {
            return $viewPath;
        }
        $tmpUrl = str_replace('_', '-', $viewPath);
        if ($this->isViewFileExists($tmpUrl)) {
            return $tmpUrl;
        }

        return $viewPath;
    }
}
