<?php
/**
 * User: fso
 * Date: 12.09.2016
 * Time: 19:14
 */

namespace CDeep\Models;

trait Api
{
    protected $is_self = false;

    public function withRelations($fields)
    {
        $fields = is_array($fields)
            ? $fields
            : explode(',', $fields);

        foreach ($fields as $field) {
            if (method_exists($this, $field)) {
                $this->{$field};
            }
        }
        return $this;
    }

    /**
     * @param int $options
     * @return array
     */
    public function toArray($options = 0) {
        if($this->is_self) {
            $this->makeVisible($this->private);
        } else {
            $this->makeHidden($this->private);
        }
        return parent::toArray();
    }
}