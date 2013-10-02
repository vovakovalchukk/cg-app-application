<?php
namespace CG\Skeleton\Vagrant;

use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Vagrant\NodeData\Node;

class Config extends ZendConfig
{
    public function getVmRam()
    {
        $this->get(Node::VM_RAM);
    }

    public function setVmRam($vmRam)
    {
        $this->offsetSet(Node::VM_RAM, $vmRam);
        return $this;
    }

    public function getVmIp()
    {
        $this->get(Node::VM_IP);
    }

    public function setVmIp($vmIp)
    {
        $this->offsetSet(Node::VM_IP, $vmIp);
        return $this;
    }

    public function getBox()
    {
        $this->get(Node::BOX);
    }

    public function setBox($box)
    {
        $this->offsetSet(Node::BOX, $box);
        return $this;
    }

    public function getChefAttributes()
    {
        $this->get(Node::CHEF_ATTRIBUTES, array());
    }

    public function setChefAttributes(array $chefAttributes)
    {
        $this->offsetSet(Node::CHEF_ATTRIBUTES, $chefAttributes);
        return $this;
    }

    public function getChefRoles()
    {
        $this->get(Node::CHEF_ROLES, array());
    }

    public function setChefRoles(array $chefRoles)
    {
        $this->offsetSet(Node::CHEF_ROLES, $chefRoles);
    }

    public function getChefRecipes()
    {
        $this->get(Node::CHEF_ROLES, array());
    }

    public function setChefRecipes(array $chefRecipes)
    {
        $this->offsetSet(Node::CHEF_ROLES, $chefRecipes);
        return $this;
    }

    public function getSyncedFolders()
    {
        $this->get(Node::SYNCED_FOLDERS, array());
    }

    public function setSyncedFolders(array $syncedFolders)
    {
        $this->offsetSet(Node::SYNCED_FOLDERS, $syncedFolders);
        return $this;
    }
}