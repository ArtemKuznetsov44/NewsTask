<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Main News Page';
$this->registerCssFile('@web/css/news_index.css');
?>

<?php if (Yii::$app->session->hasFlash('new_data')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo Yii::$app->session->getFlash('new_data'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

<?php endif; ?>

<div class='news-container-main'>
    <div class="news-container">
        <?php echo $sort->link('created_at') ?>
        <?php foreach ($all_news as $news): ?>
            <div class="one-news">
                <div class="one-news-info">
                    <h3> <?php echo $news->title; ?></h3>
                    <h5> <?php echo $news->category->title . ' | ' . Yii::$app->formatter->asDateTime($news->created_at, 'php: H:i d.m.y'); ?></h5>
                </div>
                <div class="one-news-link">
                    <?php echo Html::a('Visit page', Url::to(['/news/view', 'news_title' => str_replace(' ', '_', $news->title)]), ['style' => 'text-decoration: none;', 'main' => $news->link_url]); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?= LinkPager::widget([
        'pagination' => $pages
    ]); ?>
</div>



