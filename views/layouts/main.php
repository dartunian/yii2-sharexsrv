<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use app\assets\AppAsset;
use lo\modules\noty\Wrapper;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Library', 'url' => ['/user/library'], 'visible' => !Yii::$app->user->isGuest, 'active' => in_array(\Yii::$app->controller->action->id,['library','view-file'])],
            ['label' => 'Account', 'url' => ['/user/account'], 'visible' => !Yii::$app->user->isGuest],
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/user/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/user/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?php Pjax::begin(['id' => 'toast_pjax']); ?>
        
        <?=
        Wrapper::widget([
                'layerClass' => 'lo\modules\noty\layers\JqueryToaster',
                'layerOptions'=>[
                    // for every layer (by default)
                    'layerId' => 'noty-layer',
                    'customTitleDelimiter' => '|',
                    'overrideSystemConfirm' => false,
                    'showTitle' => false,
                ],
                 // default options
                'options' => [
                    'settings' => [
                        'toaster' => [
                            'css' => [
                                'position' => 'fixed',
                                'top' => '60px',
                                'right' => '10px',
                                'width' => '300px',
                                'zIndex' => 50000,
                            ],
                        ],
                        'toast' => [
                            'fade' => 'slow',
                            'template' => '<div class="alert alert-%priority% alert-dismissible" role="alert">' .
                                            '<button type="button" class="close" data-dismiss="alert">' .
                                                '<span aria-hidden="true">&times;</span>' .
                                                '<span class="sr-only">Close</span>' .
                                            '</button>' .
                                            '<span class="message"></span>' .
                                        '</div>',
                        ],
                    'timeout' => 3000
                    ],
                ],
        ]);
        ?>

        <?php Pjax::end(); ?>

        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; ADC IT Services, LLC <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
