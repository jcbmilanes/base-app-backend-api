<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
trait HasRolesAndPermissions
{
    /**
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class,'users_roles');
    }

    /**
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'users_permissions');
    }

    public function hasRole(... $roles ) 
    {
        foreach ($roles as $role) 
        {
            if ($this->roles->contains('slug', $role)) 
            {
                return true;
            }
        }
        return false;
    }
                    
    public function hasPermission($permission)
    {
        return (bool) $this->permissions->where('slug', $permission->slug)->count();

        //return (bool) $this->permissions->where('slug', $permission)->count();
    }    

    protected function hasPermissionTo($permission)
    {
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }    

    public function hasPermissionThroughRole($permission)
    {
        foreach ($permission->roles as $role)
        {
            if($this->roles->contains($role)) 
            {
                return true;
            }
        }
        return false;
    }    

    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('slug',$permissions)->get();
    }    

    public function givePermissionsTo(... $permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        
        if($permissions === null) 
        {
            return $this;
        }

        $this->permissions()->saveMany($permissions);
        
        return $this;
    }    

    //remove all attached permissions using the detach() method.
    public function deletePermissions(... $permissions )
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }    

    //removes all permissions for a user and then reassign the permissions provided for a user
    public function refreshPermissions(... $permissions )
    {
        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }
}