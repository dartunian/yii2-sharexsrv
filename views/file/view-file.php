<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\User;

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => 'File Library', 'url' => ['user/library']];
$this->params['breadcrumbs'][] = $model->public_combo;

$filesize = round($model->size / 1024, 2);

$owner = User::find()
	->where(['ms_id' => $model->user])
	->one();
?>
<div class="user-library">
    <h1><?= Html::encode($this->title) ?></h1>
		<div style='font-size: 12px;'>
			<span class='glyphicon glyphicon-user' style='color: #adadad; padding: 10px; padding-right: 5px; padding-left: 0px;'></span> <?= $owner->username ?>
			<span class='glyphicon glyphicon-calendar' style='color: #adadad; padding: 10px; padding-right: 5px;''></span> <?= Yii::$app->formatter->asDate($model->time, 'php:j F Y h:i A') ?>
			<span class='glyphicon glyphicon-eye-open' style='color: #adadad; padding: 10px; padding-right: 5px;''></span> <?= $model->views ?>
			<span class='glyphicon glyphicon-download-alt' style='color: #adadad; padding: 10px; padding-right: 5px;''></span> <?= $model->downloads ?>			
		</div>
	
		<div class='panel panel-default'>
			<div class='panel-heading'>
				<span class='label label-default'>.<?= $model->type ?></span>
				<span class='label label-default'><?= $filesize ?> KB</span>
				<a class='btn btn-default btn-xs pull-right' href=' <?= Url::toRoute(['file/view-download/', 'id' => $model->public_combo]) ?> '>download</a>
				<a class='btn btn-default btn-xs pull-right' style='margin-right: 3px;' href='<?= Url::toRoute(['file/view-raw/', 'id' => $model->public_combo]) ?> '>raw</a>
			</div>
			<div class='panel-body' style='padding: 0;'>
				<table class='table table-bordered' style='margin: 0; border: 0;'>
					<tbody>
						<?php
							if($model->type == 'txt')
							{
								$fn = fopen( Url::to('@files/' . $model->combo . '.' . $model->type) ,"r");
								$numRow = 0;
								
								while(!feof($fn))  {
									$result = fgets($fn);
									$numRow = $numRow+1;
									echo ("
										<tr>
											<td style='text-align: right; width: 50px; background-color: #f5f5f5; font-size: 13px; border-top: 0; border-left: 0; border-bottom: 0;
											font-family: Courier New; padding: 1px; color: #adadad;
											-webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;'>" . $numRow . ".</td>
											<td style='font-size: 13px; border: 0; font-family: Courier New; padding: 0px; padding-left: 8px;'>" . Yii::$app->formatter->asText($result) . "</td>
										</tr>
										");
								}
							  
								fclose($fn);
							}
							elseif($model->type == 'mp4')
							{
								echo ("
									  <div align='center' class='embed-responsive embed-responsive-16by9'>
										<video class='' controls autoplay>
											<source src='" . Url::toRoute(['file/load-image/', 'id' => $model->public_combo]) . "' type='video/mp4'>
										</video>
									  </div>
									  ");								
							}
							else
							{
								/*
								echo ("<img src='" . Url::to('@web/files/' . $model->combo . '.' . $model->type) . "' class='img-responsive'
									  style='display: block; margin-left: auto; margin-right: auto;'>");
								*/
								
								echo ("<img src='" . Url::toRoute(['file/load-image/', 'id' => $model->public_combo]) . "' class='img-responsive'
									  style='display: block; margin-left: auto; margin-right: auto;'>");								
							}
						?>
					</tbody>
				</table>
			</div>
		</div>
		
</div>
