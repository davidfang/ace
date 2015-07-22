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
 *         ┃　　　┗━━━┓
 *        ┃　　　　　　　┣┓
 *        ┃　　　　　　　┏┛
 *        ┗┓┓┏━┳┓┏┛
 *          ┃┫┫　┃┫┫
 *          ┗┻┛　┗┻┛
 */

namespace common\components;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class MyHelper
{
    /**
     * 打印数组
     * @param $vars
     * @param string $label
     * @param bool $return
     * @return null|string
     */
    public static function dump($vars, $label = '', $return = false)
    {
        if (ini_get('html_errors')) {
            $content = "<pre>\n";
            if ($label != '')
                $content .= "<strong>{$label} :</strong>\n";
            $content .= htmlspecialchars(print_r($vars, true));
            $content .= "\n</pre>\n";
        } else
            $content = $label . " :\n" . print_r($vars, true);
        if ($return)
            return $content;
        echo $content;
        return null;
    }

    /**
     * 获取utf8单个字符字节数
     * @param $str
     * @param int $startpos
     * @return int
     */
    public static function utf8strcount($str, $startpos = 0)
    {
        $c = substr($str, $startpos, 1);
        if (ord($c) < 0x80)
            return 1;
        else {
            if ((ord($c) | 0x1f) == 0xdf)
                return 2;
            else {
                if ((ord($c) | 0xf) == 0xef)
                    return 3;
                else {
                    if ((ord($c) | 0x7) == 0xf7)
                        return 4;
                    else
                        return 0;
                }
            }
        }
    }

    /**
     * 按字符宽度截取utf8字符串，返回 "字符.."
     * @param $in
     * @param $num
     * @param string $endstr
     * @return string
     */
    public static function SubstrUTF8($in, $num, $endstr = "...")
    {
        $pos = 0;
        $strnum = 0;
        $parity = 0;
        $out = "";
        while ($pos < strlen($in)) {
            $count = static::utf8strcount($in, $pos);
            if ($count > 0) {
                if ($count == 1)
                    $parity++;
                else
                    $parity += 2;
                if ($parity / 2 >= $num) {
                    $out .= $endstr;
                    break;
                }
                $c = substr($in, $pos, $count);
                //遇到回车符跳出
                if ($c == "\n" or $c == "\r")
                    $c = " ";
                $out .= $c;
                $pos += $count;
            } else
                break;
        }
        return $out;
    }

    /**
     * 导出csv
     * @param array $recordset
     * @param string $reportname
     * @param array $titlelist
     * @return string
     */
    public static function  csvput(array $recordset, $reportname = "", array $titlelist)
    {
        $keylist = array_keys($titlelist);
        $tmpfield = [];
        foreach ($keylist as $k => $val) {
            $tmpfield[] = iconv("UTF-8", "GB18030", $titlelist[$val]);
        }
        $cpfield = $tmpfield;
        $path = \Yii::getAlias('@backend/web') . '/tmp/' . Date('Ymd') . '/';
        if (!file_exists($path))
            mkdir($path, 0777, true);
        $uploadfile = $path . "report_" . $reportname . "_" . Date('YmdHis') . ".csv";
        $fp = fopen($uploadfile, 'w');
        fputcsv($fp, $cpfield);
        foreach ($recordset as $k => $v) {
            $tmpfield = [];
            for ($ki = 0; $ki < count($keylist); $ki++) {
                $key = $keylist[$ki];
                $tmpfield[] = iconv("UTF-8", "GB18030", $recordset[$k][$key]);
            }
            $cpfield = $tmpfield;
            fputcsv($fp, $cpfield);
        }
        fclose($fp);
        return $uploadfile;
    }

    /**
     * 生成操作按钮
     * @param $url
     * @param string $type  view|update|delete 或者要显示的图标CSS样式
     * @param array $options
     * @return string
     */
    public static function actionbutton($url, $type = 'update', $options = [])
    {
        if ($type == 'view') {
            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ArrayHelper::merge([
                'title'     => Yii::t('yii', 'View'),
                'data-pjax' => '0',
            ], $options));
        }else
        if ($type == 'update') {
            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ArrayHelper::merge([
                'title'     => Yii::t('yii', 'Update'),
                'data-pjax' => '0',
            ], $options));
        }else
        if ($type == 'delete') {
            return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, ArrayHelper::merge([
                'title'        => Yii::t('yii', 'Delete'),
                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'data-method'  => 'post',
                'data-pjax'    => '0',
            ], $options));
        }else{
            return Html::a("<span class='glyphicon $type'></span>", $url, ArrayHelper::merge([
                'title'     => Yii::t('yii', 'Update'),
                'data-pjax' => '0',
            ], $options));
        }
    }
    /**
     * 获得角色权限资源（带child）
     * @param $items  角色权限资源数组
     * @param $father 上一级的名字
     * @return array
     */
    public static function itemTree($items,$father=''){
        //$auth = Yii::$app->authManager;
        //$permissions = $auth->getPermissions();
        $return = array();// clone $permissions;//
        $str='';
        foreach ($items as $k => $v) {
            //$str .= ''.$v->name .'-('.$v->type.')-'.'('.$v->description.')<br>';
            $icon = ($v->type ==1)?'icon-user':'icon-eye-open';
            $add_html = MyHelper::actionbutton(['rbac/create', 'father' => $k], 'icon-plus-sign', ['title' => '添加下级']);
            $remove_child_html = MyHelper::actionbutton(['rbac/remove-child','father' =>$father, 'child' => $k], 'icon-minus-sign', ['title' => '从此级删除此项']);
            $del_html = MyHelper::actionbutton('rbac/delete?id=' . $k, 'delete');
            $update_html = MyHelper::actionbutton('update?id=' . $k, 'update');

            $str .= '<li class="dd-item dd2-item" data-id="'.$v->name.'">
													<div class="dd-handle dd2-handle">
														<i class="normal-icon '.$icon.' red bigger-130"></i>

														<i class="drag-icon icon-move bigger-125"></i>
													</div>
													<div class="dd2-content">'.$v->name.'('.$v->description.')
													<div class="pull-right action-buttons">
																	'.$add_html.$remove_child_html.$update_html.$del_html.'
																</div>
													</div>';

            $children = Yii::$app->authManager->getChildren($k);
            if(empty($children)){
                $children = $children ;
                //$str .= '无子';
            }else{
                $children_array = self::itemTree($children,$v->name);
                $children = $children_array['return'];
                //$str .= '='.$children_array['str'];
                $str .= '<ol class="dd-list">';
                $str .= $children_array['str'];
                $str .= '</ol>';
            }
            $str .= '</li>';
            $permission = [
                'type'=>$v->type,
                'name'=>$v->name,//str_replace('/','\/',$v->name),
                '_name'=>str_replace('/','\/',$v->name),//用于树状搜索时便于jquery搜索
                'description'=>$v->description,
                'ruleName'=>$v->ruleName,
                'data'=>$v->data,
                'createdAt'=>$v->createdAt,
                'updatedAt'=>$v->updatedAt,
                'children'=>$children,
            ];
            $return[$k] = $permission;
        }
        return ['return'=>$return,'str'=>$str];
    }
}