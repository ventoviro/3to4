<?php
/**
 * Part of Admin project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Lyrasoft\Luna\Admin\DataMapper;

use Lyrasoft\Luna\Admin\Record\MenuRecord;
use Lyrasoft\Luna\Table\LunaTable;
use Windwalker\Legacy\DataMapper\AbstractDatabaseMapperProxy;
use Windwalker\Legacy\Event\Event;

/**
 * The MenuMapper class.
 *
 * @since  1.0
 */
class MenuMapper extends AbstractDatabaseMapperProxy
{
    /**
     * Property table.
     *
     * @var  string
     */
    protected static $table = LunaTable::MENUS;

    /**
     * Property keys.
     *
     * @var  string
     */
    protected static $keys = 'id';

    /**
     * Property dataClass.
     *
     * @var  string
     */
    protected static $dataClass = MenuRecord::class;

    /**
     * Property alias.
     *
     * @var  string
     */
    protected static $alias = 'menu';

    /**
     * onAfterFind
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterFind(Event $event)
    {
        // Add your logic
    }

    /**
     * onAfterCreate
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterCreate(Event $event)
    {
        // Add your logic
    }

    /**
     * onAfterUpdate
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterUpdate(Event $event)
    {
        // Add your logic
    }

    /**
     * onAfterDelete
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterDelete(Event $event)
    {
        // Add your logic
    }

    /**
     * onAfterFlush
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterFlush(Event $event)
    {
        // Add your logic
    }

    /**
     * onAfterUpdateAll
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterUpdateAll(Event $event)
    {
        // Add your logic
    }
}
