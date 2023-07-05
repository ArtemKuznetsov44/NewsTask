<?php

/** @var yii\web\View $this */

/** @var string $content */

use app\assets\AppAsset;
use app\models\Category;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= Html::csrfMetaTags(); ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
$menu_items = [];
//$categories_for_update = [];
foreach (Category::find()->select(['id', 'title'])->asArray()->all() as $category) {
    $menu_items[] = [
        'label' => $category['title'],
        'urlToSee' => ['news/index', 'category_id' => $category['id']],
        'urlToUpdate' => ['news/create-update-category', 'category_id' => $category['id']]
    ];
//    $categories_for_update[] = ['label' => $category['title'], 'url' => ['news/update-category', 'category' => $category['id']]];
}
?>
<header id="header">

</header>

<div id="main">
    <div class="left-menu">
        <div class="title-actions">
            <h2> <?= Html::a('News', ['news/index'], ['style' => 'text-decoration:none; color: white;']); ?> </h2>
            <?php if (Yii::$app->user->isGuest): ?>
                <div style="padding: 5px; border-radius: 5px;  background-color: #dc930e;"><?= Html::a('Войти', ['news/login'], ['class'=> 'authorization-link']); ?> </div>
            <?php else:?>
                <div style="padding: 5px; border-radius: 5px;  background-color: #dc930e;"><?= 'Admin' . ' | ' . Html::a('Выйти', ['news/logout'],
                        ['class' => 'logout-link',
                            'data' => [
                                'confirm' => 'Вы действительно хотите выйти из аккаунта?'
                            ]]); ?> </div>
            <?php endif; ?>
        </div>
        <ul>
            <?php foreach ($menu_items as $item): ?>
                <li>
                    <?= Html::a($item['label'], $item['urlToSee'], ['class' => 'menu-link-button']) ?>
                    <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Html::a('Обновить', $item['urlToUpdate'], ['class' => 'link-button']) ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="left-menu-bottom">
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Html::a('Создать свою категорию', ['news/create-update-category', 'category_id' => null], ['class' => 'category-creation-button']) ?>
                <?php endif; ?>
        </div>
    </div>
    <div class="right-part">
        <div class="breadcrumb">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif; ?>
        </div>
        <?php if (isset($this->blocks['block1'])): ?>
            <?= $this->blocks['block1']; ?>
        <?php endif; ?>
        <div class="main-content">
            <?= $content ?>
        </div>

    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
