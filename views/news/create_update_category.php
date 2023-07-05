<?php

use yii\bootstrap5\ActiveForm;
use app\models\Category;
use yii\bootstrap5\Html;

$this->title = 'Create | Update category';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['options'=> ['class' => 'create-update-form']]); ?>
<?= $form->field($model, 'title')->textInput(['class'=>'form-control']); ?>
<?= $form->field($model, 'parent_cat_id')->dropDownList(Category::getListItems(),
    [
        'prompt' => 'Выбирите родительскую категорию',
        'options' => [ $parent_cat_id => ['selected' => true]]
    ]); ?>
<?= Html::submitButton('Save', ['class' => 'btn btn-success']); ?>
<?php ActiveForm::end(); ?>
