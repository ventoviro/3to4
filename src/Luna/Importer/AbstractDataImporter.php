<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    LGPL-2.0-or-later
 */

namespace Lyrasoft\Luna\Importer;

use Windwalker\Core\Seed\FakerService;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Legacy\Core\Database\DatabaseAdapter;
use Windwalker\Legacy\Data\Data;
use Windwalker\Legacy\Filesystem\File;
use Windwalker\Legacy\Structure\Structure;
use function Windwalker\tap;

/**
 * The DataImporter class.
 *
 * @since  1.7.12
 */
abstract class AbstractDataImporter implements EventAwareInterface
{
    use EventAwareTrait;

    /**
     * Property db.
     *
     * @var  DatabaseAdapter
     */
    protected $db;

    /**
     * Property fakerService.
     *
     * @var  FakerService
     */
    protected $fakerService;

    /**
     * MenuImporter constructor.
     *
     * @param DatabaseAdapter $db
     * @param FakerService    $fakerService
     */
    public function __construct(DatabaseAdapter $db, FakerService $fakerService)
    {
        $this->db           = $db;
        $this->fakerService = $fakerService;
    }

    /**
     * import
     *
     * @param iterable      $items
     * @param array         $defaultData
     * @param callable|null $dataHandler
     *
     * @return  static
     *
     * @since  1.7.12
     */
    public function import(iterable $items, array $defaultData = [], ?callable $dataHandler = null)
    {
        foreach ($items as $key => $item) {
            $this->importItem($key, $item, $defaultData, $dataHandler);
        }

        return $this;
    }

    /**
     * importItem
     *
     * @param string        $key
     * @param mixed         $item
     * @param array         $defaultData
     *
     * @param callable|null $dataHandler
     *
     * @return  Data
     *
     * @since  1.7.12
     */
    public function importItem(string $key, $item, array $defaultData = [], ?callable $dataHandler = null): Data
    {
        $data = new Data();

        $data->bind($defaultData)
            ->bind($item);

        if ($dataHandler) {
            $dataHandler($data, $key);
        }

        return tap($this->store($data), function ($data) use ($defaultData) {
            $this->emit('onItemImported', compact('data', 'defaultData'));
        });
    }

    /**
     * importFile
     *
     * @param string        $file
     * @param array         $defaultData
     * @param callable|null $dataHandler
     *
     * @return  static
     *
     * @since  1.7.12
     */
    public function importFile(string $file, array $defaultData = [], ?callable $dataHandler = null)
    {
        return $this->import((new Structure($file, File::getExtension($file)))->toArray(), $defaultData, $dataHandler);
    }

    /**
     * store
     *
     * @param Data          $data
     *
     * @return  Data
     *
     * @since  1.7.12
     */
    abstract public function store(Data $data): Data;
}
