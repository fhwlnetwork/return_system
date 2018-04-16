<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace center\models;

use Yii;
use yii\base\Object;
use yii\web\Link;
use yii\web\Linkable;
use yii\web\Request;

/**
 * Pagination represents information relevant to pagination of data items.
 *
 * When data needs to be rendered in multiple pages, Pagination can be used to
 * represent information such as [[totalCount|total item count]], [[pageSize|page size]],
 * [[page|current page]], etc. These information can be passed to [[\yii\widgets\LinkPager|pagers]]
 * to render pagination buttons or links.
 *
 * The following example shows how to create a pagination object and feed it
 * to a pager.
 *
 * Controller action:
 *
 * ```php
 * function actionIndex()
 * {
 *     $query = Article::find()->where(['status' => 1]);
 *     $countQuery = clone $query;
 *     $pages = new Pagination(['totalCount' => $countQuery->count()]);
 *     $models = $query->offset($pages->offset)
 *         ->limit($pages->limit)
 *         ->all();
 *
 *     return $this->render('index', [
 *          'models' => $models,
 *          'pages' => $pages,
 *     ]);
 * }
 * ```
 *
 * View:
 *
 * ```php
 * foreach ($models as $model) {
 *     // display $model here
 * }
 *
 * // display pagination
 * echo LinkPager::widget([
 *     'pagination' => $pages,
 * ]);
 * ```
 *
 * @property integer $limit The limit of the data. This may be used to set the LIMIT value for a SQL statement
 * for fetching the current page of data. Note that if the page size is infinite, a value -1 will be returned.
 * This property is read-only.
 * @property array $links The links for navigational purpose. The array keys specify the purpose of the links
 * (e.g. [[LINK_FIRST]]), and the array values are the corresponding URLs. This property is read-only.
 * @property integer $offset The offset of the data. This may be used to set the OFFSET value for a SQL
 * statement for fetching the current page of data. This property is read-only.
 * @property integer $page The zero-based current page number.
 * @property integer $pageCount Number of pages. This property is read-only.
 * @property integer $pageSize The number of items per page. If it is less than 1, it means the page size is
 * infinite, and thus a single page contains all items.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Pagination extends \yii\data\Pagination
{
    const LINK_NEXT = 'next';
    const LINK_PREV = 'prev';
    const LINK_FIRST = 'first';
    const LINK_LAST = 'last';

    /**
     * @var string name of the parameter storing the current page index.
     * @see params
     */
    public $pageParam = 'page';
    /**
     * @var string name of the parameter storing the page size.
     * @see params
     */
    public $pageSizeParam = 'per-page';
    /**
     * @var boolean whether to always have the page parameter in the URL created by [[createUrl()]].
     * If false and [[page]] is 0, the page parameter will not be put in the URL.
     */
    public $forcePageParam = true;
    /**
     * @var string the route of the controller action for displaying the paged contents.
     * If not set, it means using the currently requested route.
     */
    public $route;
    /**
     * @var array parameters (name => value) that should be used to obtain the current page number
     * and to create new pagination URLs. If not set, all parameters from $_GET will be used instead.
     *
     * In order to add hash to all links use `array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by [[pageParam]] is considered to be the current page number (defaults to 0);
     * while the element indexed by [[pageSizeParam]] is treated as the page size (defaults to [[defaultPageSize]]).
     */
    public $params;
    /**
     * @var \yii\web\UrlManager the URL manager used for creating pagination URLs. If not set,
     * the "urlManager" application component will be used.
     */
    public $urlManager;
    /**
     * @var boolean whether to check if [[page]] is within valid range.
     * When this property is true, the value of [[page]] will always be between 0 and ([[pageCount]]-1).
     * Because [[pageCount]] relies on the correct value of [[totalCount]] which may not be available
     * in some cases (e.g. MongoDB), you may want to set this property to be false to disable the page
     * number validation. By doing so, [[page]] will return the value indexed by [[pageParam]] in [[params]].
     */
    public $validatePage = true;
    /**
     * @var integer total number of items.
     */
    public $totalCount = 0;
    /**
     * @var integer the default page size. This property will be returned by [[pageSize]] when page size
     * cannot be determined by [[pageSizeParam]] from [[params]].
     */
    public $defaultPageSize = 20;
    /**
     * @var array|false the page size limits. The first array element stands for the minimal page size, and the second
     * the maximal page size. If this is false, it means [[pageSize]] should always return the value of [[defaultPageSize]].
     */
    public $pageSizeLimit = [1, 200];

}
