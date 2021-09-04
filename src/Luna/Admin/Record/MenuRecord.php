<?php
/**
 * Part of Admin project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Lyrasoft\Luna\Admin\Record;

use Lyrasoft\Luna\Admin\Record\Columns\MenuDataInterface;
use Lyrasoft\Luna\Admin\Record\Traits\ContentValidationTrait;
use Lyrasoft\Luna\Table\LunaTable;
use Windwalker\Legacy\Core\Repository\Exception\ValidateFailException;
use Windwalker\Legacy\Event\Event;
use Windwalker\Legacy\Record\NestedRecord;

/**
 * The MenuRecord class.
 *
 * @since  1.0
 */
class MenuRecord extends NestedRecord implements MenuDataInterface
{
    use ContentValidationTrait;

    /**
     * Property table.
     *
     * @var  string
     */
    protected $table = LunaTable::MENUS;

    /**
     * Property keys.
     *
     * @var  string
     */
    protected $keys = 'id';

    /**
     * Property casts.
     *
     * @var  array
     */
    protected $casts = [
        'variables' => 'json',
        'params' => 'json'
    ];

    /**
     * Checks that the object is valid and able to be stored.
     *
     * This method checks that the parent_id is non-zero and exists in the database.
     * Note that the root node (parent_id = 0) cannot be manipulated with this class.
     *
     * @return  static  Method allows chaining.
     *
     * @throws  \Exception
     * @throws  \RuntimeException on database error.
     * @throws  \UnexpectedValueException
     * @since   2.0
     */
    public function validate()
    {
        $this->checkParent();

        try {
            $this->checkAlias('alias', ['parent_id', 'language']);
        } catch (ValidateFailException $e) {
            throw new ValidateFailException(
                __('phoenix.message.same.alias.same.level', $this->alias),
                $e->getCode(),
                $e
            );
        }

        return parent::validate();
    }

    /**
     * onAfterLoad
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterLoad(Event $event)
    {
        // Add your logic
    }

    /**
     * onAfterStore
     *
     * @param Event $event
     *
     * @return  void
     */
    public function onAfterStore(Event $event)
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
}
