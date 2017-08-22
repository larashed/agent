<?php

namespace Larashed\Agent\Storage;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Collection;

/**
 * Class FileStorage
 *
 * @package Larashed\Agent\Storage
 */
class FileStorage implements AgentStorageInterface
{
    /**
     * @var FilesystemManager
     */
    protected $storage;

    /**
     * @var
     */
    protected $disk;

    /**
     * @var string
     */
    protected $directory;

    /**
     * FileStorage constructor.
     *
     * @param FilesystemManager $storage
     * @param                   $disk
     * @param string            $directory
     */
    public function __construct(FilesystemManager $storage, $disk, $directory = 'larashed/monitoring')
    {
        $this->storage = $storage;
        $this->disk = $disk;
        $this->directory = $directory;
    }

    /**
     * @param $record
     */
    public function addRecord($record)
    {
        $this->getDisk()->put($this->getFileName(), json_encode($record));
    }

    /**
     * @param int $limit
     *
     * @return static
     */
    public function getRecords($limit = 1000)
    {
        $files = collect($this->getDisk()->files($this->directory))->sort()->slice(0, $limit);

        $records = $files->map(function ($file) {
            $content = $this->getDisk()->get($file);

            if (empty($content)) {
                return null;
            }

            return [
                'file'    => $file,
                'content' => $content
            ];
        });

        return $records->pluck('content', 'file');
    }

    /**
     * @param $identifiers
     */
    public function remove($identifiers)
    {
        $identifiers = (array) $identifiers;

        $this->getDisk()->delete($identifiers);
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getDisk()
    {
        return $this->storage->disk($this->disk);
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        $name = str_replace('.', '', microtime(true)) . '.json';

        return rtrim($this->directory, '/') . '/' . $name;
    }
}
