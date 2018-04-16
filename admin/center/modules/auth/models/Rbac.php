<?php
namespace center\modules\auth\models;

use \yii\rbac\DbManager;
use \center\modules\auth\models\AuthItem;

class Rbac extends DbManager
{

    /**
     * Update permission and roles
     */
    public function updateItem($name, $item)
    {
        parent::updateItem($name, $item);
        return true;
    }

    /**
     * Delete permission or roles.
     * $name 主键名称
     */
    public function deleteItem($id, $params)
    {
        if (isset($id) && !empty($id)) {
            $AuthItem = AuthItem::findOne($id);

            $rbac = new self();
            $rbac->db->createCommand()->delete($this->itemTable, ['id' => $id])->execute();

            if ($params === 'permission') {
                $rbac->db->createCommand()->delete($this->itemChildTable, ['child' => $AuthItem->name])->execute();
            } else if ($params === 'roles') {
                $rbac->db->createCommand()->delete($this->itemChildTable, ['parent' => $AuthItem->name])->execute();
                $rbac->db->createCommand()->delete($this->assignmentTable, ['item_name' => $AuthItem->name])->execute();
            }
        }

        return true;
    }
}