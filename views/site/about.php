<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="panel panel-default">
        <div class="jumbotron">
        <h1 class="display-4">Version 1.1.0</h1>
            <p class="lead"><i>User Specific Sharing - 2/20/20</i></p>
            <hr>
            <!-- Formatting

                <li class="list-group-item list-group-item-success"><span class='glyphicon glyphicon-plus'></span> Addition</li>
                <li class="list-group-item list-group-item-danger"><span class='glyphicon glyphicon-minus'></span> Removal</li>
                <li class="list-group-item list-group-item-warning"><span class='glyphicon glyphicon-wrench'></span> Fix</li>
                <li class="list-group-item list-group-item-info"><span class='glyphicon glyphicon-question-sign'></span> Info</li>
                <hr>
            -->
            <ul class="list-group">            
                <li class="list-group-item list-group-item-success"><span class='glyphicon glyphicon-plus'></span> User Specific Sharing</li>
                <li class="list-group-item list-group-item-info"><span class='glyphicon glyphicon-question-sign'></span> Files can now be shared with specific users via drop down menu in Library</li>
                <li class="list-group-item list-group-item-info"><span class='glyphicon glyphicon-question-sign'></span> Files shared with the user are indicated by a check mark in '<b>Shared File</b>' column</li>
                <li class="list-group-item list-group-item-info"><span class='glyphicon glyphicon-question-sign'></span> Files being shared by the user are indicated by a yellow row</li>                
                <li class="list-group-item list-group-item-warning"><span class='glyphicon glyphicon-wrench'></span> Fixed initial table sorting</li>             
            </ul>
        </div>
    </div>
    <!--Seperator-->
    <div class="panel panel-default">
        <div class="jumbotron">
        <h1 class="display-4">Version 1.0.0</h1>
            <p class="lead"><i>Initial Release - 2/14/20</i></p>
            <hr>
            <!-- Formatting

                <li class="list-group-item list-group-item-success"><span class='glyphicon glyphicon-plus'></span> Addition</li>
                <li class="list-group-item list-group-item-danger"><span class='glyphicon glyphicon-minus'></span> Removal</li>
                <li class="list-group-item list-group-item-warning"><span class='glyphicon glyphicon-wrench'></span> Fix</li>
                <li class="list-group-item list-group-item-info"><span class='glyphicon glyphicon-question-sign'></span> Info</li>
                <hr>
            -->
            <ul class="list-group">            
                <li class="list-group-item list-group-item-success"><span class='glyphicon glyphicon-plus'></span> ShareX 13 compatibility</li>
                <li class="list-group-item list-group-item-info"><span class='glyphicon glyphicon-question-sign'></span> ShareX configuration can be downloaded
                    <a href="<?= Url::to(['user/account']) ?>"><b>here</b></a>.</li>
                <li class="list-group-item list-group-item-success"><span class='glyphicon glyphicon-plus'></span> Tabular / SQL sorting</li>                
                <li class="list-group-item list-group-item-success"><span class='glyphicon glyphicon-plus'></span> MP4 file support</li>
            </ul>
        </div>
    </div>
    

</div>
