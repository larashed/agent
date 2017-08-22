<?php

namespace Larashed\Agent\Storage;

interface AgentStorageInterface
{
    public function addRecord($record);

    public function getRecords($limit);

    public function remove($identifiers);
}
