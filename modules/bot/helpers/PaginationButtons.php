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

        if ($pagination->pageCount) {
            $currentPage = $pagination->page + 1;
            $previousPage = $currentPage - 1 ?: $pagination->pageCount;
            $nextPage = ($currentPage + 1) <= $pagination->pageCount ? $currentPage + 1 : 1;

            $buttons[] = ['callback_data' => self::getRoute($route, $previousPage), 'text' => '<'];
            $buttons[] = ['callback_data' => self::getRoute($route, $currentPage), 'text' => $currentPage . '/' . $pagination->pageCount];
            $buttons[] = ['callback_data' => self::getRoute($route, $nextPage), 'text' => '>'];

            Yii::warning($buttons);
        }

        return new InlineKeyboardMarkup([$buttons]);
    }

    /**
     * @param $pattern
     * @param $page
     *
     * @return mixed
     */
    protected static function getRoute($pattern, $page)
    {
        return str_replace('<page>', $page, $pattern);
    }
}
