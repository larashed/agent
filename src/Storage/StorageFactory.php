<?php

namespace Larashed\Agent\Storage;

class StorageFactory
{
    public static function build($engine)
    {
        $storage = null;

        switch ($engine) {
            case 'file':
                $storage = new FileStorage(
                    app('filesystem'),
                    config('larashed-agent.storage.engines.file.disk'),
                    'larashed/monitoring'
                );
                break;
            case 'database':
                $storage = new DatabaseStorage(
                    app('db'),
                    config('larashed-agent.storage.engines.database.connection'),
                    config('larashed-agent.storage.engines.database.table')
                );
                break;
        }

        return $storage;
    }

    public static function buildFromConfig()
    {
        return static::build(config('larashed-agent.storage.default'));
    }
}
