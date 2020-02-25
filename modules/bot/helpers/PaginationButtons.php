<?php

namespace app\modules\bot\helpers;

use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use yii\base\InvalidParamException;
use yii\data\Pagination;

/**
 * Class PaginationButtons
 *
 * @package app\modules\bot\helpers
 */
class PaginationButtons
{
    /**
     * @param $route string
     * @param $pagination Pagination
     *
     * @return InlineKeyboardMarkup
     */
    public static function build($route, $pagination)
    {
        $buttons = [];

        if ($pagination->pageCount > 1) {
            $currentPage = $pagination->page + 1;
            $previousPage = $currentPage - 1 ?: $pagination->pageCount;
            $nextPage = ($currentPage + 1) <= $pagination->pageCount ? $currentPage + 1 : 1;

            $buttons[] = ['callback_data' => $route . $previousPage, 'text' => '<'];
            $buttons[] = ['callback_data' => $route . $currentPage, 'text' => $currentPage . '/' . $pagination->pageCount];
            $buttons[] = ['callback_data' => $route . $nextPage, 'text' => '>'];

            Yii::warning($buttons);
        }

        return $buttons;
    }
}
