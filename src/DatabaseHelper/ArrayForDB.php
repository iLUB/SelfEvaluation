<?php

namespace ilub\plugin\SelfEvaluation\DatabaseHelper;

trait ArrayForDB
{
    protected function getArrayForDbWithAttributes(): array
    {
        $array = [];
        foreach ($this->getArrayForDb() as $property => $value) {
            $type = $value[0];
            $attributes = ['type' => $type];
            switch ($type) {
                case 'integer':
                    $attributes['length'] = 4;
                    break;
                case 'text':
                    $attributes['length'] = 1024;
                    break;
            }
            if ($property == 'id') {
                $attributes['notnull'] = true;
            }
            $array[$property] = $attributes;
        }
        return $array;
    }

    public function getArrayForDb() : array
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (!in_array($property, $this->getNonDbFields())) {
                $array[$property] = [$this->getType($value), $value];
            }
        }

        return $array;
    }

    protected function getIdForDb() : array
    {
        return ['id' => ['integer', $this->getId()]];
    }

    /**
     * @return array
     */
    protected function getNonDbFields()
    {
        return ['db'];
    }

    protected function setObjectValuesFromRecord(hasDBFields $data, $rec)
    {
        foreach ($data->getArrayForDb() as $k => $v) {
            $this->{$k} = $rec->{$k};
        }
        return $this;
    }

    protected function getType($var) : string
    {
        switch (gettype($var)) {
            case 'string':
            case 'array':
            case 'object':
                return 'text';
            case 'NULL':
            case 'boolean':
                return 'integer';
            default:
                return gettype($var);
        }
    }
}
