<?php
namespace PhpRbac\Manager;

use PhpRbac\NestedSet\FullNestedSet;

use PhpRbac\Rbac;

/**
 * @defgroup phprbac_permission_manager Documentation regarding Permission Manager Functionality
 * @ingroup phprbac
 * @{
 *
 * Documentation regarding Permission Manager functionality.
 *
 * Permission Manager: Contains functionality specific to Permissions
 *
 * @author abiusx
 * @version 1.0
 */
class PermissionManager extends BaseRbacManager
{
    function __construct()
    {
        $this->type = 'permissions';
        $this->nestedSet = new FullNestedSet(
            Rbac::getInstance()->getDatabaseManager()->getTablePrefix() .
        'permissions', 'ID', 'Lft', 'Rght');
    }

    /**
     * {inheritdoc}
     */
    function remove($id, $recursive = false)
    {
        $this->unassignRoles($id);
        return parent::remove($id, $recursive);
    }

    /**
     * Unassignes all roles of this permission, and returns their number
     *
     * @param integer $ID
     *      Permission Id
     * @return integer
     */
    function unassignRoles($ID)
    {
        $databaseManager = Rbac::getInstance()->getDatabaseManager();

        return $databaseManager->request(
            'DELETE FROM ' . $databaseManager->getTablePrefix() .
            'rolepermissions WHERE PermissionID=?'
        , $ID);
    }

    /**
     * Returns all roles assigned to a permission
     *
     * @param mixed $Permission
     *        	Id, Title, Path
     * @param boolean $OnlyIDs
     *        	if true, result will be a 1D array of IDs
     * @return Array 2D or 1D or null
     */
    function roles($Permission, $OnlyIDs = true)
    {
        $databaseManager = Rbac::getInstance()->getDatabaseManager();
        $tablePrefix = $databaseManager->getTablePrefix();

        if (!is_numeric($Permission))
        {
            $Permission = $this->returnId($Permission);
        }

        if ($OnlyIDs)
        {
            $Res = $databaseManager->request(
                "SELECT RoleID AS `ID` FROM {$tablePrefix}rolepermissions "
                . "WHERE PermissionID=? ORDER BY RoleID"
            , $Permission );

            if(is_array($Res))
            {
                $out = [];
                foreach($Res as $R)
                {
                    $out[] = $R['ID'];
                }
                return $out;
            }
            return null;
        }
        return $databaseManager->request(
            "SELECT `TP`.ID, `TP`.Title, `TP`.Description FROM {$tablePrefix}roles AS `TP`
            LEFT JOIN {$tablePrefix}rolepermissions AS `TR` ON (`TR`.RoleID=`TP`.ID)
            WHERE PermissionID=? ORDER BY TP.ID"
        , $Permission);
    }
}