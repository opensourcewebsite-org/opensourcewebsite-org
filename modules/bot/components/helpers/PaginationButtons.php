<?php

namespace app\modules\bot\components\helpers;

use yii\data\Pagination;
use yii\db\ActiveQuery;

/**
 * Class PaginationButtons
 *
 * @package app\modules\bot\helpers
 */
class PaginationButtons
{
    /**
     * @param ActiveQuery $query
     * @param callable    $routeCallback
     * @param callable    $buttonCallback
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array
     */
    public static function buildFromQuery(
        ActiveQuery $query,
        callable $routeCallback,
        callable $buttonCallback,
        int $page = 1,
        int $pageSize = 9
    ) {
        $pagination = self::generatePagination($query->count(), $page, $pageSize);
        $items = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return self::build($pagination, $routeCallback, $items, $buttonCallback);
    }

    /**
     * @param array    $items
     * @param callable $routeCallback
     * @param callable $buttonCallback
     * @param int      $page
     * @param int      $pageSize
     *
     * @return array
     */
    public static function buildFromArray(
        array $items,
        callable $routeCallback,
        callable $buttonCallback,
        int $page = 1,
        int $pageSize = 9
    ) {
        $pagination = self::generatePagination(count($items), $page, $pageSize);
        $items = array_slice(
            $items,
            $pagination->offset,
            $pagination->limit
        );

        return self::build($pagination, $routeCallback, $items, $buttonCallback);
    }

    /**
     * @param array      $items
     * @param callable   $routeCallback
     * @param callable   $buttonCallback
     * @param Pagination $pagination
     *
     * @return array
     */
    public static function build(
        Pagination $pagination,
        callable $routeCallback,
        array $items = [],
        callable $buttonCallback = null
    ) {
        if (is_null($buttonCallback)) {
            $buttons = [];
        } else {
            $buttons = array_map(
                function ($key, $item) use ($buttonCallback) {
                    $buttonCallbackResult = $buttonCallback($key, $item);
                    if (is_array($buttonCallbackResult)
                        && (!isset($buttonCallbackResult[0]) || !is_array($buttonCallbackResult[0]))) {
                        $buttonCallbackResult = [$buttonCallbackResult];
                    }

                    return $buttonCallbackResult;
                },
                array_keys($items),
                $items
            );
            $buttons = array_filter(
                $buttons,
                function ($items) {
                    foreach ($items as $key => $item) {
                        if (!$item) {
                            unset($items[$key]);
                        }
                    }

                    return $items;
                }
            );
        }

        if ($pagination->pageCount > 1) {
            $currentPage = $pagination->page + 1;
            $previousPage = $currentPage - 1 ?: $pagination->pageCount;
            $nextPage = ($currentPage + 1) <= $pagination->pageCount ? $currentPage + 1 : 1;
            $paginationButtons = [];
            $paginationButtons[] = [
                'callback_data' => $routeCallback($previousPage),
                'text'          => '<',
            ];
            $paginationButtons[] = [
                'callback_data' => $routeCallback($currentPage),
                'text'          => $currentPage . '/' . $pagination->pageCount,
            ];
            $paginationButtons[] = [
                'callback_data' => $routeCallback($nextPage),
                'text'          => '>',
            ];
            if ($buttons) {
                $buttons = array_merge($buttons, [$paginationButtons]);
            } else {
                $buttons = $paginationButtons;
            }
        }

        return $buttons;
    }

    /**
     * @param int $itemsCount
     * @param int $page
     * @param int $pageSize
     *
     * @return Pagination
     */
    private static function generatePagination(int $itemsCount, int $page, int $pageSize)
    {
        return new Pagination(
            [
                'totalCount'    => $itemsCount,
                'pageSize'      => $pageSize,
                'params'        => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage'  => true,
            ]
        );
    }
}
