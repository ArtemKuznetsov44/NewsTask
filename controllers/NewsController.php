<?php

namespace app\controllers;

use app\models\Category;
use app\models\CategoryRelation;
use app\models\Comment;
use app\models\LoginForm;
use app\models\News;
use Yii;
use DiDom\Document;
use DiDom;
use DiDom\Exceptions\InvalidSelectorException;
use Exception;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use function PHPUnit\Framework\throwException;


class NewsController extends ParseController
{
    private static string $url = 'https://www.rbc.ru/';

    // Method to add new category and category_relation if parent_cat_id was specified:

    /**
     * @throws Exception
     */
    public function getDomPageStructure(string $url = null) : Document
    {
        $result = is_null($url) ? $this->getStrHTMLByUrlGuzzle(NewsController::$url) : $this->getStrHTMLByUrlGuzzle($url);

        if (!$result['is_ok'])
            throw new Exception($result['data']);

        return new Document( $result['data'] );
    }

    /**
     * @throws Exception
     */
    private function createCategoryOrGetExists(string $category_name, int $parent_cat_id = null)
    {
        // Checking that we don't have the same category:
        if ($category = Category::find()->where(['title' => $category_name])->one())
            // if we have:
            return $category;

        // Create new instance:
        $cat_model = new Category();
        $cat_model->title = $category_name;
        $cat_model->save();

        // If parent_cat_id was specified:
        if (isset($parent_cat_id)) {
            if ($parent_cat = Category::find()->where(['id' => $parent_cat_id])->one()) {
                $relation_model = new CategoryRelation();
                $relation_model->parent_cat_id = $parent_cat->id;
                $relation_model->current_cat_id = $cat_model->id;
                $relation_model->save();
            } else throw new Exception('Parent category id does not exist in category table!');
        }
        return $cat_model;

    }

    /**
     * @throws Exception
     */
    // Method to create News.
    private function createNewsOrGetExists(string $link_url, string $news_title,
                                           string $news_main_content = null,
                                           int    $category_id = null,
                                           string $category_title = null,
                                           bool   $is_active = null,
                                           int    $created_at = null,
                                           int    $updated_at = null): News
    {
        if ($news = News::find()->where(['title' => $news_title])->one()) return $news;

        $model = new News();                // Create new instance:
        $model->link_url = $link_url;       // Configure the url:
        // Configure the content (array):
        $model->title = $news_title;
        $model->main_content = is_null($news_main_content) ? null : $news_main_content;

        // Configure is_active if it is set by user:
        if (isset($is_active)) $model->is_active = $is_active;
        // Configure created_at field if it is set by user:
//        if (isset($created_at)) $model->created_at = $created_at;
        $model->created_at = $created_at ?? time();
        // Configure updated_at field if it is set by user:
//        if (isset($updated_at)) $model->updated_at = $updated_at;
        $model->updated_at = $updated_at ?? $model->created_at;

        // Configure category_id for model if it is set:
        if (isset($category_id) and is_null($category_title)) {
            // Checking that we have such category by category id:
            if (Category::find()->where(['id' => $category_id])->exists()) {
                $model->category_id = $category_id;
            } else {
                throw new Exception('No such category by category_id');
            }
        } else if (isset($category_title) and is_null($category_id)) {
            // Checking that we have such category by category title:
            if (Category::find()->where(['title' => $category_title])->exists()) {
                $model->category_id = Category::find()->select(['id'])->where(['title' => $category_title])->scalar();
            } else {
                $category_model = $this->createCategoryOrGetExists($category_title);
                $model->category_id = $category_model->id;
            }
        } else {
            throw new Exception('Params for category were specified incorrectly!');
        }
        if (!$model->save())
            throw new Exception('Error in new news save operation! May be, some data is not valid!');

        return $model;
    }

    /**
     * @throws InvalidSelectorException
     * @throws Exception
     */
    private function getNewsDataFromPage()
    {
        $documentDOM = $this->getDomPageStructure();

        // Find a tags with specified class or attr:
        $news_container = $documentDOM->first('div.js-news-feed-list');

        $counter = 0;
        foreach ($news_container->find('a.news-feed__item.js-visited.js-news-feed-item.js-yandex-counter') as $news) {
            // Getting the main link:
            $_href = trim($news->attr('href'));
            // Getting the news title:
            $_title = trim($news->find('span.news-feed__item__title')[0]->text());
            // Getting the news category:
            $_category_name = trim(strtok($news->find('span.news-feed__item__date-text')[0]->text(), ','));
            // Getting the news creation and modification time (they are the same in our case):
            $_created_at = trim($news->attr('data-modif'));
            // Convert our time into int value:
            $_prepared_time_int = intval($_created_at);
            // In error case:
            if ($_prepared_time_int == 0) {
                throw new Exception('Error while parsing created_at time.');
            }

            $this->createNewsOrGetExists(
                $link_url = $_href,
                $new_title = $_title,
                $new_main_content = null,
                $category_id = null,
                $category_name = $_category_name,
                $is_active = null,
                $created_at = $_created_at,
                $updated_at = $_created_at,
            );
            $counter++;
        }
    }

