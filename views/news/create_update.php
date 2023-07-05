<?php

use app\models\Category;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Create News';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['options' => ['class' => 'create-update-form']]); ?>
<?= $form->field($model, 'title')->textInput(['class' => 'form-control']); ?>
<?= $form->field($model, 'main_content')->textarea(['class' => 'form-control', 'rows' => 10]);?>
<?= $form->field($model, 'category_id')->dropDownList(Category::getListItems()); ?>
<?= $form->field($model, 'is_active')->checkbox(); ?>
<?= Html::submitButton('Save', ['class' => 'btn btn-success']); ?>
<?php ActiveForm::end(); ?>

