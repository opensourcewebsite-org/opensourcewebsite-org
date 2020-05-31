<?php

namespace app\modules\bot\components\helpers;

use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class PaginationButtons
 *
 * @package app\modules\bot\helpers
 */
class PaginationButtons
{
    /**
     * @param ActiveQuery $query
     * @param callable $routeCallback
     * @param callable $buttonCallback
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static function buildFromQuery(ActiveQuery $query, callable $routeCallback, callable $buttonCallback, int $page = 1, int $pageSize = 9)
    {
        $pagination = self::generatePagination($query->count(), $page, $pageSize);
        $items = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        return self::build($items, $routeCallback, $buttonCallback, $pagination);
    }

    public static function buildFromArray(array $items, callable $routeCallback, callable $buttonCallback, int $page = 1, int $pageSize = 9)
    {
        $pagination = self::generatePagination(count($items), $page, $pageSize);
        $items = array_slice(
            $items,
            $pagination->offset,
            $pagination->limit
        );
        return self::build($items, $routeCallback, $buttonCallback, $pagination);
    }

    /**
     * @param array $items
     * @param callable $routeCallback
     * @param callable $buttonCallback
     * @param Pagination $pagination
     * @return array
     */
    private static function build(array $items, callable $routeCallback, callable $buttonCallback, Pagination $pagination)
    {
        $buttons = array_map(function ($item) use ($buttonCallback) {
            $buttonCallbackResult = $buttonCallback($item);
            if (is_array($buttonCallbackResult) && isset($buttonCallbackResult[0]) && !is_array($buttonCallbackResult[0]))
            {
                $buttonCallbackResult = [ $buttonCallbackResult ];
            }
            return [$buttonCallbackResult];
        }, $items);

        if ($pagination->pageCount > 1) {
            $currentPage = $pagination->page + 1;
            $previousPage = $currentPage - 1 ?: $pagination->pageCount;
            $nextPage = ($currentPage + 1) <= $pagination->pageCount ? $currentPage + 1 : 1;

            $buttons[] = [
                [
                    'callback_data' => $routeCallback($previousPage),
                    'text' => '<',
                ],
                [
                    'callback_data' => $routeCallback($currentPage),
                    'text' => $currentPage . '/' . $pagination->pageCount,
                ],
                [
                    'callback_data' => $routeCallback($nextPage),
                    'text' => '>',
                ]
            ];
        }

        return $buttons;
    }

    private static function generatePagination(int $itemsCount, int $page, int $pageSize)
    {
        return new Pagination([
            'totalCount' => $itemsCount,
            'pageSize' => $pageSize,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
    }
}
