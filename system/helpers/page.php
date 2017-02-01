<?php
namespace helper;

class Page {

    public function pagination(int $page, int $pages, int $n = 5) : array {
        $range = range(1, $pages);
        $range = array_combine($range, $range);
        $range = array_fill_keys(array_keys($range), false);
        $range[$page] = true;
        if (count($range) <= $n) {
            $show_pages = $range;
        } else {
            if ($page > 3) {
                if ($page + 3 > $pages) {
                    $show_pages = array_slice($range, -5, $n, true);
                } else {
                    $show_pages = array_slice($range, $page-3, $n, true);
                }
            } else {
                $show_pages = array_slice($range, 0, $n, true);
            }
        }
        return [
			'first' => 1,
			'last' => $pages,
            'left' => (($page - 1) > 0) ? ($page - 1) : false,
            'right' => (($page + 1) <= $pages) ? ($page + 1) : false,
            'pages' => $show_pages
        ];
    }

}
