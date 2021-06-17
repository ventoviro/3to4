<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Legacy\Filesystem\Comparator;

interface FileComparatorInterface
{
    public function compare($current, $key, $iterator);
}
