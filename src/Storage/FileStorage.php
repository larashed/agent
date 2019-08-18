<?php

namespace Larashed\Agent\Storage;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\FilesystemManager;

/**
 * Class FileStorage
 *
 * @package Larashed\Agent\Storage
 */
class FileStorage implements StorageInterface
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
     * @param array $record
     */
    public function push(array $record)
    {
        $this->getDisk()->put($this->getFileName(), json_encode($record));
    }

    /**
     * @return int
     */
    public function recordCount()
    {
        return count($this->getDisk()->files($this->directory));
    }

    /**
     * @param int $limit
     *
     * @return Collection
     */
    public function records($limit = 1000)
    {
        $files = $this->getDisk()->files($this->directory);
        $files = collect($files)
            ->sort()
            ->slice(0, $limit);

        $records = $files->map(function ($file) {
            $content = $this->getDisk()->get($file);

            return [
                'file'    => $file,
                'content' => $content
            ];
        });

        return $records->pluck('content', 'file');
    }

    /**
     * @param array $identifiers
     */
    public function remove(array $identifiers)
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
