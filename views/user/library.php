<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\dialog\DialogAsset;
use kartik\date\DatePicker;
use kartik\editable\Editable;
use kartik\widgets\SwitchInput;
use yii\imagine\Image;

DialogAsset::register($this);

$this->title = 'File Library';
$this->params['breadcrumbs'][] = $this->title;

$url = Url::toRoute(['file/delete-file']);

$url2 = Url::toRoute(['file/toggle-public']);

$url3 = Url::toRoute(['file/reset-file-id']);

$toSU = Url::toRoute(['file/add-secondary-user']);

$script = <<< JS
jQuery(document).ready(function ($) {
    function init(){
		function deleteRow() {
			var keys = $('#library_grid').yiiGridView('getSelectedRows');
			if(keys.length !== 0){
				var len = keys.length
				krajeeDialog.confirm('Are you sure you want to delete these <b>(' + len + ')</b> file(s)?', function(out){
					if(out) {
						//krajeeDialog.alert(keys);
						$.ajax({
							type: 'POST',
							url: '$url',
							showNoty: false, // add this for disable notification
							data: {keys: keys},
							success: function(result){
								$.pjax.reload({container: '#toast_pjax', timeout:5000});
								$.pjax.reload({container: '#library_grid-pjax', timeout:5000});
							}
						});
					}
				});
			}
			else
			{
				krajeeDialog.alert("You must select a file(s).");
			}
		}
		
		function sendToggleRequest(status, id) {
			$.ajax({
				type: 'POST',
				url:'$url2',
				showNoty: false, // add this for disable notification
				data:{status:status, id:id},
				success:function(data){
				}
			});
		}
		
		function sendResetIdRequest(id) {
			$.ajax({
				type: 'POST',
				url:'$url3',
				showNoty: false, // add this for disable notification
				data:{id:id},
				success:function(data){
					$.pjax.reload({container: '#toast_pjax', timeout:5000});
					$.pjax.reload({container: '#library_grid-pjax', timeout:5000});
				},
			});
		}              
		
		$("#dltrowbtn").on('click', function() {
			deleteRow()
		});
		
		$("#rfrshbtn").on('click', function() {
			//$.pjax.reload({container: '#library_grid-pjax', timeout:5000});
			location.reload();
			return false;
		});
		
		$('.bootstrap-switch').on('switchChange.bootstrapSwitch', function (event, state) {
			var id = $(this).parent().attr('name');
			var status = state;
			//alert(id + ' ' + status);
			sendToggleRequest(status, id);
		});
		
		$(".resetbtn").on('click', function() {
			var id = $(this).attr("data-id");
			sendResetIdRequest(id);
		});
    }
	init();
    
    $(document).on('ready pjax:end', function (event) {
        init();
    });
    
});

JS;

$this->registerJs($script, \yii\web\View::POS_READY);

