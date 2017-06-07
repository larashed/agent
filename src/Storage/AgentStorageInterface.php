<?php

namespace Larashed\Agent\Storage;

interface AgentStorageInterface
{
    public function addRecord($record);

    public function getRecords();

    public function remove($identifiers);
}
