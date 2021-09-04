<?php
/**
 * Part of Admin project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Lyrasoft\Luna\Admin\DataMapper;

use Lyrasoft\Luna\Table\LunaTable;
use Windwalker\Legacy\DataMapper\AbstractDatabaseMapperProxy;
use Windwalker\Legacy\Event\Event;

/**
 * The LanguageMapper class.
 *
 * @since  1.0
 */
class LanguageMapper extends AbstractDatabaseMapperProxy
{
    /**
     * Property table.
     *
     * @var  string
     */
    protected static $table = LunaTable::LANGUAGES;

    /**
     * Keep plural fo B/C
     *
     * @var  string
     */
    protected static $alias = 'languages';

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
