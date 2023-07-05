<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var array $current_news */
/** @var string $context */
/** @var app\models\Comment $model */

$this->title = $current_news['title'];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/news_view.css');
?>


<div class="current-news-container">
    <div class="title-options">
        <h2> <?php echo $current_news->title; ?></h2>
        <div>
            <?php if (!Yii::$app->user->isGuest): ?>
<!--                'id'=> 'delete-button',-->
                <?= Html::a('Удалить новость', ['news/delete', 'news_id' => $current_news['id']],
                    [ 'class' => 'link-button-delete',
                        'data' => [
                                'confirm' => 'Вы действительно хотите удалить данную новость?'
                        ]]); ?>
                <?= Html::a('Обновить новость', ['create-update', 'news_id' => $current_news['id']], ['class' => 'link-button-update']); ?>
            <?php endif; ?>
        </div>

    </div>


    <div class="main-container-body">
        <?php echo $context; ?>
    </div>
    <div class="comments-section">
        <?php $form = ActiveForm::begin(['options' => ['id' => 'comment-add-form']]); ?>
        <?= $form->field($model, 'context')->textarea(['row' => '5', 'style' => 'resize:none;'])->label('Оставить свой комментарий:'); ?>
<!--        --><?php //echo Html::submitButton('Отправить', ['id' => 'submit-button', 'class' => 'btn btn-success']); ?>
        <button type="submit" class="btn btn-success" id='submit-button' style="background-color:#2DD648">Отправить </button>
        <?php ActiveForm::end(); ?>
        <br/>
        <?php \yii\widgets\Pjax::begin(['options' => ['id' => 'pjax-container']]);  ?>
        <div class="prev-comments">
            <?php foreach ($current_news->comments as $comment): ?>
                <div class="one-comment">
                    <?php echo $comment['context'] . ' | ' . Yii::$app->formatter->asDatetime($comment['created_at'], 'php: H:i d.m.y'); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php yii\widgets\Pjax::end(); ?>
    </div>
</div>

<?php
$js = <<< JS
    window.addEventListener('beforeunload', function() {
        this.localStorage.setItem('scrollPosition', document.querySelector(".main-content").scrollTop)
    }); 

    window.addEventListener('load', function(){ 
        let scrollPosition = this.localStorage.getItem('scrollPosition'); 
        if (scrollPosition !== null) {
            this.document.querySelector(".main-content").scrollTo(0, scrollPosition); 
            this.localStorage.removeItem('scrollPosition')
        } 
        
    }); 
    
    $("#submit-button").on('click', function(event) {
        event.preventDefault(); 
        
        $.ajax({
            type: 'post', 
            url:  $('#comment-add-form').attr('action'),
            dataType: 'json', 
            data: {text: $('#comment-add-form').find("#comment-context").val() }, 
            success: function (response) {
                console.log('ok -', response); 
                $.pjax.reload({container: "#pjax-container"});
            }, 
            error: function (response) {
                console.log('err -', response); 
            }
        }); 
    }); 
    
 JS;
$position = $this::POS_END;
$this->registerJs($js, $position);
?>
