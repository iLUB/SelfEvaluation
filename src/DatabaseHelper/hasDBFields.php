<?php

namespace ilub\plugin\SelfEvaluation\DatabaseHelper;

interface hasDBFields extends \Serializable
{
    public function getArrayForDb(): array;
}