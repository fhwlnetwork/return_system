<?php

namespace center\widgets;

use yii\helpers\Html;

class SuperLinkPager extends \yii\widgets\LinkPager
{
    /**
     * @var string the name of the input checkbox input fields. This will be appended with `[]` to ensure it is an array.
     */
    public $separator = '...';

    public $pageNum;
    public $uri;
    /**
     * @var boolean turns on|off the <a> tag for the active page. Defaults to true (will be a link).
     */
    public $activePageAsLink = true;

    /**
     * @inheritdoc
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        if ($this->firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // page calculations
        list($beginPage, $endPage) = $this->getPageRange();
        $startSeparator = false;
        $endSeparator = false;
        $beginPage++;
        $endPage--;

        if ($beginPage != 1) {
            $startSeparator = true;
            $beginPage++;
        }

        if ($endPage + 1 != $pageCount - 1) {
            $endSeparator = true;
            $endPage--;
        }

        // smallest page
        $buttons[] = $this->renderPageButton(1, 0, null, false, 0 == $currentPage);

        // separator after smallest page
        if ($startSeparator) {
            $buttons[] = $this->renderPageButton($this->separator, null, null, true, false);
        }

        // internal pages
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            if ($i != 0 && $i != $pageCount - 1) {
                $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
            }
        }

        // separator before largest page
        if ($endSeparator) {
            $buttons[] = $this->renderPageButton($this->separator, null, null, true, false);
        }

        // largest page
        $buttons[] = $this->renderPageButton($pageCount, $pageCount - 1, null, false, $pageCount - 1 == $currentPage);

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        if ($this->lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        //$buttons[] = '<input type="text" id="pageNum" style="width:25px"><input type="button" value="GO" onclick="alert(111)">';

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }
}




