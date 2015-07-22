<?php
/**
 *      ┏┓　　　┏┓
 *    ┏┛┻━━━┛┻┓
 *    ┃　　　　　　　┃
 *    ┃　　　━　　　┃
 *    ┃　┳┛　┗┳　┃
 *    ┃　　　　　　　┃
 *    ┃　　　┻　　　┃
 *    ┃　　　　　　　┃
 *    ┗━┓　　　┏━┛
 *        ┃　　　┃   神兽保佑
 *        ┃　　　┃   代码无BUG！
 *        ┃　　　┗━━━┓
 *        ┃　　　　　　　┣┓
 *        ┃　　　　　　　┏┛
 *        ┗┓┓┏━┳┓┏┛
 *          ┃┫┫　┃┫┫
 *          ┗┻┛　┗┻┛
 */
use yii\grid\GridView;
use yii\helpers\Html;
use common\components\MyHelper;
use backend\assets\RbacAsset;
RbacAsset::register($this);
$this->params['breadcrumbs'] = [
    '权限管理',
];
?>
    <p>
        <?= Html::a('添加资源/角色', 'create', ['class' => 'btn btn-sm btn btn-success']) ?>
    </p>
<div class="col-sm-6">
    <div class="widget-box">
    <div class="widget-header header-color-blue2">
        <h4 class="lighter smaller">职称角色</h4>
    </div>

    <div class="widget-body">
    <div class="widget-main padding-8">
    <div class="dd" id="nestable">
        <ol class="dd-list">
            <?=$roles_tree['str'] ?>
        </ol>
    </div>
    </div>
    </div>
    </div>
</div>



<div class="col-sm-6">
    <div class="widget-box">
    <div class="widget-header header-color-green2">
        <h4 class="lighter smaller">权限资源</h4>
    </div>

    <div class="widget-body">
    <div class="widget-main padding-8">
    <div class="dd dd-draghandle">
        <ol class="dd-list">
            <?=$permissions_tree['str'] ?>
        </ol>
    </div>
    </div>
    </div>
    </div>
</div>

<script type="text/javascript">
    <?php $this->beginBlock('js_end') ?>
    $(function($){

        $('.dd').nestable();
        //$('.dd').nestable('serialize');//将页面显示的树结构序列化
        //$('.dd').nestable('collapseAll');//折叠所有节点
        //$('.dd').nestable('expandAll');//展开所有节点

        $('.dd-handle a').on('mousedown', function(e){
            alert('aaa');
            //console.log(e.offsetParent().o().parentElement());

            //console.log(e.parentElement().attributes[1]);
            e.stopPropagation();

        });
        $('.dd-handle a').on('mouseup', function(e){
            alert('bbbb');alert(e.id);
            //e.stopPropagation();

        });

        $('[data-rel="tooltip"]').tooltip();

    });
    <?php $this->endBlock(); ?>
</script>
<?php $this->registerJs($this->blocks['js_end'],\yii\web\View::POS_END) ?>