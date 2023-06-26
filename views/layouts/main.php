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
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<?php
$menu_items = [];
$categories_for_update = [];
foreach (Category::find()->all() as $category) {
    $menu_items[] = ['label' => $category->title, 'url' => ['news/index', 'category' => $category->id]];
    $categories_for_update[] = ['label' => $category->title, 'url' => ['news/update-category', 'category' => $category->id]];
}
?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            [
                'label' => 'Категория',
                'url' => '#',
                'options' => ['class' => 'nav-item dropdown'],
                'items' => [
                    ...$menu_items
                ]
            ],
            [
                'label' => 'Создать новость',
                'visible' => !Yii::$app->user->isGuest,
                'url' => ['news/create']
            ],
            [
                'label' => 'Создать категорию',
                'visible' => !Yii::$app->user->isGuest,
                'url' => ['news/create-category']
            ],
            [
                'label' => 'Обновить категорию',
                'url' => '#',
                'options' => ['class' => 'nav-item dropdown'],
                'items' => [
                    ...$categories_for_update
                ]
            ],
            [
                'label' => 'Войти',
                'visible' => Yii::$app->user->isGuest,
                'url' => ['news/login'],
            ],
            [
                'label' => 'admin | Выйти',
                'visible' => !Yii::$app->user->isGuest,
                'url' => ['news/logout']
            ],
        ]
    ]);
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light"
        style="position:fixed; left: 0; bottom: 0; max-height: 50px; width: 100% ">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; My Company <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
