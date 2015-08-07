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
namespace backend\controllers;

use app\models\AuthItem;
use app\models\AdminUser;
use app\models\Menu;
use common\components\MyHelper;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Response;
use yii\widgets\ActiveForm;

class RbacController extends BackendController
{
    /**
     * 角色权限管理首页
     */
    public function actionIndex(){
        $auth = Yii::$app->authManager;

        $roles = $auth->getRoles();
        $roles_tree = MyHelper::itemTree($roles);
        $permissions = $auth->getPermissions();
        $permissions_tree = MyHelper::itemTree($permissions);

        return $this->render('index',['roles_tree'=>$roles_tree,'permissions_tree'=>$permissions_tree]);
    }
    /**
     * 添加角色/权限资料
     * @param string $father 上级名称
     * @return string|Response
     */
    public function actionCreate($father='')
    {
        if($father !==''){
            $father_info = AuthItem::findOne($father);
        }else{
            $father_info = null;
        }
        $model = new AuthItem;
        if ($model->load(Yii::$app->request->post())) {

            $posts = Yii::$app->request->post();
            $auth = Yii::$app->authManager;
            if($posts['type']==1) {
                $role = $auth->createRole($model->name);
                $role->description = $model->description;
                $auth->add($role);
                $child_item = $role;
            }else{
                $permission = $auth->createPermission($model->name);
                $permission->description = $model->description;
                $auth->add($permission);
                $child_item = $permission;
            }
            if(!empty($father_info)){//有父级，将建立父子关系
                $father_item = is_null($auth->getRole($father))?$auth->getPermission($father):$auth->getRole($father);
                $auth->addChild($father_item,$child_item);
            }


            Yii::$app->session->setFlash('success');
            return $this->redirect(['rbac/roles']);
        } else {
            return $this->render('create', [
                'model' => $model,
                'father_info' => $father_info
            ]);
        }
    }

