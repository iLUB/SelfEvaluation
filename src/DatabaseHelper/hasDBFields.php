<?php

namespace ilub\plugin\SelfEvaluation\DatabaseHelper;

interface hasDBFields
{
    public function getArrayForDb(): array;
}