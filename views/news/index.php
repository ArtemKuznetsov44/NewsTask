<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Main News Page';
$this->registerCssFile('@web/css/news_index.css');
?>

<?php $this->beginBlock('block1'); ?>
<?php if (Yii::$app->session->hasFlash('new_data')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo Yii::$app->session->getFlash('new_data'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php $this->endBlock(); ?>

<div class='news-container-main'>
    <div class="sorting"> <?php echo $sort->link('created_at') ?> </div>

    <div class="news-container">
        <?php foreach ($all_news as $news): ?>
            <div class="one-news">
                <div class="one-news-info">
                    <div class="one-news-info-title">
                        <?php echo $news['title']; ?>
                    </div>
                    <div class="one-news-info-category">
                        <?php echo $news['category']['title'] . ' | ' . Yii::$app->formatter->asDateTime($news['created_at'], 'php: H:i d.m.y'); ?>
                    </div>
                </div>

                    <?php echo Html::a('Посмотреть подробней', Url::to(['/news/view', 'news_title' => str_replace([' ', '%'], ['_', '__'], $news['title'])]), ['class' => 'one-news-link']); ?>

            </div>
        <?php endforeach; ?>
    </div>
    <?= LinkPager::widget([
        'pagination' => $pages,
        //Css option for container
//        'options' => ['style' => 'background-color: #202020'],
        'maxButtonCount' => 5,
        'linkOptions' => ['style' => 'text-decoration: none;color:white;']

    ]); ?>
</div>



