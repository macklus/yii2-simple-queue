<?php
/* @var $this \yii\web\View */
/* @var $content string */

use Yii;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>
        <div class="wrap">
            <?php
            $baseUrl = '/' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
            NavBar::begin([
                'brandLabel' => 'SimpleQueue',
                'brandUrl' => $baseUrl,
                'options' => [
                    'class' => 'navbar navbar-fixed-top navbar-default',
                ],
            ]);
            $subitems = [];
            foreach ($this->context->queues as $queue) {
                $subitems[] = ['label' => $queue['queue'], 'url' => [$baseUrl . '?q=' . $queue['queue']]];
            }
            $tubes[] = [
                'label' => 'All queues',
                'items' => $subitems,
            ];
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-left'],
                'items' => $tubes,
            ]);

            $menuItems = [
                ['label' => 'Home', 'url' => ['/site/index']],
                ['label' => 'About', 'url' => ['/site/about']],
                ['label' => 'Contact', 'url' => ['/site/contact']],
            ];
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
            ?>

            <div class="container">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
