<?php

namespace RebelCode\Wp\Admin\Menu;

/**
 * Basic functionality for a sub-menu.
 *
 * @since [*next-version*]
 */
abstract class AbstractSubMenu extends AbstractMenu
{
    /**
     * The parent top-level menu.
     *
     * @since [*next-version*]
     *
     * @var TopLevelMenuInterface
     */
    protected $parentMenu;

    /**
     * Gets the parent top-level menu.
     *
     * @since [*next-version*]
     *
     * @return TopLevelMenuInterface
     */
    protected function _getParentMenu()
    {
        return $this->parentMenu;
    }

    /**
     * Sets the parent top-level menu.
     *
     * @since [*next-version*]
     *
     * @param TopLevelMenuInterface $parentMenu
     *
     * @return $this
     */
    protected function _setParentMenu(TopLevelMenuInterface $parentMenu)
    {
        $this->parentMenu = $parentMenu;

        return $this;
    }
}