    /**
     * @throws InvalidSelectorException
     */
    public function actionIndex(int $category = null): string
    {
        $sort = new Sort([
            'attributes' => [
                'created_at' => [
                    'asc' => ['created_at' => SORT_ASC],
                    'desc' => ['created_at' => SORT_DESC],
                    'default' => SORT_DESC,
                    'label' => 'Created At'
                ]
            ]
        ]);

        $news_count_before = News::find()->where(['is_active' => true])->count();
        $this->getNewsDataFromPage();
        $news_count_after = News::find()->where(['is_active' => true])->count();

        if ($news_count_before < $news_count_after) {
            Yii::$app->session->setFlash('new_data', 'Fresh news were added!');
        }

        if (is_null($category)) {
            $query = News::find()->where(['is_active' => true])->orderBy($sort->orders);
        } else {
            $query = News::find()->where(['is_active' => true, 'category_id' => $category])->orderBy($sort->orders);
        }

        $pages = new Pagination(['totalCount' => $query->count(), 'pageSize' => 3, 'pageSizeParam' => false, 'forcePageParam' => false]);
        $all_news = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('index', ['all_news' => $all_news, 'pages' => $pages, 'sort' => $sort]);
    }

    /**
     * @throws Exception
     */
    public function actionView()
    {
        $news_title = str_replace('_', ' ', Yii::$app->request->get('news_title'));
        $news_obj = News::find()->where(['title' => $news_title])->one();

        if (!$news_obj)
            throw new Exception('No such news for current url address!');

        $prev_comments = Comment::find()->where(['news_id' => $news_obj->id])->orderBy('created_at')->all();

        $model = new Comment();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = time();
            $model->news_id = $news_obj->id;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'New comment was added!');
                return $this->refresh();
            }
            else Yii::$app->session->setFlash('error', 'Error in comment adding!');
        }

        if ($news_obj->link_url != "") {
            $documentDOM = $this->getDomPageStructure($news_obj->link_url);
            $main_news_container = $documentDOM->first('div.article__text.article__text_free');

            $main_news_container = strip_tags($main_news_container, '<img><video>');
            $news_obj->main_content = str_replace(array("\r\n", "\r", "\n"), '', strip_tags($main_news_container));
            $news_obj->save();

            return $this->render('view', ['current_news' => $news_obj, 'context' => $main_news_container, 'model' => $model, 'prev_comments' => $prev_comments]);
        }

        return $this->render('view', ['current_news' => $news_obj, 'context' => $news_obj->main_content, 'model'=> $model, 'prev_comments' => $prev_comments]);
    }

    public function actionCreate() {

        $model = new News();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = time();
            $model->updated_at = time();
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'News was created successfully!');
                return $this->refresh();
            }
            else
                Yii::$app->session->setFlash('error', 'Error in news creation process!');
        }

        return $this->render('create_update', ['model' => $model]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect('index');
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->goHome();
    }


    /**
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function actionDelete(int $news_id)
    {
        $news = News::find()->where(['id' => $news_id])->one();
        $news->delete();
        return $this->redirect(['news/index']);
    }

    public function actionUpdate(int $news_id) {
        $model = News::find()->where(['id' => $news_id])->one();

        if ($model->load(Yii::$app->request->post())) {
            $model->updated_at = time();
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'News was update successfully!');
                return $this->refresh();
            }
            else
                Yii::$app->session->setFlash('error', 'Error in news update process!');
        }

        return $this->render('create_update', ['model' => $model]);
    }

    public function actionCreateCategory() {
        $model = new Category();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'New category was added!');
                return $this->refresh();
            }
            else Yii::$app->session->setFlash('error', 'Error in category creation process');

        }
        return $this->render('create_update_category', ['model' => $model]);
    }

    public function actionUpdateCategory(int $category) {
        $model = Category::find()->where(['id'=> $category])->one();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'New category was updated!');
                return $this->refresh();
            }
            else Yii::$app->session->setFlash('error', 'Error in category update process');

        }
        return $this->render('create_update_category', ['model' => $model]);

    }

}
