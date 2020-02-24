<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Account';
$this->params['breadcrumbs'][] = $this->title;

Pjax::begin(['id' => 'usr_table']);

$loc = Url::toRoute('user/reset-auth-key');
$id = Yii::$app->user->identity->ms_id;
$secret = Yii::$app->user->identity->auth_key;

$script = <<< JS
    $("#refreshbtn").click(function(e) {
        e.preventDefault();
        $.ajax({
            url: '$loc',
            type: 'POST',
             data: { reset: true },
             success: function(data) {
                $.pjax.reload({container: '#usr_table'});
                $.pjax.reload({container: '#toast_pjax'});
             }
         });
    });
  
    document.getElementById("kcopy").onclick = function() {
        copyToClipboard(document.getElementById("kval"));
    }

    document.getElementById("icopy").onclick = function() {
        copyToClipboard(document.getElementById("ival"));
    }
    
    function copyToClipboard(e) {
        var tempItem = document.createElement('input');
        
        tempItem.setAttribute('type','text');
        tempItem.setAttribute('display','none');
        
        let content = e;
        if (e instanceof HTMLElement) {
                content = e.innerHTML;
        }
        
        tempItem.setAttribute('value',content);
        document.body.appendChild(tempItem);
        
        tempItem.select();
        document.execCommand('Copy');
        
        tempItem.parentElement.removeChild(tempItem);
        
        $.toaster({
            message : 'Item has been copied to clipboard.',
            priority : 'info',
            settings :
            {
                'toaster':
                {
                    'css' :
                    {
                        'position' : 'fixed',
                        'top'      : '60px',
                        'right'    : '10px',
                        'width'    : '300px',
                        'zIndex'   : 50000
                    }
                },
                'toast' :
                {
                    'template' :
                        '<div class="alert alert-%priority% alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '<span class="sr-only">Close</span>' +
                            '</button>' +
                            '<span class="message"></span>' +
                        '</div>'
                },
                'timeout' : 3000,
            }
        });
    }
    
    function download(filename, text) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', filename);
      
        element.style.display = 'none';
        document.body.appendChild(element);
      
        element.click();
      
        document.body.removeChild(element);
    }
    
    $(".dlbtn").click(function(e) {
        e.preventDefault();
        var cntent = `
        {
        "Version": "13.0.1",
        "Name": "ADCShareXConfig",
        "DestinationType": "ImageUploader, TextUploader, FileUploader",
        "RequestMethod": "POST",
        "RequestURL": "https://share.adcitservices.com/upload",
        "Body": "MultipartFormData",
        "Arguments": {
        "id": "$id",
        "secret": "$secret"
        },
        "FileFormName": "sharex"
        }
        `;
        download("ADCShareXConfig.sxcu", cntent);
    });
JS;

$this->registerJs($script);

?>


<div class="user-account">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="panel panel-default">
        <div class="panel-heading">Account Information</div>
        <table class="table table-bordered table-hover">
            <tr>
                <td style='max-width: 50px;'><b>Name</b></td>
                <td><?= Yii::$app->user->identity->ms_firstname . " " . Yii::$app->user->identity->ms_lastname ?></td>
            </tr>
            <tr>
                <td style='max-width: 50px;'><b>Email</b></td>
                <td><?= Yii::$app->user->identity->ms_email ?></td>
            </tr>
            <tr>
                <td style='max-width: 50px;'><b>User ID</b></td>
                <td><span id="ival"><?= Yii::$app->user->identity->ms_id ?></span> <a id="icopy" style="cursor: pointer;"><span class="glyphicon glyphicon-paperclip" ></span></a>
                </td>
            </tr>
            <tr>
                <td style='max-width: 50px;'><b>Authorization Key</b></td>
                <td><span id="kval"><?= Yii::$app->user->identity->auth_key ?></span> <a id="kcopy" style="cursor: pointer;"><span class="glyphicon glyphicon-paperclip" ></span></a>
                <button id="refreshbtn" class="btn btn-xs btn-default pull-right"><span class="glyphicon glyphicon-refresh"></span></button>
                </td>
            </tr>
            <tr>
        </table>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">ShareX Configuration Download <a class='btn btn-default btn-xs pull-right dlbtn'><span class='glyphicon glyphicon-download-alt'></span></a></div>
    </div>
</div>

<?php Pjax::end() ?>