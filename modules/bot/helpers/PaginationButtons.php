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
    public static function build($route, $pagination, $maxVisibleButtons = 5)
    {
        if ($maxVisibleButtons < 5) {
            throw new InvalidParamException('maxVisibleButtons can\t be less than 5');
        }

        $buttons = [];

        $page = $pagination->page + 1;

        $start = 1;
        $end = $pagination->pageCount;

        if ($end > $maxVisibleButtons) {
            $position = $page;
            $radius = ceil(($maxVisibleButtons - 2) / 2);
            if ($page > $pagination->pageCount - $radius) {
                $position = $pagination->pageCount - $radius + 1;
            }
            if ($page <= $radius) {
                $position = $radius;
            }
            $start = $position - $radius + 1;
            $end = $position + $radius;

            $buttons[] = ['callback_data' => self::getRoute($route, 1), 'text' => "\xE2\x8F\xAA"];
        }

        for ($pageIndex = $start; $pageIndex < $end; $pageIndex++) {
            $buttons[] = ['callback_data' => self::getRoute($route, $pageIndex), 'text' => (int)$pageIndex];
        }

        if ($pagination->pageCount > $maxVisibleButtons) {
            $buttons[] = ['callback_data' => self::getRoute($route, $pagination->pageCount), 'text' => "\xE2\x8F\xA9"];
        }
        Yii::warning($buttons);

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