    /**
     * 编辑角色/权限资料
     * @param $id
     * @param string $father 上级名称
     * @return string|Response
     */
    public function actionUpdate($id,$father='')
    {
        if($father !==''){
            $father_info = AuthItem::findOne($father);
        }else{
            $father_info = null;
        }
        $model = AuthItem::findOne($id);
        $request = Yii::$app->request;
        $auth = Yii::$app->authManager;
        if ($request->isPost && $model->load($request->post())) {
            $name = $model->oldAttributes['name'];
            $role = $auth->getRole($name);
            $role->name = $model->name;
            $role->description = $model->description;
            if ($auth->update($name, $role)) {
                Yii::$app->session->setFlash('success');
                return $this->redirect(['rbac/roles']);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
                'father_info' => $father_info
            ]);
        }
    }
    /**
     * 删除角色或权限资源
     * @param $id
     * @return Response
     */
    public function actionDelete($id)
    {
        $auth = Yii::$app->authManager;
        $role = (is_null($auth->getRole($id)))?$auth->getPermission($id):$auth->getRole($id);
        if ($auth->remove($role)) {
            Yii::$app->session->setFlash('success');
        } else {
            Yii::$app->session->setFlash('fail', '角色删除失败');
        }
        return $this->redirect(['rbac/roles']);
    }
    /**
     * 删除角色或权限资源的下级角色或资源
     * @param $id
     * @return Response
     */
    public function actionRemoveChild($father,$child)
    {
        $auth = Yii::$app->authManager;
        $father_obj = (is_null($auth->getRole($father)))?$auth->getPermission($father):$auth->getRole($father);
        $child_obj = (is_null($auth->getRole($child)))?$auth->getPermission($child):$auth->getRole($child);
        if ($auth->hasChild($father_obj,$child_obj) and $auth->removeChild($father_obj,$child_obj)) {
            Yii::$app->session->setFlash('success');
        } else {
            Yii::$app->session->setFlash('fail', '删除失败');
        }
        return $this->redirect(['rbac/roles']);
    }
    /**
     * 添加角色或权限资源的下级角色或资源
     * @param $id
     * @return Response
     */
    public function actionAddChild($father,$child)
    {
        $return = ['status'=>false,'msg'=>'添加失败'];
        $auth = Yii::$app->authManager;
        $father_obj = (is_null($auth->getRole($father)))?$auth->getPermission($father):$auth->getRole($father);
        $child_obj = (is_null($auth->getRole($child)))?$auth->getPermission($child):$auth->getRole($child);
        if (!$auth->hasChild($father_obj,$child_obj) and $auth->addChild($father_obj,$child_obj)) {
            //Yii::$app->session->setFlash('success');
            $return['status'] = true;
            $return['msg'] = '添加成功';
        } /*else {
            Yii::$app->session->setFlash('fail', '删除失败');
        }
        return $this->redirect(['rbac/roles']);*/
        Yii::$app->response->format = 'json';
        return $return;
    }









    /**
     * 角色列表
     * @return string
     */
    public function actionRoles()
    {
        $roles = Yii::$app->authManager->getRoles();
        $dataprovider = new ArrayDataProvider([
            'allModels' => $roles,
        ]);
        return $this->render('roles', [
            'dataprovider' => $dataprovider,
        ]);
    }

    /**
     * 资源列表
     * @return string
     */
    public function actionPermissions(){
        $Permissions = Yii::$app->authManager->getPermissions();
        $dataprovider = new ArrayDataProvider([
            'allModels' => $Permissions,
        ]);
        return $this->render('permissions', [
            'dataprovider' => $dataprovider,
        ]);
    }


    /**
     * 添加/修改角色
     * @return string
     */
    public function actionManagerole()
    {
        $request = Yii::$app->request;
        if ($rolename = $request->get('id')) {
            $model = AuthItem::findOne($rolename);
        } else {
            $model = new AuthItem();
        }
        if ($request->isPost) {
            $auth = Yii::$app->authManager;
            $model->load($request->post());
            if ($model->isNewRecord) {
                $role = $auth->createRole($model->name);
                $role->description = $model->description;
                $rzt = $auth->add($role);
            } else {
                $name = $model->oldAttributes['name'];
                $role = $auth->getRole($name);
                $role->name = $model->name;
                $role->description = $model->description;
                $rzt = $auth->update($name, $role);
            }
            if ($rzt) {
                Yii::$app->session->setFlash('success');
                return $this->redirect(['rbac/roles']);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除角色
     * @param $id
     * @return Response
     */
    public function actionDeleterole($id)
    {
        $role = Yii::$app->authManager->getRole($id);
        if (Yii::$app->authManager->remove($role)) {
            Yii::$app->session->setFlash('success');
        } else {
            Yii::$app->session->setFlash('fail', '角色删除失败');
        }
        return $this->redirect(['rbac/roles']);
    }

    /**
     * ajax验证角色是否存在
     * @return array
     */
    public function actionValidateitemname()
    {
        if ($name = $_REQUEST['id']) {
            $model = AuthItem::findOne($name);
        } else {
            $model = new AuthItem();
        }
        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }
    /**
     * 给角色分配菜单
     * @return string
     */
    public function actionAssignrolemenu()
    {
        if (Yii::$app->request->isPost) {
            $posts = Yii::$app->request->post();
            $auth = Yii::$app->authManager;
            $role = is_null($auth->getRole($posts['rolename']))?$auth->getPermission($posts['rolename']):$auth->getRole($posts['rolename']);
            //var_dump($role);exit;
            $thismenu = Menu::findOne($posts['menuid']);
            $route = $thismenu->route;
            $permission = $auth->getPermission($route);
            if ($posts['ck'] == 'true') {
                if ($posts['level'] == 3) {
                    //2级菜单
                    $father = $thismenu->father;
                    //var_dump($father->route);
                    $fpermission = $auth->getPermission($father->route);
                    //var_dump($fpermission);exit;
                    $this->addChild($role, $fpermission);
                    //1级菜单
                    $ffpermission = $auth->getPermission($father->father->route);
                    $this->addChild($role,$ffpermission );
                }
                if ($posts['level'] == 2) {
                    //1级菜单
                    $fpermission = $auth->getPermission($thismenu->father->route);
                    $this->addChild($role, $fpermission);
                    //3级菜单
                    $children = $thismenu->son;
                    foreach ($children as $son) {
                        $spermission = $auth->getPermission($son->route);
                        $this->addChild($role, $spermission);
                    }
                }
                if ($posts['level'] == 1) {
                    //子子孙孙都加权限
                    $sons = $thismenu->son;
                    //var_dump($sons);exit;
                    if(!empty($sons)) {
                        foreach ($sons as $son) {
                            $spermission = $auth->getPermission($son->route);
                            $this->addChild($role, $spermission);
                            if ($son->level == 2) {
                                $gsons = $son->son;
                                foreach ($gsons as $gson) {
                                    $gspermission = $auth->getPermission($gson->route);
                                    $this->addChild($role, $gspermission);
                                }
                            }
                        }
                    }
                }
                //自身加入权限
                $auth->addChild($role, $permission);
            } else {
                if ($posts['level'] == 3 && $posts['cntlv3'] == 0) {
                    $father = $thismenu->father;
                    $auth->removeChild($role, $auth->getPermission($father->route));
                    if ($posts['cntlv3'] == 0) {
                        $auth->removeChild($role, $auth->getPermission($father->route));
                    }
                    if ($posts['cntlv2'] == 0) {
                        $auth->removeChild($role, $auth->getPermission($father->father->route));
                    }
                }
                if ($posts['level'] == 2) {
                    foreach ($thismenu->son as $son) {
                        $auth->removeChild($role, $auth->getPermission($son->route));
                    }
                    if ($posts['cntlv2'] == 0) {
                        $auth->removeChild($role, $auth->getPermission($thismenu->father->route));
                    }
                }
                if ($posts['level'] == 1) {
                    foreach ($thismenu->son as $son) {
                        $auth->removeChild($role, $auth->getPermission($son->route));
                        foreach ($son->son as $gson) {
                            $auth->removeChild($role, $auth->getPermission($gson->route));
                        }
                    }
                }
                //删除自身
                $auth->removeChild($role, $permission);
            }
            return 'ok';
        }
        $list = Menu::find()->where('level=1')->all();
        $rolename = Yii::$app->request->get('rolename');
        $model = AuthItem::findOne($rolename);
        /*echo '<pre>';
        var_dump([
            'list'     => $list,
            'rolename' => $rolename,
            'role'     => Yii::$app->authManager->getRole($rolename),
            'model'    => $model,
        ]);exit;*/
        return $this->render('assignrolemenu', [
            'list'     => $list,
            'rolename' => $rolename,
            'role'     => Yii::$app->authManager->getRole($rolename),
            'model'    => $model,
        ]);
    }
    /**
     * 给角色分配权限资源 或者
     * 给权限资源加子资源
     * @return string
     */
    public function actionAssignauth()
    {
        if (Yii::$app->request->isPost) {
            $posts = Yii::$app->request->post();
            //var_dump($posts);exit;
            $auth = Yii::$app->authManager;
            $father = is_null($auth->getRole($posts['father']))?$auth->getPermission($posts['father']):$auth->getRole($posts['father']);
            $child = is_null($auth->getRole($posts['child']))?$auth->getPermission($posts['child']):$auth->getRole($posts['child']);
            //var_dump($role);exit;

            if ($posts['ck'] == 'true') {
                //自身加入权限
                $auth->addChild($father, $child);
            } else {
                //删除自身
                $auth->removeChild($father, $child);
            }
            return 'ok';
        }
        $roles_tree = $this->getRolesTree();;

        $rolename = Yii::$app->request->get('rolename');
        $model = AuthItem::findOne($rolename);
        /*echo '<pre>';
        var_dump([
            'permissions_tree'     => $permissions_tree,
            'rolename' => $rolename,
            'role'     => Yii::$app->authManager->getRole($rolename),
            'model'    => $model,
        ]);exit;*/
        return $this->render('assignauth', [
            'roles_tree'     => $roles_tree,
            'rolename' => $rolename,
            'role'     => Yii::$app->authManager->getRole($rolename),
            'model'    => $model,
        ]);
        return $this->render('assignpermission', [
            'permissions_tree'     => $permissions_tree,
            'permission' => $permission,
            'role'     => Yii::$app->authManager->getRole($rolename),
            'model'    => $model,
        ]);
    }
    /**
     * 给角色分配权限资源 或者
     * 给权限资源加子资源
     * @return string
     */
    public function actionAssignpermission()
    {
        $auth = Yii::$app->authManager;
        if (Yii::$app->request->isPost) {
            $posts = Yii::$app->request->post();
            //var_dump($posts);exit;

            $father = is_null($auth->getRole($posts['father']))?$auth->getPermission($posts['father']):$auth->getRole($posts['father']);
            $child = $auth->getPermission($posts['child']);
            //var_dump($role);exit;

            if ($posts['ck'] == 'true') {
                //自身加入权限
                $auth->addChild($father, $child);
            } else {
                //删除自身
                $auth->removeChild($father, $child);
            }
            return 'ok';
        }
        $permissions_tree = $this->getPermissionsTree();;

        $permission = Yii::$app->request->get('permission');
        $model = AuthItem::findOne($permission);
        /*echo '<pre>';
        var_dump([
            'permissions_tree'     => $permissions_tree,
            'rolename' => $rolename,
            'role'     => Yii::$app->authManager->getRole($rolename),
            'model'    => $model,
        ]);exit;*/
        return $this->render('assignpermission', [
            'permissions_tree'     => $permissions_tree,
            'permission' => $permission,
            'role'     => is_null($auth->getRole($permission))?$auth->getPermission($permission):$auth->getRole($permission),
            'model'    => $model,
        ]);
    }
    public function actionTest(){
        $auth = Yii::$app->authManager;
        /*$children = $auth->getChildren('管理员');
        //var_dump($children);
        $conf = $auth->getPermission('conf');
        $rbac = $auth->getPermission('rbac');
        $rbac_roles = $auth->getPermission('rbac/roles');
        $rbac_permissions = $auth->getPermission('rbac/permissions');
        //$auth->addChild($rbac,$rbac_permissions);
        //$auth->addChild($rbac,$rbac_roles);
        $auth->addChild($conf,$rbac);

        $conf = $auth->getPermission('conf');
        $sys_menu = $auth->getPermission('sys/menu');
        //$auth->addChild($conf,$sys_menu);

        $children = $auth->getChildren('rbac');
        $children = $auth->getChildren('rbac/roles');
        //var_dump($children);

        $permissions_tree = $this->getPermissionsTree();
        var_dump($permissions_tree);*/


        /*$a = $auth->createRole('a');
        $b = $auth->createRole('b');
        $c = $auth->createRole('c');
        $d = $auth->createRole('d');
        $e = $auth->createRole('e');
        $f = $auth->createRole('f');
        $a->description = 'a';
        $b->description = 'b';
        $c->description = 'c';
        $d->description = 'd';
        $e->description = 'e';
        $f->description = 'f';
        $auth->add($a);
        $auth->add($b);
        $auth->add($c);
        $auth->add($d);
        $auth->add($e);
        $auth->add($f);

        $auth->addChild($a,$b);
        $auth->addChild($b,$c);

        $auth->addChild($d,$e);
        $auth->addChild($e,$f);*/

        //$roles_tree = $this->getRolesTree();
        $roles = $auth->getRoles();
        $roles_tree = MyHelper::itemTree($roles);
        $permissions = $auth->getPermissions();
        $permissions_tree = MyHelper::itemTree($permissions);
        //echo '<pre>';
        //print_r($roles_tree);
        //echo $roles_tree['str'];
        //print_r($permissions);
        //\Yii::$app->response->format = 'json';
        //return $roles_tree;


        //exit;
        return $this->render('test',['roles_tree'=>$roles_tree,'permissions_tree'=>$permissions_tree]);
    }
    /**
     * 获得资源树（树状资源）
     *  @return array
     */
    public function getPermissionsTree()
    {
        $auth = Yii::$app->authManager;
        $permissions = $auth->getPermissions();
        $return = $permissions_tree = $this->getPermissions($permissions);

        foreach($permissions_tree as $fk => $fv){//一级
            if(!empty($fv['children'])){
                foreach($fv['children'] as $ck => $cv){//二级
                    unset($return[$ck]);//删除一级中多余的二级有的权限资源
                    if(!empty($cf['children'])){
                        foreach($cv['children'] as $gk => $gv){//三级
                            unset($return[$gk]);//删除一级中多余的三级有的权限资源
                        }
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 获得权限资源（带child）
     * @param $permissions
     * @return array
     */
    protected function getPermissions($permissions){
        //$auth = Yii::$app->authManager;
        //$permissions = $auth->getPermissions();
        $return = array();// clone $permissions;
        foreach ($permissions as $k => $v) {
            $children = Yii::$app->authManager->getChildren($k);
            if(empty($children)){
                $children = $children ;
            }else{
                $children = $this->getPermissions($children);
            }
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
        return $return;
    }
    /**
     * 获得角色树（树状资源）
     *  @return array
     */
    public function getRolesTree()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $back = array();
        $return = $roles_tree = $this->getRoles($roles);
        foreach($roles_tree as $fk => $fv){//一级
            if(!empty($fv['children'])){
                foreach($fv['children'] as $ck => $cv){//二级
                    unset($return[$ck]);//删除一级中多余的二级有的权限资源
                    if(!empty($cf['children'])){
                        foreach($cv['children'] as $gk => $gv){//三级
                            unset($return[$gk]);//删除一级中多余的三级有的权限资源
                        }
                    }
                }
            }
        }

        return $return;
    }
    /**
     * 获得所有角色（带child）
     * @param $roles
     * @return array
     */
    protected function getRoles($roles){
        //$auth = Yii::$app->authManager;
        //$permissions = $auth->getPermissions();
        $return = array();// clone $permissions;
        foreach ($roles as $k => $v) {
            $children = Yii::$app->authManager->getChildren($k);
            if(empty($children)){
                $children = $children ;
            }else{
                $children = $this->getRoles($children);
            }
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
        return $return;
    }
    /**
     * 给用户分配角色
     * @param $id
     * @return string
     */
    public function actionAssignrole($id)
    {
        $auth = Yii::$app->authManager;
        $model = AdminUser::findOne($id);
        if (Yii::$app->request->isPost) {
            $action = Yii::$app->request->get('action');
            $roles = Yii::$app->request->post('roles');
            if ($action == 'assign') {
                foreach ($roles as $rolename) {
                    $role = $auth->getRole($rolename);
                    $auth->assign($role, $id);
                }
            } else {
                foreach ($roles as $rolename) {
                    $role = $auth->getRole($rolename);
                    $auth->revoke($role, $id);
                }
            }
            //所有角色
            $allroles = ArrayHelper::map($auth->getRoles(), 'name', 'name');
            //所有已选择的角色
            $selectedroles = ArrayHelper::map($auth->getRolesByUser($id), 'name', 'name');
            $res = [
                Html::renderSelectOptions('', array_diff($allroles, $selectedroles)),
                Html::renderSelectOptions('', $selectedroles)
            ];
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $res;
        }
        //获取已有角色
        $assignedroles = ArrayHelper::map($auth->getRolesByUser($id), 'name', 'name');
        //获取所有角色
        $allroles = ArrayHelper::map($auth->getRoles(), 'name', 'name');
        //未被选择的角色
        $roles = array_diff($allroles, $assignedroles);
        return $this->render('assignrole', [
            'roles'         => $roles,
            'assignedroles' => $assignedroles,
            'model'         => $model,
            'id'            => $id
        ]);
    }







    /**
     * 添加权限
     * @param $role
     * @param $item
     */
    protected function addChild($role, $item)
    {
        $auth = Yii::$app->authManager;
        if(!is_null($item)) {
            if (!$auth->hasChild($role, $item)) {
                $auth->addChild($role, $item);
            }
        }
    }
}