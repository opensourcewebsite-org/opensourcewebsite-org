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
     * @param Pagination $pagination
     * @param callable $routeCallback
     * @return array
     */
    public static function build(Pagination $pagination, callable $routeCallback)
    {
        $buttons = [];

        if ($pagination->pageCount > 1) {
            $currentPage = $pagination->page + 1;
            $previousPage = $currentPage - 1 ?: $pagination->pageCount;
            $nextPage = ($currentPage + 1) <= $pagination->pageCount ? $currentPage + 1 : 1;

            $buttons[] = [
                'callback_data' => $routeCallback($previousPage),
                'text' => '<',
            ];
            $buttons[] = [
                'callback_data' => $routeCallback($currentPage),
                'text' => $currentPage . '/' . $pagination->pageCount,
            ];
            $buttons[] = [
                'callback_data' => $routeCallback($nextPage),
                'text' => '>',
            ];

            Yii::warning($buttons);
        }

        return $buttons;
    }
}