$panelTemplate = ("
<div class='panel-{type}'>
    {panelHeading}
    {panelBefore}
    {items}
    <div class='text-center'>{panelFooter}</div>
</div>
");
?>

<div class="user-library">
    
<h1><?= Html::encode($this->title) ?></h1>
        <?php
            
            if(isset($dataProvider))
            {
                $dataProvider->sort = ['defaultOrder' => ['time' => 'SORT_ASC']];
                
                echo GridView::widget([
                    'id' => 'library_grid',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'pjax' => true,
                    'pjaxSettings' => [
                        //'neverTimeout'=>true,
                        //'beforeGrid'=>'My fancy content before.',
                        'afterGrid' => $this->registerJs($script, \yii\web\View::POS_READY),
                        'clientOptions' => [
                                'showNoty' => false
                        ],
                    ],
                    'bordered' => true,
                    'striped' => false,
                    'hover' => true,
                    'condensed' => true,
                    //'floatHeader'=>true,
                    //'floatHeaderOptions'=>['top'=>'50'],
                    'panel' => [
                        'type' => GridView::TYPE_DEFAULT,
                        'heading' => '<span class="glyphicon glyphicon-book"></span> Library',
                        'before' => '<i>Supported file formats: .txt, .png, .jpeg, .jpg, .gif, .mp4</i>',
                        'after' => false,
                    ],
                    'panelTemplate' => $panelTemplate,
                    'toolbar' =>  [
                        ['content' => 
                            "<div class='btn-group'>" .
                                "<a class='btn btn-default' href='" . Url::toRoute(['user/library']) . "' id='rfrshbtn' data-pjax='0'><span class='glyphicon glyphicon-repeat'></span></a>" .
                                //"<button class='btn btn-default'><span class='glyphicon glyphicon-plus'></span></button>" .
                                "<button class='btn btn-default' id='dltrowbtn'><span class='glyphicon glyphicon-trash'></span></button>" .
                            "</div>"
                        ],
                        '{export}',
                        //'{toggleData}',
                    ],
                    'tableOptions' => ['style' => 'margin:0px;'],
                    'rowOptions' => function ($model, $index, $widget, $grid){
                        if (!empty($model->auth_id_list) && $model->user == Yii::$app->user->identity->ms_id)
                        {
                                return ['class' => GridView::TYPE_WARNING];
                        }
                        },
                    'columns' => [
                        [
                                'class' => 'kartik\grid\ExpandRowColumn',
                                'width' => '50px',
                                'value' => function ($model, $key, $index, $column) {
                                    return GridView::ROW_COLLAPSED;
                                 },
                                'detail' => function ($model, $key, $index, $column) {
                                    return Yii::$app->controller->renderPartial('_libexpand', ['model' => $model]);
                                 },
                                'headerOptions' => ['class' => 'kartik-sheet-style'],
                                'expandOneOnly' => true,
                                'expandIcon' => '',
                                'collapseIcon' => '',
                        ],                      
                        [
                            'format'=>'raw',
                            'value' => function($data){
                                if($data->type == 'txt')
                                {
                                        $thumb = Url::to('@web/assets/siteassets/textimage.png');
                                }
                                elseif($data->type == 'mp4')
                                {
                                        $thumb = Url::to('@web/assets/siteassets/textimage.png');
                                }
                                else
                                {  
                                        $img = Url::to('@files/' . $data->combo . '.' . $data->type);

                                        $thumb = Yii::$app->thumbnail->url($img, ['thumbnail' => ['width' => 64, 'height' => 64]]);
                                }
								                                
                                return ("<img src='" . $thumb . "' style='max-width:30px;' class='img-thumbnail'>");
                            }
                        ],
                        [
                            'class' => 'kartik\grid\EditableColumn',
                            //'filterType' => GridView::FILTER_TYPEAHEAD,
                            'label' => 'File Name',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'attribute' => 'name',
                            'refreshGrid' => true,
                            'editableOptions' => [
                                'options' => ['maxlength' => 30],
                                'formOptions' => [
                                   'action' => Url::toRoute(['file/edit-file-name']),
                                ]
                            ]
                        ],
                        [
                            'label' => 'Public ID',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'attribute' => 'public_combo',
                        ],
                        [
                            'attribute' => 'time',
                            'label' => 'Date',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'format' => ['date', 'php:j F Y h:i A'],
                            'filterType' => GridView::FILTER_DATE,
                            'filterWidgetOptions' => [
                                'pluginOptions' => [
                                    'convertFormat' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'autoWidget' => true,
                                    'autoclose' => true,
                                ]
                            ],
                        ],                      
                        [
                            'label' => 'File Ext.',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'mergeHeader' => true,
                            'format' => 'raw',
                            'value' => function ($model) {
                                return "<span class='label label-default'>." . $model->type . "</span>";
                            }
                        ],
                        [
                            'class' => 'kartik\grid\BooleanColumn',
                            'label' => 'Shared File',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'mergeHeader' => true,
                            'format' => 'raw',
                            'value' => function ($model) {
                                return !empty($model->user !== Yii::$app->user->identity->ms_id);
                                //return in_array(Yii::$app->user->identity->id, array($model->auth_id_list));
                            }
                        ], 
                        [
                            //'class' => 'kartik\grid\EditableColumn',
                            'label' => 'Public',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'mergeHeader' => true,
                            'format' => 'raw',
                            'value' => function ($model) {
                                return SwitchInput::widget([
                                    'name' => $model->id,
                                    'value' => $model->ispublic,
                                    'disabled' => $model->user !== Yii::$app->user->identity->ms_id,
                                    'pluginOptions' => [
                                        'size' => 'mini',
                                        'onColor' => 'success', 
                                        'onText' => 'ON',
                                        'offText' => 'OFF',
                                    ],
                                        //'options' => ['id' => $model->id,],
                                        'containerOptions' => ['style' => 'margin: 0px;', 'name' => $model->id,],
                                ]);
                            }
                        ],
                        [
                            'label' => 'Views',
                            'attribute' => 'views',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'mergeHeader' => true,
                        ],
                        [
                            'label' => 'Downloads',
                            'attribute' => 'downloads',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'mergeHeader' => true,
                        ],						
                        [
                            'class' => '\kartik\grid\ActionColumn',
                            'template' => '{view} {reset}',
                            'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                                            'title' => Yii::t('app', 'view-file'),
                                ]);
                            },
                
                            'update' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                                            'title' => Yii::t('app', 'update-file'),
                                ]);
                            },
                            
                            'delete' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                            'title' => Yii::t('app', 'delete-file'),
                                ]);
                            },
                            
                            'reset' => function ($url, $model) {
                                if($model->user == Yii::$app->user->identity->ms_id)
                                {
                                        return Html::a('<span data-id="' . $model->public_combo . '" class="glyphicon glyphicon-refresh resetbtn" disabled></span>', $url, [
                                                    'title' => Yii::t('app', 'reset-file-id'),
                                        ]);
                                }
                            }                            
                
                          ],
                          'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action === 'view') {
                                $url = Url::toRoute(['file/view-file', 'id' => $model->public_combo]);
                                return $url;
                            }
                
                            if ($action === 'update') {
                                $url = "#";
                                return $url;
                            }
                            if ($action === 'delete') {
                                $url = "#";
                                return $url;
                            }
                            if ($action === 'reset') {
                                $url = "#";
                                return $url;
                            }
                
                          }
                        ],
                        [
                            'class' => '\kartik\grid\CheckboxColumn',
                        ],
                    ],
                ]);
            }
            
        ?>
</div>

