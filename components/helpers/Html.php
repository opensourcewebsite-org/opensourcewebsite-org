<?php

namespace app\components\helpers;

use Yii;

class Html extends \yii\helpers\Html
{
    // https://fontawesome.com/v5/changelog/latest
    public static array $icons = [
        'add' => [
            'class' => 'fa fa-plus',
        ],
        'edit' => [
            'class' => 'fas fa-edit',
        ],
        'private' => [
            'class' => 'far fa-eye-slash',
            'title' => 'Private',
        ],
        'trash' => [
            'class' => 'far fa-trash-alt',
        ],
        'eye' => [
            'class' => 'fa fa-eye',
            'title' => 'View',
        ],
        'warning' => [
            'class' => 'fas fa-exclamation-triangle',
        ],
        'on' => [
            'class' => 'fas fa-toggle-on',
        ],
        'pending' => [
            'class' => 'fas fa-hourglass-half',
        ],
        'off' => [
            'class' => 'fas fa-toggle-off',
        ],
    ];

    // https://getbootstrap.com/docs/4.6/components/badge/#contextual-variations
    public static array $badges = [
        'primary' => [],
        'secondary' => [],
        'success' => [],
        'danger' => [],
        'warning' => [],
        'info' => [],
        'light' => [],
        'dark' => [],
    ];

    /**
     * @param string $name
     * @param string|null $title
     *
     * @return string|boolean
     */
    public static function icon($name, $title = null)
    {
        if (!$title) {
            $title = self::$icons[$name]['title'] ?? null;
        }

        if (isset(self::$icons[$name]) || array_key_exists($name, self::$icons)) {
            return '<i class="' . self::$icons[$name]['class'] . '"' . ($title ? ' title="' . Yii::t('app', $title) . '"' : '') . '></i>';
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return string|boolean
     */
    public static function badge($name, $text)
    {
        if (isset(self::$badges[$name]) || array_key_exists($name, self::$badges)) {
            return self::tag('span', Yii::t('app', $text), ['class' => 'badge badge-' . $name]);
        }

        return false;
    }
}
