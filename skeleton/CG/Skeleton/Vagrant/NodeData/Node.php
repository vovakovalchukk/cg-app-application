<?php
namespace CG\Skeleton\Vagrant\NodeData;

use ArrayObject;

class Node extends ArrayObject
{
    const VM_RAM = 'VM-RAM';
    const VM_IP = 'VM-ip';
    const BOX = 'box';
    const APPLICATIONS = 'application';
    const CHEF_ATTRIBUTES = 'chef_attributes';
    const CHEF_ROLES = 'chef_roles';
    const CHEF_RECIPES = 'chef_recipes';
    const SYNCED_FOLDERS = 'synced_folders';

    public function getVmRam()
    {
        return $this->offsetExists(static::VM_RAM) ? $this->offsetGet(static::VM_RAM) : null;
    }

    public function setVmRam($vmRam)
    {
        $this->offsetSet(static::VM_RAM, $vmRam);
        return $this;
    }

    public function getVmIp()
    {
        return $this->offsetExists(static::VM_IP) ? $this->offsetGet(static::VM_IP) : null;
    }

    public function setVmIp($vmIp)
    {
        $this->offsetSet(static::VM_IP, $vmIp);
        return $this;
    }

    public function getBox()
    {
        return $this->offsetExists(static::BOX) ? $this->offsetGet(static::BOX) : null;
    }

    public function setBox($box)
    {
        $this->offsetSet(static::BOX, $box);
        return $this;
    }

    public function getApplications()
    {
        return $this->offsetExists(static::APPLICATIONS) ? $this->offsetGet(static::APPLICATIONS) : array();
    }

    public function addApplication($application, $value)
    {
        $applications = $this->getApplications();
        if (isset($applications[$application])) {
            $value = array_merge($applications[$application], $value);
        }
        $applications[$application] = $value;
        $this->offsetSet(static::APPLICATIONS, $applications);
        return $this;
    }

    public function getChefAttributes()
    {
        return $this->offsetExists(static::CHEF_ATTRIBUTES) ? $this->offsetGet(static::CHEF_ATTRIBUTES) : array();
    }

    public function addChefAttribute($attribute, $value)
    {
        $chefAttributes = $this->getChefAttributes();
        $chefAttributes[$attribute] = $value;
        $this->offsetSet(static::CHEF_ATTRIBUTES, $chefAttributes);
        return $this;
    }

    public function getChefRoles()
    {
        return $this->offsetExists(static::CHEF_ROLES) ? $this->offsetGet(static::CHEF_ROLES) : array();
    }

    public function addChefRole($chefRole)
    {
        $chefRoles = $this->getChefRoles();
        if (isset(array_flip($chefRoles)[$chefRole])) {
            return;
        }
        $chefRoles[] = $chefRole;
        $this->offsetSet(static::CHEF_ROLES, $chefRoles);
        return $this;
    }

    public function getChefRecipes()
    {
        return $this->offsetExists(static::CHEF_RECIPES) ? $this->offsetGet(static::CHEF_RECIPES) : array();
    }

    public function addChefRecipe($chefRecipe)
    {
        $chefRecipes = $this->getChefRecipes();
        if (isset(array_flip($chefRecipes)[$chefRecipe])) {
            return;
        }
        $chefRecipes[] = $chefRecipe;
        $this->offsetSet(static::CHEF_RECIPES, $chefRecipes);
        return $this;
    }

    public function getSyncedFolders()
    {
        return $this->offsetExists(static::SYNCED_FOLDERS) ? $this->offsetGet(static::SYNCED_FOLDERS) : array();
    }

    public function addSyncedFolder($syncedFolder)
    {
        $syncedFolders = $this->getSyncedFolders();
        if (isset(array_flip($syncedFolders)[$syncedFolder])) {
            return;
        }
        $syncedFolders[] = $syncedFolder;
        $this->offsetSet(static::SYNCED_FOLDERS, $syncedFolders);
        return $this;
    }

    public function getArrayCopy()
    {
        $arrayCopy = parent::getArrayCopy();
        if (isset($arrayCopy[static::CHEF_ATTRIBUTES])) {
            $arrayCopy[static::CHEF_ATTRIBUTES] = (object) $arrayCopy[static::CHEF_ATTRIBUTES];
        }
        return $arrayCopy;
    }
}