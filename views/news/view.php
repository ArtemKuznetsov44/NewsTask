<?php


use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = $current_news->title;
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/news_view.css');
?>

<div class="main-container">

    <div class="main-container-head">
        <div class="options">
            <?php if(!Yii::$app->user->isGuest): ?>
                <?= Html::a('Delete News', Url::to(['news/delete', 'news_id' => $current_news->id]), ['class' => 'link-button']); ?>
                <?= Html::a('Update News', Url::to(['news/update', 'news_id' => $current_news->id]), ['class' => 'link-button']); ?>
            <?php endif; ?>
        </div>

        <h2> <?php echo $current_news->title;  ?></h2>
    </div>
    <div class="main-container-body">
        <?php echo $context; ?>

        <hr/>

        <?php $form= ActiveForm::begin(); ?>
        <?= $form->field($model, 'context')->textarea(['row'=>'5']);  ?>
        <?php echo Html::submitButton('Send', ['class' => 'btn btn-success']); ?>
        <?php ActiveForm::end();?>
        <br/>
        <div class="prev-comments">
            <?php foreach($prev_comments as $comment): ?>
                <div class="one-comment" >
                    <?php echo $comment->context . ' | ' . Yii::$app->formatter->asDatetime($comment->created_at, 'php: H:i d.m.y'); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>



<?php
$js = <<< JS
    $('.fox-tail').remove();
 JS;
$position = $this::POS_END;
$this->registerJs($js, $position);
?>
