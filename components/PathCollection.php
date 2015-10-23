<?php

namespace mobidev\swagger\components;

class PathCollection extends Collection
{
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->items as $item) {
            if (array_key_exists($item->path, $result)) {
                // merge verbs for path
                $result[$item->path]->verbs->addArray($item->verbs);
                continue;
            }
            $result[$item->path] = $item;
        }

        return array_map(function($item) {
                   return $item->toArray();
               }, $result);
    }

}