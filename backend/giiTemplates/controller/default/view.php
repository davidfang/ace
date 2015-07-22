<?php
/**
 * This is the template for generating an action view file.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */
/* @var $action string the action ID */

echo "<?php\n";
?>
/* @var $this yii\web\View */
$this->title = <?= $generator->getControllerID() . '/' . $action ?>'页面标题';
//$this->registerCssFile('本页调用的css文件');
//$this->registerJsFile('本页调用的js文件');
<?= "?>" ?>

视图具体内容
