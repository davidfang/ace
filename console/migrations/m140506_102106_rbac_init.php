<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Schema;
use yii\rbac\DbManager;

/**
 * Initializes RBAC tables
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class m140506_102106_rbac_init extends \yii\db\Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

    public function up()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($authManager->ruleTable, [
            'name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'data' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (name)',
        ], $tableOptions);

        $this->createTable($authManager->itemTable, [
            'name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'type' => Schema::TYPE_INTEGER . ' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'rule_name' => Schema::TYPE_STRING . '(64)',
            'data' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (name)',
            'FOREIGN KEY (rule_name) REFERENCES ' . $authManager->ruleTable . ' (name) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

        $this->createTable($authManager->itemChildTable, [
            'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
            'child' => Schema::TYPE_STRING . '(64) NOT NULL',
            'PRIMARY KEY (parent, child)',
            'FOREIGN KEY (parent) REFERENCES ' . $authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
            'FOREIGN KEY (child) REFERENCES ' . $authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);

        $this->createTable($authManager->assignmentTable, [
            'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_id' => Schema::TYPE_STRING . '(64) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (item_name, user_id)',
            'FOREIGN KEY (item_name) REFERENCES ' . $authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);
        $sql = "INSERT INTO {$authManager->itemTable} (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('admininfo/index', 2, '用户列表', NULL, NULL, 1437391893, 1437480807),
('conf', 2, '系统设置', NULL, NULL, 1436867969, 1436867969),
('rbac', 2, '权限管理', NULL, NULL, 1436869060, 1436869060),
('rbac/index', 2, '权限地图', NULL, NULL, 1437530570, 1437530570),
('rbac/permissions', 2, '资源管理', NULL, NULL, 1436865051, 1436865491),
('rbac/roles', 2, '角色管理', NULL, NULL, 1436866405, 1436866405),
('sys/menu', 2, '菜单管理', NULL, NULL, 1436868018, 1436868018),
('user/add', 2, '用户', NULL, NULL, 1436860839, 1436860839),
('user/index', 2, '用户管理(备用)', NULL, NULL, 1437530369, 1437530369),
('管理员', 1, '管理员', NULL, NULL, 1436865294, 1436865294);";
        $this->execute($sql);
        $sql = "INSERT INTO {$authManager->itemChildTable} (`parent`, `child`) VALUES
('user/add', 'admininfo/index'),
('管理员', 'admininfo/index'),
('管理员', 'conf'),
('conf', 'rbac'),
('rbac', 'rbac/index'),
('管理员', 'rbac/index'),
('rbac', 'rbac/permissions'),
('管理员', 'rbac/permissions'),
('rbac', 'rbac/roles'),
('管理员', 'rbac/roles'),
('conf', 'sys/menu'),
('管理员', 'sys/menu'),
('管理员', 'user/add');";
        $this->execute($sql);
        $sql = "INSERT INTO {$authManager->assignmentTable} (`item_name`, `user_id`, `created_at`) VALUES
('管理员', '2', 1436868814);
";
        $this->execute($sql);

    }

    public function down()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }
}
