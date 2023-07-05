<?php

namespace app\controllers;

use app\models\Category;
use app\models\Comment;
use app\models\LoginForm;
use app\models\News;
use Yii;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use Exception;
use yii\behaviors\TimestampBehavior;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\JsonResponseFormatter;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class NewsController extends ParseController
{
    private static string $url = 'https://www.rbc.ru/';

    // Method to add new category and category_relation if parent_cat_id was specified:

    /**
     * @throws Exception
     */
    public function getDomPageStructure(string $url = null): Document
    {
        $result = is_null($url) ? $this->getStrHTMLByUrlGuzzle(NewsController::$url) : $this->getStrHTMLByUrlGuzzle($url);

        if (!$result['is_ok'])
            throw new HttpException($result['data'], 400);

        return new Document($result['data']);
    }

    /**
     * @throws InvalidSelectorException
     * @throws Exception
     */
    private function getNewsDataFromPage(): int
    {
        $documentDOM = $this->getDomPageStructure();

        // Find a tags with specified class or attr:
        $news_container = $documentDOM->find('div.js-news-feed-list')[0];

        $query_titles = News::find()->select(['title'])->asArray()->all();
        $query_categories = Category::find()->select(['id', 'title'])->asArray()->all();

        $titles = array_column($query_titles, 'title');

        $categories = [];
        foreach ($query_categories as $category) {
            $categories[$category['title']] = $category['id'];
        }

        $added_count = 0;
        $counter = 0;
        foreach ($news_container->find('a.news-feed__item.js-visited.js-news-feed-item.js-yandex-counter') as $news) {
            // Getting the main link:
            $href = trim($news->attr('href'));
            // Getting the news title:
            $title = trim($news->find('span.news-feed__item__title')[0]->text());
            // Getting the news category:
            $category_title = trim(strtok($news->find('span.news-feed__item__date-text')[0]->text(), ','));
            // Getting the news creation and modification time (they are the same in our case):
            $created_at = intval(trim($news->attr('data-modif')));

            // In error case:
            if ($created_at == 0) {
                throw new Exception('Error while parsing created_at time.');
            }

            if (!in_array($title, $titles)) {
                $model = new News();
                $model->link_url = $href;
                $model->title = $title;
                if (array_key_exists($category_title, $categories))
                    $model->category_id = $categories[$category_title];
                else {
                    $category_model = new Category();
                    $category_model->title = $category_title;
                    if (!$category_model->save())
                        throw new HttpException(400, 'Error in adding category for news');
                    $model->category_id = $category_model->id;
                }
                $model->detachBehavior('timestampBehavior');
                $model->created_at = $created_at;
                $model->updated_at = $created_at;
                if (!$model->save())
                    throw new HttpException(400, 'Error in news saving process');
                $model->attachBehavior('timestampBehavior', TimestampBehavior::class);
                $added_count++;
            }
            $counter++;
        }
        return $added_count;
    }

    /**
     * @throws InvalidSelectorException
     */
    public function actionIndex(int $category_id = null): string
    {
        $sort = new Sort([
            'attributes' => [
                'created_at' => [
                    'desc' => ['created_at' => SORT_DESC],
                    'asc' => ['created_at' => SORT_ASC],
                    'default' => SORT_DESC,
                    'label' => 'Время добавления'
                ],
            ],
            'defaultOrder' => ['created_at' => SORT_DESC]
        ]);

        $result = $this->getNewsDataFromPage();

        if ($result > 0)
            Yii::$app->session->setFlash('new_data', "$result новостей было добавлено!");

        if (is_null($category_id))
            $query = News::find()->where(['is_active' => true])->asArray();
        else
            $query = News::find()->where(['is_active' => true, 'category_id' => $category_id])->asArray();

        $countQuery = clone $query;

        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 3, 'pageSizeParam' => false, 'forcePageParam' => false]);

        $all_news = $query->orderBy($sort->orders)->limit($pages->limit)->with('category')->offset($pages->offset)->asArray()->all();

        return $this->render('index', ['all_news' => $all_news, 'pages' => $pages, 'sort' => $sort]);
    }

    /**
     * @throws Exception
     */
    public function actionView(string $news_title)
    {
        $news_title = str_replace(['__', '_'], ['%', ' '] , $news_title);
        $news_obj = News::find()->where(['title' => $news_title])->limit(1)->with('comments')->one();

        if (!$news_obj)
            throw new NotFoundHttpException('No such page or URL is invalid', 404);

        $model = new Comment();
        if (Yii::$app->request->isAjax and $data = Yii::$app->request->post()) {

                $model->context = $data['text'];
                $model->created_at = time();
                $model->news_id = $news_obj['id'];
                $model->save();
                // TODO: не понятно, почему он все равно идет дальше и доходит до рендеринга, когда уже есть RETURN
                return $this->asJson(['success' => 'comment was added', 'status'=>201]);

        }

        if ($news_obj['link_url'] != "" and $news_obj['main_content'] == "") {
            $documentDOM = $this->getDomPageStructure($news_obj['link_url']);
            $main_news_container =
                $documentDOM->first('div.article__text.article__text_free') ??
                $documentDOM->first('div.l-base__col__main') ??
                $documentDOM->first('div.l-col-center-590.article__content') ??
                $documentDOM->first('div.article__main');


            if (!is_null($main_news_container)) {
                $news_obj['main_content'] = str_replace(array("\r\n", "\r", "\n"), '', strip_tags($main_news_container, '<img><video><h2><h3><p>'));
            } else {
                $news_obj['main_content'] =
                    "К сожалению, контент данной новости не удалось распознать 😔 \n 
                     Его будет куда удобней смотреть в источнике 😉  <br/> </br>
                     <a href=" . $news_obj['link_url'] . " alt='rbc.ru' style='padding: 10px; background-color: #dc930e; text-decoration: none; color: white; border-radius: 5px;'>Перейти на страницу источника </a>";
            }
            $news_obj->save();
        }

        return $this->render('view', ['current_news' => $news_obj, 'context' => $news_obj['main_content'], 'model' => $model]);
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

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function actionDelete(int $news_id): Response
    {
        $news = News::find()->where(['id' => $news_id])->limit(1)->one();
        if (is_null($news))
            throw new HttpException('Error in deleting process!', 400);
        $news->is_active = false;
        $news->save();
        return $this->redirect(['news/index']);
    }

    public function actionCreateUpdate(int $news_id = null)
    {
        // If $news_id is not null -> make update operation:
        if (isset($news_id)) {
            $model = News::find()->where(['id' => $news_id])->limit(1)->one();
        } // In opposite case, create new instance:
        else $model = new News();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Операция выполнена успешно!');
                return $this->refresh();
            }
            Yii::$app->session->setFlash('error', 'Возника ошибка при выполнении операции...');
        }

        return $this->render('create_update', ['model' => $model]);
    }

    public function actionCreateUpdateCategory(int $category_id = null)
    {
        // If $news_id is not null -> make update operation:
        if (isset($category_id)) {
            $model = Category ::find()->where(['id' => $category_id])->limit(1)->one();
        } // In opposite case, create new instance:
        else $model = new Category();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Операция выполнена успешно!');
                return $this->refresh();
            }
            Yii::$app->session->setFlash('error', 'Возника ошибка при выполнении операции...');
        }

        return $this->render('create_update_category', ['model' => $model]);
    }
}
