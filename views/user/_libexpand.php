<?php
use kartik\widgets\Select2;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
if($model->user == Yii::$app->user->identity->ms_id)
{
$usrs = User::find()->select('id, username')->where(['<>', 'id', Yii::$app->user->identity->id])->asArray()->all();

$array = ArrayHelper::map($usrs, 'id', 'username');

$toSU = Url::toRoute(['file/add-secondary-user']);

$value = $model->auth_id_list;

if($value)
{
	$gets = explode(",", $value);
	
	$row = array();
	
	foreach($gets as $get)
	{
		array_push($row, $get);	
	}
	
}
else
{
	$row = $value;
}	

$script2 = <<< JS
function addUser(id, file)
{
	$.ajax({
		type: 'POST',
		url:'$toSU',
		showNoty: false, // add this for disable notification
		data:{id:id, file:file},
		success:function(data){
			$.pjax.reload({container: '#library_grid-pjax', timeout:5000});		
		},
	});
}
JS;

$this->registerJs($script2, \yii\web\View::POS_READY);

echo '<label class="control-label">Authorized Users</label>';
echo Select2::widget([
	
    'name' => 'auth-users',
	'value' => $row,
    'data' => $array,
    'options' => [
        'placeholder' => 'Select Users ...',
        'multiple' => true,
    ],
    'pluginEvents' => [
		"change" => "function() { var id = $(this).val(); addUser(id, $model->id); }",
	],	
]);
}
else
{
	echo "You may only edit your own files.";
}
?>